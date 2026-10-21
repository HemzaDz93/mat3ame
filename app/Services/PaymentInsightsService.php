<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeePayment;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\Trainee;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * تحليلات مالية: الانتهاء القريب، التجديد، والمتأخرات (الديون).
 */
class PaymentInsightsService
{
    public function __construct(
        private SettingsService $settings,
        private PricingService $pricing
    ) {
    }

    /** مدة التنبيه المسبق قبل انتهاء السداد (بالأيام) */
    public function alertDays(): int
    {
        return max(1, (int) $this->settings->number('expiry_alert_days', 7));
    }

    /**
     * مرشحون للتجديد: السدادات المعتمدة (الأحدث لكل شخص) التي انتهى سريانها
     * أو تنتهي خلال مدة التنبيه، مرتّبة من الأقرب انتهاءً.
     */
    public function renewalCandidates(string $type = 'trainee'): Collection
    {
        $limit = now()->addDays($this->alertDays())->toDateString();

        if ($type === 'employee') {
            return EmployeePayment::query()
                ->with('employee')
                ->where('status', EmployeePayment::STATUS_APPROVED)
                ->whereDate('end_date', '<=', $limit)
                ->orderByDesc('end_date')
                ->get()
                ->unique('employee_id')
                ->sortBy('end_date')
                ->values();
        }

        return Payment::query()
            ->with('trainee')
            ->where('status', Payment::STATUS_APPROVED)
            ->whereDate('end_date', '<=', $limit)
            ->orderByDesc('end_date')
            ->get()
            ->unique('trainee_id')
            ->sortBy('end_date')
            ->values();
    }

    /**
     * المتأخرات: من انتهى سريان آخر سداد معتمد له ولم يجدّد، مع عدد الوجبات
     * المستلمة بعد تاريخ الانتهاء والمبلغ المستحق عنها.
     */
    public function traineeDebts(): Collection
    {
        $ends = $this->latestTraineeEnds()->get();

        if ($ends->isEmpty()) {
            return collect();
        }

        $trainees = Trainee::withTrashed()
            ->whereIn('id', $ends->pluck('trainee_id'))
            ->get()
            ->keyBy('id');

        $meals = Meal::query()
            ->joinSub($this->latestTraineeEnds(), 'ends', 'meals.trainee_id', '=', 'ends.trainee_id')
            ->whereColumn('meals.day', '>', 'ends.last_end')
            ->selectRaw('meals.trainee_id as subject_id, COUNT(*) as total')
            ->groupBy('meals.trainee_id')
            ->pluck('total', 'subject_id');

        return $this->buildDebts($ends, $trainees, $meals, $this->pricing->priceForPlan(Payment::PLAN_DAY));
    }

    /** المتأخرات الخاصة بالموظفين */
    public function employeeDebts(): Collection
    {
        $ends = $this->latestEmployeeEnds()->get();

        if ($ends->isEmpty()) {
            return collect();
        }

        $employees = Employee::withTrashed()
            ->whereIn('id', $ends->pluck('employee_id'))
            ->get()
            ->keyBy('id');

        $meals = EmployeeMeal::query()
            ->joinSub($this->latestEmployeeEnds(), 'ends', 'employee_meals.employee_id', '=', 'ends.employee_id')
            ->whereColumn('employee_meals.day', '>', 'ends.last_end')
            ->selectRaw('employee_meals.employee_id as subject_id, COUNT(*) as total')
            ->groupBy('employee_meals.employee_id')
            ->pluck('total', 'subject_id');

        return $this->buildDebts($ends, $employees, $meals, $this->pricing->priceForEmployeeMeal());
    }

    /** إجمالي المتأخرات (متربصون + موظفون) */
    public function debtsTotal(string $type = 'trainee'): array
    {
        $rows = $type === 'employee' ? $this->employeeDebts() : $this->traineeDebts();

        return [
            'count' => $rows->count(),
            'meals' => (int) $rows->sum('meals'),
            'amount' => (float) $rows->sum('amount'),
        ];
    }

    /** آخر تاريخ انتهاء سداد معتمد لكل متربص (السدادات المنتهية فقط) */
    private function latestTraineeEnds(): Builder
    {
        return Payment::query()
            ->selectRaw('trainee_id, MAX(end_date) as last_end')
            ->where('status', Payment::STATUS_APPROVED)
            ->groupBy('trainee_id')
            ->havingRaw('MAX(end_date) < ?', [now()->toDateString()]);
    }

    /** آخر تاريخ انتهاء سداد معتمد لكل موظف (السدادات المنتهية فقط) */
    private function latestEmployeeEnds(): Builder
    {
        return EmployeePayment::query()
            ->selectRaw('employee_id, MAX(end_date) as last_end')
            ->where('status', EmployeePayment::STATUS_APPROVED)
            ->groupBy('employee_id')
            ->havingRaw('MAX(end_date) < ?', [now()->toDateString()]);
    }

    /**
     * @param  Collection<int, object>  $ends
     * @param  Collection<int, mixed>  $subjects
     * @param  Collection<int, mixed>  $meals
     */
    private function buildDebts(Collection $ends, Collection $subjects, Collection $meals, float $mealPrice): Collection
    {
        $key = isset($ends->first()->trainee_id) ? 'trainee_id' : 'employee_id';
        $today = now()->startOfDay();

        return $ends
            ->map(function ($row) use ($key, $subjects, $meals, $mealPrice, $today) {
                $subject = $subjects->get($row->{$key});

                if (! $subject) {
                    return null;
                }

                $lastEnd = Carbon::parse($row->last_end)->startOfDay();
                $count = (int) ($meals[$row->{$key}] ?? 0);

                return [
                    'subject' => $subject,
                    'last_end' => $lastEnd,
                    'days_overdue' => (int) $lastEnd->diffInDays($today),
                    'meals' => $count,
                    'amount' => $count * $mealPrice,
                ];
            })
            ->filter()
            ->sortByDesc('amount')
            ->values();
    }
}
