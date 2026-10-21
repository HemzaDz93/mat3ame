<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $staff;

    private User $admin;

    private Trainee $trainee;

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

        $this->trainee = Trainee::create([
            'reg_no' => '20260001',
            'electronic_reg_no' => 'E000001',
            'last_name' => 'زيتوني',
            'first_name' => 'محمد',
            'eligible' => true,
            'file_accepted' => true,
        ]);
    }

    public function test_staff_request_creates_a_pending_payment_with_computed_amount(): void
    {
        $result = app(PaymentService::class)->create(
            $this->trainee,
            Payment::PLAN_MONTH,
            '2026-01-01',
            $this->staff
        );

        $this->assertTrue($result['ok']);
        $this->assertFalse($result['skipped']);

        $payment = $result['payment'];

        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('1100.00', $payment->amount);
        $this->assertSame('2026-01-31', $payment->end_date->toDateString());
        $this->assertSame($this->staff->id, $payment->requested_by);
        $this->assertNull($payment->approved_by);
    }

    public function test_admin_can_create_a_directly_approved_payment(): void
    {
        $result = app(PaymentService::class)->create(
            $this->trainee,
            Payment::PLAN_DAY,
            '2026-01-01',
            $this->admin,
            null,
            true
        );

        $this->assertTrue($result['ok']);
        $this->assertSame(Payment::STATUS_APPROVED, $result['payment']->status);
        $this->assertSame($this->admin->id, $result['payment']->approved_by);
        $this->assertNotNull($result['payment']->decided_at);
    }

    public function test_staff_cannot_directly_approve_a_payment(): void
    {
        $result = app(PaymentService::class)->create(
            $this->trainee,
            Payment::PLAN_DAY,
            '2026-01-01',
            $this->staff,
            null,
            true
        );

        $this->assertSame(Payment::STATUS_PENDING, $result['payment']->status);
    }

    public function test_overlapping_period_is_skipped(): void
    {
        $service = app(PaymentService::class);

        $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->admin);

        $second = $service->create($this->trainee, Payment::PLAN_DAY, '2026-01-15', $this->admin);

        $this->assertFalse($second['ok']);
        $this->assertTrue($second['skipped']);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_non_overlapping_period_is_accepted(): void
    {
        $service = app(PaymentService::class);

        $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->admin);

        $second = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-03-01', $this->admin);

        $this->assertTrue($second['ok']);
        $this->assertDatabaseCount('payments', 2);
    }

    public function test_a_rejected_period_does_not_block_a_new_one(): void
    {
        $service = app(PaymentService::class);

        $first = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->staff);
        $service->decide($first['payment'], 'reject', $this->admin);

        $second = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->staff);

        $this->assertTrue($second['ok']);
    }

    public function test_invalid_plan_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        app(PaymentService::class)->create($this->trainee, 'yearly', '2026-01-01', $this->admin);
    }

    public function test_supervisor_can_approve_a_pending_payment(): void
    {
        $supervisor = User::factory()->create([
            'username' => 'supervisor',
            'role' => User::ROLE_SUPERVISOR,
            'is_active' => true,
            'password' => 'Password123',
        ]);

        $service = app(PaymentService::class);
        $payment = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->staff)['payment'];

        $result = $service->decide($payment, 'approve', $supervisor);

        $this->assertTrue($result['ok']);
        $this->assertSame(Payment::STATUS_APPROVED, $payment->fresh()->status);
        $this->assertSame($supervisor->id, $payment->fresh()->approved_by);
    }

    public function test_staff_cannot_decide_on_a_payment(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->staff)['payment'];

        $result = $service->decide($payment, 'approve', $this->staff);

        $this->assertFalse($result['ok']);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_an_approved_payment_can_be_cancelled(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->admin, null, true)['payment'];

        $result = $service->decide($payment, 'cancel', $this->admin, 'خطأ في التسجيل');

        $this->assertTrue($result['ok']);
        $this->assertSame(Payment::STATUS_CANCELLED, $payment->fresh()->status);
        $this->assertStringContainsString('خطأ في التسجيل', $payment->fresh()->note);
    }

    public function test_a_pending_payment_cannot_be_cancelled(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->staff)['payment'];

        $result = $service->decide($payment, 'cancel', $this->admin);

        $this->assertFalse($result['ok']);
        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_an_already_approved_payment_cannot_be_approved_again(): void
    {
        $service = app(PaymentService::class);
        $payment = $service->create($this->trainee, Payment::PLAN_MONTH, '2026-01-01', $this->admin, null, true)['payment'];

        $result = $service->decide($payment, 'approve', $this->admin);

        $this->assertFalse($result['ok']);
    }

    public function test_creating_a_payment_writes_an_audit_log(): void
    {
        app(PaymentService::class)->create($this->trainee, Payment::PLAN_DAY, '2026-01-01', $this->admin, null, true);

        $this->assertDatabaseHas('activity_logs', ['action' => 'payment.created_approved']);
    }
}
