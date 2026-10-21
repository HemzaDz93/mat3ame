<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeePaymentRequest;
use App\Http\Requests\PaymentRequest;
use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Payment;
use App\Models\Trainee;
use App\Services\EmployeePaymentService;
use App\Services\PaymentInsightsService;
use App\Services\PaymentService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $payments,
        private EmployeePaymentService $employeePayments,
        private PricingService $pricing,
        private PaymentInsightsService $insights
    ) {
    }

    public function index(Request $request): View
    {
        $tab = $request->input('tab') === 'employees' ? 'employees' : 'trainees';

        if ($tab === 'employees') {
            $employeePayments = EmployeePayment::query()
                ->with(['employee', 'requester', 'approver'])
                ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
                ->when($request->input('plan'), fn ($q, $p) => $q->where('plan', $p))
                ->when($request->input('q'), function ($q, $term) {
                    $term = trim($term);
                    $q->whereHas('employee', function ($w) use ($term) {
                        $w->where('last_name', 'like', "%{$term}%")
                            ->orWhere('first_name', 'like', "%{$term}%")
                            ->orWhere('emp_no', 'like', "%{$term}%");
                    });
                })
                ->when($request->input('from'), fn ($q, $d) => $q->whereDate('start_date', '>=', $d))
                ->when($request->input('to'), fn ($q, $d) => $q->whereDate('end_date', '<=', $d))
                ->latest('id')
                ->paginate(25)
                ->withQueryString();

            return view('payments.index', [
                'tab' => 'employees',
                'employeePayments' => $employeePayments,
                'statuses' => EmployeePayment::statuses(),
                'plans' => EmployeePayment::plans(),
            ]);
        }

        $payments = Payment::query()
            ->with(['trainee', 'requester', 'approver'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('plan'), fn ($q, $p) => $q->where('plan', $p))
            ->when($request->input('q'), function ($q, $term) {
                $term = trim($term);
                $q->whereHas('trainee', function ($w) use ($term) {
                    $w->where('last_name', 'like', "%{$term}%")
                        ->orWhere('first_name', 'like', "%{$term}%")
                        ->orWhere('reg_no', 'like', "%{$term}%")
                        ->orWhere('electronic_reg_no', 'like', "%{$term}%")
                        ->orWhere('nin', 'like', "%{$term}%");
                });
            })
            ->when($request->input('from'), fn ($q, $d) => $q->whereDate('start_date', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->whereDate('end_date', '<=', $d))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('payments.index', [
            'tab' => 'trainees',
            'payments' => $payments,
            'statuses' => Payment::statuses(),
            'plans' => Payment::plans(),
        ]);
    }

    public function create(Request $request): View
    {
        $type = $request->input('type') === 'employee' ? 'employee' : 'trainee';

        $trainee = null;
        $employee = null;
        $results = collect();

        if ($type === 'employee') {
            if ($request->filled('employee_id')) {
                $employee = Employee::query()->find($request->input('employee_id'));
            }

            if (! $employee) {
                $results = Employee::query()
                    ->search($request->input('q'))
                    ->orderBy('last_name')->orderBy('first_name')
                    ->limit(30)
                    ->get();
            }
        } else {
            if ($request->filled('trainee_id')) {
                $trainee = Trainee::query()->find($request->input('trainee_id'));
            }

            if (! $trainee) {
                $results = Trainee::query()
                    ->when($request->filled('specialty'), fn ($q) => $q->where('specialty', $request->input('specialty')))
                    ->search($request->input('q'))
                    ->orderBy('last_name')->orderBy('first_name')
                    ->limit(30)
                    ->get();
            }
        }

        return view('payments.create', [
            'type' => $type,
            'trainee' => $trainee,
            'employee' => $employee,
            'results' => $results,
            'specialties' => Trainee::query()->whereNotNull('specialty')->where('specialty', '!=', '')
                ->distinct()->orderBy('specialty')->pluck('specialty'),
            'plans' => Payment::plans(),
            'prices' => [
                'day' => $this->planPrice($type, Payment::PLAN_DAY),
                'month' => $this->planPrice($type, Payment::PLAN_MONTH),
                'quarter' => $this->planPrice($type, Payment::PLAN_QUARTER),
            ],
        ]);
    }

    /** سعر الخطة حسب الفئة: سدادات المتربصين أم سدادات الموظفين */
    private function planPrice(string $type, string $plan): float
    {
        return $type === 'employee'
            ? $this->pricing->priceForEmployeePlan($plan)
            : $this->pricing->priceForPlan($plan);
    }

    public function store(PaymentRequest $request): RedirectResponse
    {
        $trainee = Trainee::query()->findOrFail($request->input('trainee_id'));

        $result = $this->payments->create(
            $trainee,
            $request->input('plan'),
            $request->input('start_date'),
            $request->user(),
            $request->input('note'),
            false
        );

        if (! $result['ok']) {
            return back()->withInput()->with('warning', $result['message']);
        }

        return redirect()->route('payments.index')
            ->with('success', 'تم تسجيل طلب السداد بحالة معلقة، بانتظار الاعتماد.');
    }

    public function storeDirect(PaymentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->canDecidePayments(), 403);

        $trainee = Trainee::query()->findOrFail($request->input('trainee_id'));

        $result = $this->payments->create(
            $trainee,
            $request->input('plan'),
            $request->input('start_date'),
            $request->user(),
            $request->input('note'),
            true
        );

        if (! $result['ok']) {
            return back()->withInput()->with('warning', $result['message']);
        }

        return redirect()->route('payments.index')->with('success', 'تم تسجيل السداد واعتماده مباشرة.');
    }

    public function decide(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
            'action' => ['required', 'in:approve,reject,cancel'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = Payment::query()->findOrFail($data['payment_id']);

        $result = $this->payments->decide($payment, $data['action'], $request->user(), $data['note'] ?? null);

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /** سداد موظف (طلب) */
    public function storeEmployee(EmployeePaymentRequest $request): RedirectResponse
    {
        $employee = Employee::query()->findOrFail($request->input('employee_id'));

        $result = $this->employeePayments->create(
            $employee,
            $request->input('plan'),
            $request->input('start_date'),
            $request->user(),
            $request->input('note'),
            false
        );

        if (! $result['ok']) {
            return back()->withInput()->with('warning', $result['message']);
        }

        return redirect()->route('payments.index', ['tab' => 'employees'])
            ->with('success', 'تم تسجيل طلب سداد الموظف بحالة معلقة، بانتظار الاعتماد.');
    }

    /** سداد موظف (اعتماد مباشر) */
    public function storeEmployeeDirect(EmployeePaymentRequest $request): RedirectResponse
    {
        abort_unless($request->user()->canDecidePayments(), 403);

        $employee = Employee::query()->findOrFail($request->input('employee_id'));

        $result = $this->employeePayments->create(
            $employee,
            $request->input('plan'),
            $request->input('start_date'),
            $request->user(),
            $request->input('note'),
            true
        );

        if (! $result['ok']) {
            return back()->withInput()->with('warning', $result['message']);
        }

        return redirect()->route('payments.index', ['tab' => 'employees'])
            ->with('success', 'تم تسجيل سداد الموظف واعتماده مباشرة.');
    }

    /** قرار على سداد موظف */
    public function decideEmployee(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'employee_payment_id' => ['required', 'integer', 'exists:employee_payments,id'],
            'action' => ['required', 'in:approve,reject,cancel'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $payment = EmployeePayment::query()->findOrFail($data['employee_payment_id']);

        $result = $this->employeePayments->decide($payment, $data['action'], $request->user(), $data['note'] ?? null);

        if (! $result['ok']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /** وصل سداد متربص قابل للطباعة */
    public function receipt(Payment $payment): View
    {
        $payment->load(['trainee', 'requester', 'approver']);

        return view('payments.receipt', [
            'type' => 'trainee',
            'payment' => $payment,
            'subject' => $payment->trainee,
            'code' => 'P'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
        ]);
    }

    /** وصل سداد موظف قابل للطباعة */
    public function receiptEmployee(EmployeePayment $payment): View
    {
        $payment->load(['employee', 'requester', 'approver']);

        return view('payments.receipt', [
            'type' => 'employee',
            'payment' => $payment,
            'subject' => $payment->employee,
            'code' => 'M'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT),
        ]);
    }

    /** شاشة الانتهاء القريب والتجديد الجماعي */
    public function renewals(Request $request): View
    {
        $type = $request->input('tab') === 'employees' ? 'employee' : 'trainee';
        $rows = $this->insights->renewalCandidates($type);

        return view('payments.renewals', [
            'type' => $type,
            'rows' => $rows,
            'alertDays' => $this->insights->alertDays(),
            'expiringCount' => $rows->filter(fn ($p) => $p->daysLeft() >= 0)->count(),
            'expiredCount' => $rows->filter(fn ($p) => $p->daysLeft() < 0)->count(),
            'plans' => $type === 'employee' ? EmployeePayment::plans() : Payment::plans(),
            'renewable' => $request->user()->canDecidePayments(),
        ]);
    }

    /** تجديد جماعي للسدادات المحددة انطلاقًا من اليوم التالي لانتهائها */
    public function renewBulk(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canDecidePayments(), 403);

        $data = $request->validate([
            'type' => ['required', 'in:trainee,employee'],
            'payment_ids' => ['required', 'array', 'min:1'],
            'payment_ids.*' => ['integer'],
            'plans' => ['required', 'array'],
            'plans.*' => ['nullable', 'in:day,month,quarter'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [], [
            'payment_ids' => 'السدادات المحددة',
        ]);

        $isEmployee = $data['type'] === 'employee';
        $statusApproved = $isEmployee ? EmployeePayment::STATUS_APPROVED : Payment::STATUS_APPROVED;
        $plans = $isEmployee ? EmployeePayment::plans() : Payment::plans();

        $payments = ($isEmployee ? EmployeePayment::query() : Payment::query())
            ->with($isEmployee ? 'employee' : 'trainee')
            ->whereIn('id', $data['payment_ids'])
            ->where('status', $statusApproved)
            ->get();

        $created = 0;
        $skipped = 0;

        foreach ($payments as $payment) {
            $plan = $data['plans'][$payment->id] ?? $payment->plan;
            $subject = $isEmployee ? $payment->employee : $payment->trainee;

            if (! $subject || ! array_key_exists($plan, $plans)) {
                $skipped++;
                continue;
            }

            // التجديد يبدأ من اليوم التالي لانتهاء السداد، أو من اليوم إن كان منتهيًا سابقًا
            $start = $payment->end_date->copy()->startOfDay()->addDay();

            if ($start->isPast()) {
                $start = Carbon::today();
            }

            $result = $isEmployee
                ? $this->employeePayments->create($subject, $plan, $start, $request->user(), $data['note'] ?? null, true)
                : $this->payments->create($subject, $plan, $start, $request->user(), $data['note'] ?? null, true);

            $result['ok'] ? $created++ : $skipped++;
        }

        $tab = $isEmployee ? 'employees' : 'trainees';

        if ($created === 0) {
            return redirect()->route('payments.renewals', ['tab' => $tab])
                ->with('warning', 'لم يتم تجديد أي سداد (تم تخطي '.$skipped.' سجل بسبب التداخل).');
        }

        $message = 'تم تجديد '.$created.' سدادًا بنجاح.';

        if ($skipped > 0) {
            $message .= ' وتم تخطي '.$skipped.' سجل بسبب التداخل.';
        }

        return redirect()->route('payments.renewals', ['tab' => $tab])->with('success', $message);
    }

    /** قائمة المتأخرات (الديون) */
    public function debts(Request $request): View
    {
        $type = $request->input('tab') === 'employees' ? 'employee' : 'trainee';

        return view('payments.debts', [
            'type' => $type,
            'rows' => $type === 'employee' ? $this->insights->employeeDebts() : $this->insights->traineeDebts(),
            'totals' => $this->insights->debtsTotal($type),
        ]);
    }
}
