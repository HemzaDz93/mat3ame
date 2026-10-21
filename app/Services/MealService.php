<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class MealService
{
    public function __construct(private AuditService $audit)
    {
    }

    /** حالة أهلية وجبة المتربص لتاريخ معيّن */
    public function traineeStatus(Trainee $trainee, Carbon|string|null $date = null): array
    {
        $day = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $status = [
            'allowed' => false,
            'reason' => null,
            'payment' => null,
            'has_meal_today' => false,
            'file_accepted' => (bool) $trainee->file_accepted,
            'eligible' => (bool) $trainee->eligible,
            'account_cancelled' => (bool) $trainee->account_cancelled,
        ];

        if ($trainee->trashed()) {
            $status['reason'] = 'سجل المتربص محذوف';

            return $status;
        }

        $status['has_meal_today'] = Meal::query()
            ->where('trainee_id', $trainee->id)
            ->whereDate('day', $day)
            ->exists();

        $payment = Payment::query()
            ->where('trainee_id', $trainee->id)
            ->where('status', Payment::STATUS_APPROVED)
            ->whereDate('start_date', '<=', $day)
            ->whereDate('end_date', '>=', $day)
            ->orderByDesc('start_date')
            ->first();

        $status['payment'] = $payment;

        if ($status['has_meal_today']) {
            $status['reason'] = 'تم تسليم وجبة لهذا المتربص اليوم مسبقًا';
        } elseif ($trainee->account_cancelled) {
            $status['reason'] = 'حساب المتربص ملغى';
        } elseif (! $trainee->file_accepted || ! $trainee->eligible) {
            $status['reason'] = 'ملف المتربص غير مقبول أو غير مؤهل';
        } elseif (! $payment) {
            $status['reason'] = 'لا يوجد سداد معتمد وساري يغطي هذا اليوم';
        } else {
            $status['allowed'] = true;
        }

        return $status;
    }

    /** تسليم وجبة متربص داخل معاملة قاعدة بيانات مع منع التكرار */
    public function serveTrainee(Trainee $trainee, User $user, Carbon|string|null $date = null): array
    {
        $day = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        try {
            return DB::transaction(function () use ($trainee, $user, $day) {
                $fresh = Trainee::withTrashed()->lockForUpdate()->findOrFail($trainee->id);
                $status = $this->traineeStatus($fresh, $day);

                if (! $status['allowed']) {
                    return ['ok' => false, 'message' => $status['reason'], 'status' => $status];
                }

                $meal = Meal::create([
                    'trainee_id' => $fresh->id,
                    'day' => $day,
                    'served_at' => now(),
                    'served_by' => $user->id,
                ]);

                $this->audit->log('meal.served', $fresh, 'تسليم وجبة متربص');

                return ['ok' => true, 'message' => 'تم تسجيل الوجبة بنجاح', 'meal' => $meal, 'status' => $status];
            });
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                return ['ok' => false, 'message' => 'تم تسليم وجبة لهذا المتربص اليوم مسبقًا', 'status' => $this->traineeStatus($trainee, $day)];
            }
            throw $e;
        }
    }

    /** حالة أهلية وجبة الموظف (لا تشترط سدادًا) */
    public function employeeStatus(Employee $employee, Carbon|string|null $date = null): array
    {
        $day = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        $status = ['allowed' => false, 'reason' => null, 'has_meal_today' => false];

        if ($employee->trashed()) {
            $status['reason'] = 'سجل الموظف محذوف';

            return $status;
        }

        $status['has_meal_today'] = EmployeeMeal::query()
            ->where('employee_id', $employee->id)
            ->whereDate('day', $day)
            ->exists();

        if ($status['has_meal_today']) {
            $status['reason'] = 'تم تسليم وجبة لهذا الموظف اليوم مسبقًا';
        } else {
            $status['allowed'] = true;
        }

        return $status;
    }

    public function serveEmployee(Employee $employee, User $user, Carbon|string|null $date = null): array
    {
        $day = $date ? Carbon::parse($date)->toDateString() : now()->toDateString();

        try {
            return DB::transaction(function () use ($employee, $user, $day) {
                $fresh = Employee::withTrashed()->lockForUpdate()->findOrFail($employee->id);
                $status = $this->employeeStatus($fresh, $day);

                if (! $status['allowed']) {
                    return ['ok' => false, 'message' => $status['reason'], 'status' => $status];
                }

                $meal = EmployeeMeal::create([
                    'employee_id' => $fresh->id,
                    'day' => $day,
                    'served_at' => now(),
                    'served_by' => $user->id,
                ]);

                $this->audit->log('employee_meal.served', $fresh, 'تسليم وجبة موظف');

                return ['ok' => true, 'message' => 'تم تسجيل وجبة الموظف بنجاح', 'meal' => $meal, 'status' => $status];
            });
        } catch (QueryException $e) {
            if ($this->isUniqueViolation($e)) {
                return ['ok' => false, 'message' => 'تم تسليم وجبة لهذا الموظف اليوم مسبقًا', 'status' => $this->employeeStatus($employee, $day)];
            }
            throw $e;
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return (int) $e->getCode() === 23000 || str_contains($e->getMessage(), 'Duplicate entry');
    }
}
