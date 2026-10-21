<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Services\MealService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MealServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $canteen;

    protected function setUp(): void
    {
        parent::setUp();

        $this->canteen = User::factory()->create([
            'username' => 'canteen',
            'name' => 'مسيّر المطعم',
            'role' => User::ROLE_CANTEEN,
            'is_active' => true,
            'password' => 'Password123',
        ]);
    }

    private function trainee(array $attributes = []): Trainee
    {
        static $seq = 0;
        $seq++;

        return Trainee::create(array_merge([
            'reg_no' => '2026'.str_pad((string) $seq, 4, '0', STR_PAD_LEFT),
            'electronic_reg_no' => 'E'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'last_name' => 'بن عمر',
            'first_name' => 'أمينة',
            'eligible' => true,
            'file_accepted' => true,
            'account_cancelled' => false,
        ], $attributes));
    }

    private function approvedPayment(Trainee $trainee, ?string $day = null): Payment
    {
        $day ??= now()->toDateString();

        return Payment::create([
            'trainee_id' => $trainee->id,
            'plan' => Payment::PLAN_MONTH,
            'start_date' => $day,
            'end_date' => $day,
            'amount' => 1100,
            'status' => Payment::STATUS_APPROVED,
            'requested_by' => $this->canteen->id,
            'approved_by' => $this->canteen->id,
            'decided_at' => now(),
        ]);
    }

    public function test_meal_is_served_when_every_condition_is_met(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee);

        $result = app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $this->assertTrue($result['ok']);
        $this->assertDatabaseCount('meals', 1);
        $this->assertDatabaseHas('meals', [
            'trainee_id' => $trainee->id,
            'day' => now()->startOfDay()->toDateTimeString(),
            'served_by' => $this->canteen->id,
        ]);
    }

    public function test_meal_is_rejected_when_file_is_not_accepted(): void
    {
        $trainee = $this->trainee(['file_accepted' => false]);
        $this->approvedPayment($trainee);

        $result = app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $this->assertFalse($result['ok']);
        $this->assertDatabaseCount('meals', 0);
    }

    public function test_meal_is_rejected_when_trainee_is_not_eligible(): void
    {
        $trainee = $this->trainee(['eligible' => false]);
        $this->approvedPayment($trainee);

        $this->assertFalse(app(MealService::class)->serveTrainee($trainee, $this->canteen)['ok']);
        $this->assertDatabaseCount('meals', 0);
    }

    public function test_meal_is_rejected_when_account_is_cancelled(): void
    {
        $trainee = $this->trainee(['account_cancelled' => true]);
        $this->approvedPayment($trainee);

        $result = app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $this->assertFalse($result['ok']);
        $this->assertSame('حساب المتربص ملغى', $result['message']);
    }

    public function test_meal_is_rejected_without_an_approved_payment(): void
    {
        $trainee = $this->trainee();

        $result = app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $this->assertFalse($result['ok']);
        $this->assertDatabaseCount('meals', 0);
    }

    public function test_meal_is_rejected_when_the_payment_does_not_cover_today(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee, now()->subMonth()->toDateString());

        $this->assertFalse(app(MealService::class)->serveTrainee($trainee, $this->canteen)['ok']);
    }

    public function test_meal_is_rejected_when_the_payment_is_only_pending(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee)->update(['status' => Payment::STATUS_PENDING]);

        $this->assertFalse(app(MealService::class)->serveTrainee($trainee, $this->canteen)['ok']);
    }

    public function test_a_second_meal_is_rejected_on_the_same_day(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee);

        $service = app(MealService::class);

        $this->assertTrue($service->serveTrainee($trainee, $this->canteen)['ok']);

        $second = $service->serveTrainee($trainee, $this->canteen);

        $this->assertFalse($second['ok']);
        $this->assertDatabaseCount('meals', 1);
    }

    public function test_meal_is_rejected_for_a_soft_deleted_trainee(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee);
        $trainee->delete();

        $result = app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $this->assertFalse($result['ok']);
        $this->assertSame('سجل المتربص محذوف', $result['message']);
    }

    public function test_employee_meal_is_served_once_per_day(): void
    {
        static $seq = 0;
        $seq++;

        $employee = Employee::create([
            'emp_no' => 'EMP-'.str_pad((string) $seq, 6, '0', STR_PAD_LEFT),
            'last_name' => 'قاسمي',
            'first_name' => 'عبد القادر',
        ]);

        $service = app(MealService::class);

        $this->assertTrue($service->serveEmployee($employee, $this->canteen)['ok']);

        $second = $service->serveEmployee($employee, $this->canteen);

        $this->assertFalse($second['ok']);
        $this->assertDatabaseCount('employee_meals', 1);
    }

    public function test_trainee_status_reports_remaining_eligibility_fields(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee);

        $status = app(MealService::class)->traineeStatus($trainee);

        $this->assertTrue($status['allowed']);
        $this->assertFalse($status['has_meal_today']);
        $this->assertTrue($status['file_accepted']);
        $this->assertTrue($status['eligible']);
        $this->assertFalse($status['account_cancelled']);
        $this->assertInstanceOf(Payment::class, $status['payment']);
    }

    public function test_meal_model_records_the_serving_user(): void
    {
        $trainee = $this->trainee();
        $this->approvedPayment($trainee);

        app(MealService::class)->serveTrainee($trainee, $this->canteen);

        $meal = Meal::query()->firstOrFail();

        $this->assertSame($this->canteen->id, $meal->served_by);
        $this->assertTrue($meal->day->isSameDay(now()));
    }
}
