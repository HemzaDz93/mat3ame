<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeePayment;
use App\Models\Meal;
use App\Models\MealForecast;
use App\Models\Payment;
use App\Models\Trainee;
use App\Services\AuditService;
use App\Services\MealService;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CanteenController extends Controller
{
    public function __construct(
        private MealService $meals,
        private PaymentService $payments,
        private AuditService $audit,
        private \App\Exports\SpreadsheetExporter $exporter
    ) {
    }

    public function index(Request $request): View
    {
        $today = now()->toDateString();

        $code = trim((string) $request->input('code', ''));
        $person = $code !== '' ? $this->resolve($code) : null;

        return view('canteen.index', [
            'mealsToday' => Meal::query()->whereDate('day', $today)->count(),
            'employeeMealsToday' => EmployeeMeal::query()->whereDate('day', $today)->count(),
            'expectedToday' => (int) (MealForecast::forDay($today)?->expected_count ?? 0),
            'expectedTomorrow' => (int) (MealForecast::forDay(now()->addDay()->toDateString())?->expected_count ?? 0),
            'code' => $code,
            'person' => $person,
        ]);
    }

    /** بحث AJAX بالرمز (QR/باركود/رقم يدوي) */
    public function find(Request $request): JsonResponse
    {
        $code = trim((string) $request->input('code', ''));

        if ($code === '') {
            return response()->json(['found' => false, 'message' => 'أدخل رقمًا أو امسح رمزًا.'], 422);
        }

        $person = $this->resolve($code);

        if (! $person) {
            return response()->json(['found' => false, 'message' => 'لا يوجد سجل مطابق للرمز: '.$code], 404);
        }

        return response()->json($person);
    }

    /** صفحة عرض السجل يدويًا (تعمل دون JavaScript) */
    public function show(string $code): View
    {
        $person = $this->resolve(trim($code));

        return view('canteen.show', ['person' => $person, 'code' => $code]);
    }

    public function serve(Request $request, Trainee $trainee): RedirectResponse
    {
        $result = $this->meals->serveTrainee($trainee, $request->user());

        if (! $result['ok']) {
            $this->audit->log('meal.rejected', $trainee, 'رفض تسليم وجبة متربص: '.$result['message'], null, null, 'failure');

            return back()->with('error', 'تعذّر تسليم الوجبة: '.$result['message']);
        }

        return back()->with('success', 'تم تسجيل الوجبة للمتربص: '.$trainee->full_name);
    }

    public function serveEmployee(Request $request, Employee $employee): RedirectResponse
    {
        $result = $this->meals->serveEmployee($employee, $request->user());

        if (! $result['ok']) {
            $this->audit->log('employee_meal.rejected', $employee, 'رفض تسليم وجبة موظف: '.$result['message'], null, null, 'failure');

            return back()->with('error', 'تعذّر تسليم الوجبة: '.$result['message']);
        }

        return back()->with('success', 'تم تسجيل الوجبة للموظف: '.$employee->full_name);
    }

    /** تسجيل سداد وجبة اليوم للمتربص */
    public function pay(Request $request, Trainee $trainee): RedirectResponse
    {
        $result = $this->payments->create(
            $trainee,
            Payment::PLAN_DAY,
            now()->toDateString(),
            $request->user(),
            'سداد وجبة يوم من منصة المطعم',
            $request->user()->canDecidePayments()
        );

        if (! $result['ok']) {
            return back()->with('warning', $result['message']);
        }

        $approved = $result['payment']->status === Payment::STATUS_APPROVED;

        return back()->with('success', $approved
            ? 'تم تسجيل سداد وجبة اليوم واعتماده.'
            : 'تم تسجيل طلب سداد وجبة اليوم بحالة معلقة.');
    }

    public function report(Request $request): View
    {
        abort_unless($request->user()->canViewCanteenReports(), 403);

        return view('canteen.report', $this->reportData($request));
    }

    public function reportExport(Request $request, string $format)
    {
        abort_unless($request->user()->canViewCanteenReports(), 403);

        $data = $this->reportData($request);

        $rows = [
            ['التقرير', 'تقرير المطعم'],
            ['من', $data['from']],
            ['إلى', $data['to']],
            ['وجبات المتربصين', $data['traineeMeals']],
            ['وجبات الموظفين', $data['employeeMeals']],
            ['متربصون بسداد ساري', $data['activePayers']],
            ['موظفون بسداد ساري', $data['activeEmployeePayers']],
            ['إجمالي السدادات المعتمدة', number_format($data['approvedTotal'], 2, '.', '')],
            ['إجمالي الوجبات المتوقّعة', $data['expectedTotal']],
            ['عمليات تسليم ناجحة', $data['serveSuccess']],
            ['عمليات تسليم مرفوضة', $data['serveRejected']],
            [],
            ['السدادات المعتمدة حسب النوع'],
            ['الفئة', 'النوع', 'العدد', 'المبلغ'],
        ];

        foreach ($data['byType'] as $row) {
            $rows[] = [$row['category'], $row['plan_label'], $row['total'], number_format($row['amount'], 2, '.', '')];
        }

        $rows[] = [];
        $rows[] = ['السدادات حسب الحالة'];
        $rows[] = ['الفئة', 'الحالة', 'العدد', 'المبلغ'];

        foreach ($data['byStatus'] as $row) {
            $rows[] = [$row['category'], $row['status'], $row['total'], number_format($row['amount'], 2, '.', '')];
        }

        $rows[] = [];
        $rows[] = ['توزيع العمليات حسب التاريخ'];
        $rows[] = ['التاريخ', 'المتوقّع (متربصون)', 'وجبات المتربصين', 'وجبات الموظفين', 'الإجمالي', 'الفرق'];

        foreach ($data['byDate'] as $row) {
            $rows[] = [
                $row->day,
                $row->expected,
                $row->trainee_meals,
                $row->employee_meals,
                $row->trainee_meals + $row->employee_meals,
                $row->trainee_meals - $row->expected,
            ];
        }

        $filename = 'canteen-report-'.now()->format('Ymd-His');

        return $format === 'xlsx'
            ? $this->exporter->xlsx($rows, $filename, 'تقرير المطعم')
            : $this->exporter->csv($rows, $filename);
    }

    private function reportData(Request $request): array
    {
        $from = $request->input('from') ?: now()->startOfMonth()->toDateString();
        $to = $request->input('to') ?: now()->toDateString();

        $traineeMeals = Meal::query()->whereDate('day', '>=', $from)->whereDate('day', '<=', $to)->count();
        $employeeMeals = EmployeeMeal::query()->whereDate('day', '>=', $from)->whereDate('day', '<=', $to)->count();

        $activePayers = Payment::query()
            ->where('status', Payment::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->distinct('trainee_id')
            ->count('trainee_id');

        $activeEmployeePayers = EmployeePayment::query()
            ->where('status', EmployeePayment::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $to)
            ->whereDate('end_date', '>=', $from)
            ->distinct('employee_id')
            ->count('employee_id');

        $approvedTotal = (float) Payment::query()
            ->where('status', Payment::STATUS_APPROVED)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->sum('amount')
            + (float) EmployeePayment::query()
                ->where('status', EmployeePayment::STATUS_APPROVED)
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->sum('amount');

        // السدادات المعتمدة حسب النوع: فئة السداد (متربص/موظف) ثم الخطة
        $byType = collect();

        foreach ([
            ['label' => 'المتربصون', 'rows' => $this->paymentsGroupedByPlan(Payment::class, $from, $to)],
            ['label' => 'الموظفون', 'rows' => $this->paymentsGroupedByPlan(EmployeePayment::class, $from, $to)],
        ] as $group) {
            foreach (array_keys(Payment::plans()) as $plan) {
                $row = $group['rows']->firstWhere('plan', $plan);

                if (! $row) {
                    continue;
                }

                $byType->push([
                    'category' => $group['label'],
                    'plan' => $plan,
                    'plan_label' => Payment::plans()[$plan],
                    'total' => (int) $row->total,
                    'amount' => (float) $row->amount,
                ]);
            }
        }

        // السدادات حسب الحالة: فئة السداد ثم الحالة (معلق/معتمد/مرفوض/ملغى)
        $byStatus = collect();

        foreach ([
            ['label' => 'المتربصون', 'model' => Payment::class],
            ['label' => 'الموظفون', 'model' => EmployeePayment::class],
        ] as $group) {
            $counts = $group['model']::query()
                ->select('status', DB::raw('COUNT(*) as total, SUM(amount) as amount'))
                ->whereDate('created_at', '>=', $from)
                ->whereDate('created_at', '<=', $to)
                ->groupBy('status')
                ->get()
                ->keyBy('status');

            foreach (Payment::statuses() as $status => $label) {
                $row = $counts[$status] ?? null;

                $byStatus->push([
                    'category' => $group['label'],
                    'status' => $label,
                    'tone' => match ($status) {
                        Payment::STATUS_APPROVED => 'success',
                        Payment::STATUS_PENDING => 'warning',
                        default => 'danger',
                    },
                    'total' => $row ? (int) $row->total : 0,
                    'amount' => $row ? (float) $row->amount : 0.0,
                ]);
            }
        }

        // توزيع العمليات حسب التاريخ: كل التواريخ التي وردت فيها وجبات (متربصون أو موظفون) أو توقّع
        $traineeByDate = Meal::query()
            ->selectRaw('DATE(day) as day, COUNT(*) as trainee_meals')
            ->whereDate('day', '>=', $from)->whereDate('day', '<=', $to)
            ->groupBy('day')->get()->keyBy('day');

        $employeeByDate = EmployeeMeal::query()
            ->selectRaw('DATE(day) as day, COUNT(*) as employee_meals')
            ->whereDate('day', '>=', $from)->whereDate('day', '<=', $to)
            ->groupBy('day')->get()->keyBy('day');

        $forecasts = MealForecast::query()
            ->whereDate('day', '>=', $from)->whereDate('day', '<=', $to)
            ->get()->keyBy(fn ($forecast) => $forecast->day->toDateString());

        $expectedTotal = (int) $forecasts->sum('expected_count');

        $byDate = $traineeByDate->keys()
            ->merge($employeeByDate->keys())
            ->merge($forecasts->keys())
            ->unique()
            ->sort()
            ->values()
            ->map(fn ($day) => (object) [
                'day' => $day,
                'expected' => (int) ($forecasts[$day]->expected_count ?? 0),
                'trainee_meals' => (int) ($traineeByDate[$day]->trainee_meals ?? 0),
                'employee_meals' => (int) ($employeeByDate[$day]->employee_meals ?? 0),
            ]);

        $serveSuccess = ActivityLog::query()
            ->whereIn('action', ['meal.served', 'employee_meal.served'])
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->count();

        $serveRejected = ActivityLog::query()
            ->whereIn('action', ['meal.rejected', 'employee_meal.rejected'])
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->count();

        return [
            'from' => $from,
            'to' => $to,
            'traineeMeals' => $traineeMeals,
            'employeeMeals' => $employeeMeals,
            'activePayers' => $activePayers,
            'activeEmployeePayers' => $activeEmployeePayers,
            'approvedTotal' => $approvedTotal,
            'expectedTotal' => $expectedTotal,
            'byType' => $byType,
            'byStatus' => $byStatus,
            'byDate' => $byDate,
            'serveSuccess' => $serveSuccess,
            'serveRejected' => $serveRejected,
        ];
    }

    /** السدادات مجمّعة حسب الخطة لفئة معيّنة خلال الفترة */
    private function paymentsGroupedByPlan(string $model, string $from, string $to)
    {
        return $model::query()
            ->select('plan', DB::raw('COUNT(*) as total, SUM(amount) as amount'))
            ->where('status', Payment::STATUS_APPROVED)
            ->whereDate('created_at', '>=', $from)
            ->whereDate('created_at', '<=', $to)
            ->groupBy('plan')
            ->get();
    }

    /** البحث عن شخص بالرمز */
    private function resolve(string $code): ?array
    {
        $trainee = Trainee::withTrashed()
            ->where('electronic_reg_no', $code)
            ->orWhere('reg_no', $code)
            ->orWhere('nin', $code)
            ->first();

        if ($trainee) {
            $status = $this->meals->traineeStatus($trainee);

            return [
                'found' => true,
                'type' => 'trainee',
                'id' => $trainee->id,
                'name' => $trainee->full_name,
                'identifier' => $trainee->identityCode(),
                'subtitle' => $trainee->specialty ?: '—',
                'photo' => $trainee->photo ? route('trainees.photo', $trainee) : null,
                'allowed' => $status['allowed'],
                'reason' => $status['reason'],
                'has_payment' => $status['payment'] !== null,
                'has_meal_today' => $status['has_meal_today'],
                'serve_url' => route('canteen.serve', $trainee),
                'pay_url' => route('canteen.pay', $trainee),
                'profile_url' => route('trainees.show', $trainee),
                'can_pay' => ! $status['has_meal_today'],
            ];
        }

        $employee = Employee::withTrashed()->where('emp_no', $code)->first();

        if ($employee) {
            $status = $this->meals->employeeStatus($employee);

            return [
                'found' => true,
                'type' => 'employee',
                'id' => $employee->id,
                'name' => $employee->full_name,
                'identifier' => $employee->emp_no,
                'subtitle' => $employee->rank ?: '—',
                'photo' => $employee->photo ? route('employees.photo', $employee) : null,
                'allowed' => $status['allowed'],
                'reason' => $status['reason'],
                'has_payment' => true,
                'has_meal_today' => $status['has_meal_today'],
                'serve_url' => route('canteen.serve-employee', $employee),
                'pay_url' => null,
                'profile_url' => route('employees.show', $employee),
                'can_pay' => false,
            ];
        }

        return null;
    }
}
