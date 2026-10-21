<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class EmployeePaymentService
{
    public function __construct(
        private PricingService $pricing,
        private AuditService $audit,
        private NotificationService $notifications
    ) {
    }

    /**
     * تسجيل سداد جديد لموظف.
     * يُرجع مصفوفة: ['ok' => bool, 'skipped' => bool, 'payment' => ?EmployeePayment, 'message' => string]
     */
    public function create(
        Employee $employee,
        string $plan,
        Carbon|string $startDate,
        User $actor,
        ?string $note = null,
        bool $directApprove = false
    ): array {
        if (! array_key_exists($plan, EmployeePayment::plans())) {
            throw new InvalidArgumentException('نوع السداد غير صالح.');
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = $this->pricing->computeEndDate($start, $plan);
        $amount = $this->pricing->priceForEmployeePlan($plan);

        if ($amount < 0) {
            throw new InvalidArgumentException('المبلغ غير صالح.');
        }

        return DB::transaction(function () use ($employee, $plan, $start, $end, $amount, $actor, $note, $directApprove) {
            // قفل سجلات سداد الموظف لمنع التداخل الناتج عن الطلبات المتزامنة
            $existing = EmployeePayment::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', [EmployeePayment::STATUS_PENDING, EmployeePayment::STATUS_APPROVED])
                ->lockForUpdate()
                ->get();

            $overlaps = $existing->first(function (EmployeePayment $p) use ($start, $end) {
                return $p->start_date->format('Y-m-d') <= $end->format('Y-m-d')
                    && $p->end_date->format('Y-m-d') >= $start->format('Y-m-d');
            });

            if ($overlaps) {
                return [
                    'ok' => false,
                    'skipped' => true,
                    'payment' => null,
                    'message' => 'تم تخطي العملية لتداخل الفترة مع سداد قائم ('. $overlaps->statusLabel() .').',
                ];
            }

            $canApprove = $directApprove && $actor->canDecidePayments();

            $payment = EmployeePayment::create([
                'employee_id' => $employee->id,
                'plan' => $plan,
                'start_date' => $start,
                'end_date' => $end,
                'amount' => $amount,
                'status' => $canApprove ? EmployeePayment::STATUS_APPROVED : EmployeePayment::STATUS_PENDING,
                'requested_by' => $actor->id,
                'approved_by' => $canApprove ? $actor->id : null,
                'note' => $note,
                'decided_at' => $canApprove ? now() : null,
            ]);

            $this->audit->log(
                $canApprove ? 'employee_payment.created_approved' : 'employee_payment.requested',
                $payment,
                'تسجيل سداد موظف ('.$payment->planLabel().') بمبلغ '.$payment->amount,
                null,
                $payment->only(['employee_id', 'plan', 'start_date', 'end_date', 'amount', 'status'])
            );

            if (! $canApprove) {
                $this->notifications->notify(
                    [User::ROLE_ADMIN, User::ROLE_SUPERVISOR],
                    'employee_payment.pending',
                    'طلب سداد موظف جديد',
                    'الموظف: '.$employee->full_name.' — المبلغ: '.$payment->amount,
                    'info',
                    ['employee_payment_id' => $payment->id]
                );
            }

            return ['ok' => true, 'skipped' => false, 'payment' => $payment, 'message' => 'تم تسجيل السداد.'];
        });
    }

    /** اعتماد أو رفض أو إلغاء سداد موظف */
    public function decide(EmployeePayment $payment, string $action, User $actor, ?string $note = null): array
    {
        if (! $actor->canDecidePayments()) {
            return ['ok' => false, 'message' => 'غير مصرح باتخاذ قرار على السداد.'];
        }

        return DB::transaction(function () use ($payment, $action, $actor, $note) {
            $payment = EmployeePayment::lockForUpdate()->findOrFail($payment->id);
            $old = $payment->only(['status', 'approved_by', 'decided_at']);

            switch ($action) {
                case 'approve':
                    if ($payment->status !== EmployeePayment::STATUS_PENDING) {
                        return ['ok' => false, 'message' => 'لا يمكن اعتماد هذا السداد في حالته الحالية.'];
                    }
                    $payment->status = EmployeePayment::STATUS_APPROVED;
                    $payment->approved_by = $actor->id;
                    $payment->decided_at = now();
                    break;

                case 'reject':
                    if ($payment->status !== EmployeePayment::STATUS_PENDING) {
                        return ['ok' => false, 'message' => 'لا يمكن رفض هذا السداد في حالته الحالية.'];
                    }
                    $payment->status = EmployeePayment::STATUS_REJECTED;
                    $payment->approved_by = $actor->id;
                    $payment->decided_at = now();
                    break;

                case 'cancel':
                    if ($payment->status !== EmployeePayment::STATUS_APPROVED) {
                        return ['ok' => false, 'message' => 'لا يمكن إلغاء إلا السداد المعتمد.'];
                    }
                    $payment->status = EmployeePayment::STATUS_CANCELLED;
                    $payment->approved_by = $actor->id;
                    $payment->decided_at = now();
                    break;

                default:
                    return ['ok' => false, 'message' => 'إجراء غير معروف.'];
            }

            if ($note !== null && $note !== '') {
                $payment->note = trim(($payment->note ? $payment->note.' | ' : '').$note);
            }

            $payment->save();

            $this->audit->log(
                'employee_payment.'.$action,
                $payment,
                'قرار على سداد موظف: '.$payment->statusLabel(),
                $old,
                $payment->only(['status', 'approved_by', 'decided_at'])
            );

            $this->notifications->notify(
                [User::ROLE_ADMIN, User::ROLE_STAFF],
                'employee_payment.decided',
                'تم '.($action === 'approve' ? 'اعتماد' : ($action === 'reject' ? 'رفض' : 'إلغاء')).' سداد موظف',
                'المبلغ: '.$payment->amount,
                $action === 'approve' ? 'success' : 'warning',
                ['employee_payment_id' => $payment->id]
            );

            return ['ok' => true, 'payment' => $payment, 'message' => 'تم تنفيذ الإجراء بنجاح.'];
        });
    }
}
