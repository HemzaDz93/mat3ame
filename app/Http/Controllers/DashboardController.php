<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeePayment;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Services\PaymentInsightsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private PaymentInsightsService $insights)
    {
    }

    public function index(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isCanteen()) {
            return redirect()->route('canteen.index');
        }

        if ($user->isAssistant()) {
            return redirect()->route('forecast.index');
        }

        $today = now()->toDateString();

        $traineeStats = Trainee::query()->selectRaw(
            'COUNT(*) as total,
             SUM(CASE WHEN file_accepted = 1 AND eligible = 1 THEN 1 ELSE 0 END) as accepted,
             SUM(CASE WHEN file_accepted = 0 OR eligible = 0 THEN 1 ELSE 0 END) as not_accepted,
             SUM(CASE WHEN eligible = 1 THEN 1 ELSE 0 END) as eligible,
             SUM(CASE WHEN account_cancelled = 1 THEN 1 ELSE 0 END) as cancelled'
        )->first();

        $stats = [
            'trainees_total' => (int) ($traineeStats->total ?? 0),
            'trainees_accepted' => (int) ($traineeStats->accepted ?? 0),
            'trainees_not_accepted' => (int) ($traineeStats->not_accepted ?? 0),
            'trainees_eligible' => (int) ($traineeStats->eligible ?? 0),
            'trainees_cancelled' => (int) ($traineeStats->cancelled ?? 0),
            'employees_total' => Employee::query()->count(),
            'meals_today' => Meal::query()->whereDate('day', $today)->count(),
        ];

        $bySpecialty = Trainee::query()
            ->select('specialty', DB::raw('COUNT(*) as total'))
            ->whereNotNull('specialty')->where('specialty', '!=', '')
            ->groupBy('specialty')->orderByDesc('total')->limit(8)->get();

        $byDiploma = Trainee::query()
            ->select('diploma', DB::raw('COUNT(*) as total'))
            ->whereNotNull('diploma')->where('diploma', '!=', '')
            ->groupBy('diploma')->orderByDesc('total')->limit(8)->get();

        $byLevel = Trainee::query()
            ->select('level', DB::raw('COUNT(*) as total'))
            ->whereNotNull('level')->where('level', '!=', '')
            ->groupBy('level')->orderByDesc('total')->limit(8)->get();

        $byGender = Trainee::query()
            ->select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')->get();

        $yearSql = DB::connection()->getDriverName() === 'sqlite'
            ? "CAST(strftime('%Y', start_date) AS INTEGER)"
            : 'YEAR(start_date)';

        $byStartYear = Trainee::query()
            ->selectRaw($yearSql.' as year, COUNT(*) as total')
            ->whereNotNull('start_date')
            ->groupBy('year')->orderByDesc('year')->limit(8)->get();

        $pendingPayments = null;

        if ($user->canDecidePayments()) {
            $traineePending = Payment::query()
                ->with('trainee')
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (Payment $payment) => [
                    'name' => $payment->trainee?->full_name ?? '—',
                    'type' => 'متربص',
                    'amount' => $payment->amount,
                    'created_at' => $payment->created_at,
                ]);

            $employeePending = EmployeePayment::query()
                ->with('employee')
                ->where('status', EmployeePayment::STATUS_PENDING)
                ->latest()
                ->limit(10)
                ->get()
                ->map(fn (EmployeePayment $payment) => [
                    'name' => $payment->employee?->full_name ?? '—',
                    'type' => 'موظف',
                    'amount' => $payment->amount,
                    'created_at' => $payment->created_at,
                ]);

            $pendingPayments = $traineePending->concat($employeePending)
                ->sortByDesc('created_at')
                ->take(10)
                ->values();
        }

        $employeeMealsToday = $user->canViewCanteenReports()
            ? EmployeeMeal::query()->whereDate('day', $today)->count()
            : null;

        // سدادات تنتهي خلال مدة التنبيه (للمالية فقط)
        $expiringPayments = null;

        if ($user->hasRole(User::ROLE_ADMIN, User::ROLE_SUPERVISOR, User::ROLE_STAFF)) {
            $expiringPayments = $this->insights->renewalCandidates('trainee')
                ->filter(fn ($payment) => $payment->daysLeft() >= 0)
                ->count()
                + $this->insights->renewalCandidates('employee')
                    ->filter(fn ($payment) => $payment->daysLeft() >= 0)
                    ->count();
        }

        return view('dashboard.index', compact(
            'stats',
            'bySpecialty',
            'byDiploma',
            'byLevel',
            'byGender',
            'byStartYear',
            'pendingPayments',
            'employeeMealsToday',
            'expiringPayments'
        ));
    }
}
