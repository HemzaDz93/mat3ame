<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Payment;
use App\Models\User;
use App\Services\EmployeePaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class EmployeePaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $admin;

    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->create([
            'username' => 'staff',
            'role' => User::ROLE_STAFF,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        $this->admin = User::factory()->create([
            'username' => 'admin',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        $this->employee = Employee::create([
            'emp_no' => 'EMP-000001',
            'last_name' => 'بوعلام',
            'first_name' => 'سعيد',
            'rank' => 'مقتصد',
        ]);
    }

    public function test_staff_request_creates_a_pending_employee_payment_with_computed_amount(): void
    {
        app(\App\Services\SettingsService::class)->set('employee_meal_price', '75');

        $result = app(EmployeePaymentService::class)->create(
            $this->employee,
            EmployeePayment::PLAN_MONTH,
            '2026-01-01',
            $this->staff
        );

        $this->assertTrue($result['ok']);
        $this->assertFalse($result['skipped']);

        $payment = $result['payment'];

        $this->assertSame(EmployeePayment::STATUS_PENDING, $payment->status);
        // سعر وجبة الموظف 75 × عدد أيام الشهر 22
        $this->assertSame('1650.00', $payment->amount);
        $this->assertSame('2026-01-31', $payment->end_date->toDateString());
        $this->assertSame($this->staff->id, $payment->requested_by);
        $this->assertNull($payment->approved_by);
    }

    public function test_employee_prices_are_independent_from_trainee_prices(): void
    {
        app(\App\Services\SettingsService::class)->setMany([
            'meal_price' => '50',
            'employee_meal_price' => '75',
        ]);

        $pricing = app(\App\Services\PricingService::class);

        $this->assertSame(50.0, $pricing->priceForPlan(Payment::PLAN_DAY));
        $this->assertSame(75.0, $pricing->priceForEmployeePlan(EmployeePayment::PLAN_DAY));

        $this->assertSame(1100.0, $pricing->priceForPlan(Payment::PLAN_MONTH));
        $this->assertSame(1650.0, $pricing->priceForEmployeePlan(EmployeePayment::PLAN_MONTH));
    }

    public function test_explicit_employee_month_price_overrides_the_derived_one(): void
    {
        app(\App\Services\SettingsService::class)->setMany([
            'employee_meal_price' => '75',
            'employee_price_month' => '1500',
        ]);

        $this->assertSame(1500.0, app(\App\Services\PricingService::class)->priceForEmployeePlan(EmployeePayment::PLAN_MONTH));
    }

    public function test_the_employee_form_shows_the_employee_price(): void
    {
        app(\App\Services\SettingsService::class)->setMany([
            'meal_price' => '50',
            'employee_meal_price' => '75',
        ]);

        $this->actingAs($this->admin)
            ->get(route('payments.create', ['type' => 'employee', 'employee_id' => $this->employee->id]))
            ->assertOk()
            ->assertSee('1 650')
            ->assertDontSee('1 100');
    }

    public function test_admin_can_create_a_directly_approved_employee_payment(): void
    {
        $result = app(EmployeePaymentService::class)->create(
            $this->employee,
            EmployeePayment::PLAN_DAY,
            '2026-01-01',
            $this->admin,
            null,
            true
        );

        $this->assertTrue($result['ok']);
        $this->assertSame(EmployeePayment::STATUS_APPROVED, $result['payment']->status);
        $this->assertSame($this->admin->id, $result['payment']->approved_by);
        $this->assertNotNull($result['payment']->decided_at);
    }

    public function test_staff_cannot_directly_approve_an_employee_payment(): void
    {
        $result = app(EmployeePaymentService::class)->create(
            $this->employee,
            EmployeePayment::PLAN_DAY,
            '2026-01-01',
            $this->staff,
            null,
            true
        );

        $this->assertSame(EmployeePayment::STATUS_PENDING, $result['payment']->status);
    }

    public function test_overlapping_period_is_skipped(): void
    {
        $service = app(EmployeePaymentService::class);

        $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-01-01', $this->admin);

        $second = $service->create($this->employee, EmployeePayment::PLAN_DAY, '2026-01-15', $this->admin);

        $this->assertFalse($second['ok']);
        $this->assertTrue($second['skipped']);
        $this->assertDatabaseCount('employee_payments', 1);
    }

    public function test_non_overlapping_period_is_accepted(): void
    {
        $service = app(EmployeePaymentService::class);

        $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-01-01', $this->admin);

        $second = $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-03-01', $this->admin);

        $this->assertTrue($second['ok']);
        $this->assertDatabaseCount('employee_payments', 2);
    }

    public function test_invalid_plan_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(EmployeePaymentService::class)->create($this->employee, 'yearly', '2026-01-01', $this->admin);
    }

    public function test_supervisor_can_approve_a_pending_employee_payment(): void
    {
        $supervisor = User::factory()->create([
            'username' => 'supervisor',
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        $service = app(EmployeePaymentService::class);
        $payment = $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-01-01', $this->staff)['payment'];

        $result = $service->decide($payment, 'approve', $supervisor);

        $this->assertTrue($result['ok']);
        $this->assertSame(EmployeePayment::STATUS_APPROVED, $payment->fresh()->status);
        $this->assertSame($supervisor->id, $payment->fresh()->approved_by);
    }

    public function test_staff_cannot_decide_on_an_employee_payment(): void
    {
        $service = app(EmployeePaymentService::class);
        $payment = $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-01-01', $this->staff)['payment'];

        $result = $service->decide($payment, 'approve', $this->staff);

        $this->assertFalse($result['ok']);
        $this->assertSame(EmployeePayment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_an_approved_employee_payment_can_be_cancelled(): void
    {
        $service = app(EmployeePaymentService::class);
        $payment = $service->create($this->employee, EmployeePayment::PLAN_MONTH, '2026-01-01', $this->admin, null, true)['payment'];

        $result = $service->decide($payment, 'cancel', $this->admin, 'خطأ في التسجيل');

        $this->assertTrue($result['ok']);
        $this->assertSame(EmployeePayment::STATUS_CANCELLED, $payment->fresh()->status);
        $this->assertStringContainsString('خطأ في التسجيل', $payment->fresh()->note);
    }

    public function test_creating_an_employee_payment_writes_an_audit_log(): void
    {
        app(EmployeePaymentService::class)->create($this->employee, EmployeePayment::PLAN_DAY, '2026-01-01', $this->admin, null, true);

        $this->assertDatabaseHas('activity_logs', ['action' => 'employee_payment.created_approved']);
    }

    public function test_the_create_screen_lists_employees_when_employee_type_is_selected(): void
    {
        $this->actingAs($this->staff)
            ->get(route('payments.create', ['type' => 'employee']))
            ->assertOk()
            ->assertSee('بوعلام')
            ->assertSee('اختيار الموظف');
    }

    public function test_the_create_screen_filters_trainees_by_specialty(): void
    {
        \App\Models\Trainee::create([
            'reg_no' => '20260009',
            'last_name' => 'قاسمي',
            'first_name' => 'ياسمين',
            'specialty' => 'إعلام آلي',
            'eligible' => true,
            'file_accepted' => true,
        ]);

        \App\Models\Trainee::create([
            'reg_no' => '20260010',
            'last_name' => 'زيتوني',
            'first_name' => 'مراد',
            'specialty' => 'خياطة',
            'eligible' => true,
            'file_accepted' => true,
        ]);

        $this->actingAs($this->staff)
            ->get(route('payments.create', ['type' => 'trainee', 'specialty' => 'إعلام آلي']))
            ->assertOk()
            ->assertSee('قاسمي')
            ->assertDontSee('زيتوني');
    }

    public function test_staff_can_register_an_employee_payment_through_the_web_form(): void
    {
        $this->actingAs($this->staff)
            ->post(route('payments.employees.store'), [
                'employee_id' => $this->employee->id,
                'plan' => EmployeePayment::PLAN_MONTH,
                'start_date' => '2026-02-01',
            ])
            ->assertRedirect(route('payments.index', ['tab' => 'employees']));

        $this->assertDatabaseHas('employee_payments', [
            'employee_id' => $this->employee->id,
            'status' => EmployeePayment::STATUS_PENDING,
        ]);
    }

    public function test_the_employees_tab_lists_employee_payments(): void
    {
        app(EmployeePaymentService::class)->create($this->employee, EmployeePayment::PLAN_DAY, '2026-01-01', $this->admin, null, true);

        $this->actingAs($this->admin)
            ->get(route('payments.index', ['tab' => 'employees']))
            ->assertOk()
            ->assertSee('بوعلام');
    }

    public function test_the_employee_payment_form_renders_for_a_selected_employee(): void
    {
        $this->actingAs($this->admin)
            ->get(route('payments.create', ['type' => 'employee', 'employee_id' => $this->employee->id]))
            ->assertOk()
            ->assertSee('سداد للموظف: بوعلام سعيد');
    }

    public function test_admin_can_register_a_directly_approved_employee_payment_through_the_web_form(): void
    {
        $this->actingAs($this->admin)
            ->post(route('payments.employees.store-direct'), [
                'employee_id' => $this->employee->id,
                'plan' => EmployeePayment::PLAN_DAY,
                'start_date' => '2026-02-01',
            ])
            ->assertRedirect(route('payments.index', ['tab' => 'employees']));

        $this->assertDatabaseHas('employee_payments', [
            'employee_id' => $this->employee->id,
            'status' => EmployeePayment::STATUS_APPROVED,
        ]);
    }

    public function test_trainee_payments_stay_isolated_from_employee_payments(): void
    {
        $trainee = \App\Models\Trainee::create([
            'reg_no' => '20260020',
            'last_name' => 'عمراني',
            'first_name' => 'هدى',
            'eligible' => true,
            'file_accepted' => true,
        ]);

        app(\App\Services\PaymentService::class)->create($trainee, Payment::PLAN_DAY, '2026-01-01', $this->admin, null, true);

        $this->assertDatabaseCount('payments', 1);
        $this->assertDatabaseCount('employee_payments', 0);
    }
}
