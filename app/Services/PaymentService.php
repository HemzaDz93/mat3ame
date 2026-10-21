<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function __construct(
        private PricingService $pricing,
        private AuditService $audit,
        private NotificationService $notifications
    ) {
    }

    /**
     * تسجيل سداد جديد.
     * يُرجع مصفوفة: ['ok' => bool, 'skipped' => bool, 'payment' => ?Payment, 'message' => string]
     */
    public function create(
        Trainee $trainee,
        string $plan,
        Carbon|string $startDate,
        User $actor,
        ?string $note = null,
        bool $directApprove = false
    ): array {
        if (! array_key_exists($plan, Payment::plans())) {
            throw new InvalidArgumentException('نوع السداد غير صالح.');
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end = $this->pricing->computeEndDate($start, $plan);
        $amount = $this->pricing->priceForPlan($plan);

        if ($amount < 0) {
            throw new InvalidArgumentException('المبلغ غير صالح.');
        }

        return DB::transaction(function () use ($trainee, $plan, $start, $end, $amount, $actor, $note, $directApprove) {
            // قفل سجلات سداد المتربص لمنع التداخل الناتج عن الطلبات المتزامنة
            $existing = Payment::query()
                ->where('trainee_id', $trainee->id)
                ->whereIn('status', [Payment::STATUS_PENDING, Payment::STATUS_APPROVED])
                ->lockForUpdate()
                ->get();

            $overlaps = $existing->first(function (Payment $p) use ($start, $end) {
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

            $payment = Payment::create([
                'trainee_id' => $trainee->id,
                'plan' => $plan,
                'start_date' => $start,
                'end_date' => $end,
                'amount' => $amount,
                'status' => $canApprove ? Payment::STATUS_APPROVED : Payment::STATUS_PENDING,
                'requested_by' => $actor->id,
                'approved_by' => $canApprove ? $actor->id : null,
                'note' => $note,
                'decided_at' => $canApprove ? now() : null,
            ]);

            $this->audit->log(
                $canApprove ? 'payment.created_approved' : 'payment.requested',
                $payment,
                'تسجيل سداد ('.$payment->planLabel().') بمبلغ '.$payment->amount,
                null,
                $payment->only(['trainee_id', 'plan', 'start_date', 'end_date', 'amount', 'status'])
            );

            if (! $canApprove) {
                $this->notifications->notify(
                    [User::ROLE_ADMIN, User::ROLE_SUPERVISOR],
                    'payment.pending',
                    'طلب سداد جديد',
                    'المتربص: '.$trainee->full_name.' — المبلغ: '.$payment->amount,
                    'info',
                    ['payment_id' => $payment->id]
                );
            }

            return ['ok' => true, 'skipped' => false, 'payment' => $payment, 'message' => 'تم تسجيل السداد.'];
        });
    }

    /** اعتماد أو رفض أو إلغاء سداد */
    public function decide(Payment $payment, string $action, User $actor, ?string $note = null): array
    {
        if (! $actor->canDecidePayments()) {
            return ['ok' => false, 'message' => 'غير مصرح باتخاذ قرار على السداد.'];
        }

        return DB::transaction(function () use ($payment, $action, $actor, $note) {
            $payment = Payment::lockForUpdate()->findOrFail($payment->id);
            $old = $payment->only(['status', 'approved_by', 'decided_at']);

            switch ($action) {
                case 'approve':
                    if ($payment->status !== Payment::STATUS_PENDING) {
                        return ['ok' => false, 'message' => 'لا يمكن اعتماد هذا السداد في حالته الحالية.'];
                    }
                    $payment->status = Payment::STATUS_APPROVED;
                    $payment->approved_by = $actor->id;
                    $payment->decided_at = now();
                    break;

                case 'reject':
                    if ($payment->status !== Payment::STATUS_PENDING) {
                        return ['ok' => false, 'message' => 'لا يمكن رفض هذا السداد في حالته الحالية.'];
                    }
                    $payment->status = Payment::STATUS_REJECTED;
                    $payment->approved_by = $actor->id;
                    $payment->decided_at = now();
                    break;

                case 'cancel':
                    if ($payment->status !== Payment::STATUS_APPROVED) {
                        return ['ok' => false, 'message' => 'لا يمكن إلغاء إلا السداد المعتمد.'];
                    }
                    $payment->status = Payment::STATUS_CANCELLED;
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
                'payment.'.$action,
                $payment,
                'قرار على سداد: '.$payment->statusLabel(),
                $old,
                $payment->only(['status', 'approved_by', 'decided_at'])
            );

            $this->notifications->notify(
                [User::ROLE_ADMIN, User::ROLE_STAFF],
                'payment.decided',
                'تم '.($action === 'approve' ? 'اعتماد' : ($action === 'reject' ? 'رفض' : 'إلغاء')).' سداد',
                'المبلغ: '.$payment->amount,
                $action === 'approve' ? 'success' : 'warning',
                ['payment_id' => $payment->id]
            );

            return ['ok' => true, 'payment' => $payment, 'message' => 'تم تنفيذ الإجراء بنجاح.'];
        });
    }
}
