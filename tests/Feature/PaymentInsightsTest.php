<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeePayment;
use App\Models\Meal;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Services\PaymentInsightsService;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentInsightsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = User::ROLE_ADMIN): User
    {
        return User::factory()->create([
            'username' => 'user_'.$role,
            'role' => $role,
            'is_active' => true,
            'password' => 'Password123',
        ]);
    }

    private function trainee(string $regNo = '3001'): Trainee
    {
        return Trainee::create([
            'reg_no' => $regNo,
            'electronic_reg_no' => 'E'.$regNo,
            'last_name' => 'بلقاسم',
            'first_name' => 'ياسين',
            'specialty' => 'إعلام آلي',
        ]);
    }

    private function payment(Trainee $trainee, string $start, string $end, string $status = Payment::STATUS_APPROVED): Payment
    {
        return Payment::create([
            'trainee_id' => $trainee->id,
            'plan' => Payment::PLAN_MONTH,
            'start_date' => $start,
            'end_date' => $end,
            'amount' => 1100,
            'status' => $status,
        ]);
    }

    public function test_renewals_screen_lists_a_payment_expiring_soon(): void
    {
        $trainee = $this->trainee();
        $this->payment($trainee, now()->subDays(25)->toDateString(), now()->addDays(3)->toDateString());

        $this->actingAs($this->user())
            ->get(route('payments.renewals'))
            ->assertOk()
            ->assertSee('بلقاسم')
            ->assertSee('تنتهي خلال 7 أيام')
            ->assertSee('متبقٍ 3 يوم');
    }

    public function test_alert_days_setting_controls_the_alert_window(): void
    {
        $trainee = $this->trainee();
        $this->payment($trainee, now()->subDays(20)->toDateString(), now()->addDays(5)->toDateString());

        $insights = app(PaymentInsightsService::class);
        $this->assertCount(1, $insights->renewalCandidates('trainee'));

        app(SettingsService::class)->set('expiry_alert_days', '3');

        $this->assertCount(0, $insights->renewalCandidates('trainee'));
    }

    public function test_expired_payment_appears_in_the_debts_list_with_its_owed_amount(): void
    {
        $trainee = $this->trainee();
        $this->payment($trainee, now()->subDays(20)->toDateString(), now()->subDays(5)->toDateString());

        // وجبتان بعد تاريخ انتهاء السداد
        foreach ([4, 3] as $daysAgo) {
            Meal::create([
                'trainee_id' => $trainee->id,
                'day' => now()->subDays($daysAgo)->toDateString(),
                'served_at' => now(),
            ]);
        }

        $debts = app(PaymentInsightsService::class)->traineeDebts();

        $this->assertCount(1, $debts);
        $this->assertSame(2, $debts->first()['meals']);
        $this->assertSame(5, $debts->first()['days_overdue']);
        $this->assertSame(100.0, $debts->first()['amount']);

        $this->actingAs($this->user())
            ->get(route('payments.debts'))
            ->assertOk()
            ->assertSee('بلقاسم')
            ->assertSee('100 دج');
    }

    public function test_employee_debts_use_the_employee_meal_price(): void
    {
        $employee = Employee::create(['emp_no' => 'EMP-9001', 'first_name' => 'أمين', 'last_name' => 'زروقي']);

        EmployeePayment::create([
            'employee_id' => $employee->id,
            'plan' => EmployeePayment::PLAN_MONTH,
            'start_date' => now()->subDays(20)->toDateString(),
            'end_date' => now()->subDays(5)->toDateString(),
            'amount' => 1100,
            'status' => EmployeePayment::STATUS_APPROVED,
        ]);

        EmployeeMeal::create([
            'employee_id' => $employee->id,
            'day' => now()->subDays(2)->toDateString(),
            'served_at' => now(),
        ]);

        $debts = app(PaymentInsightsService::class)->employeeDebts();

        $this->assertCount(1, $debts);
        $this->assertSame(50.0, $debts->first()['amount']);
    }

    public function test_bulk_renewal_creates_an_approved_payment_after_the_current_period(): void
    {
        $trainee = $this->trainee();
        $payment = $this->payment($trainee, now()->subDays(25)->toDateString(), now()->addDays(2)->toDateString());

        $this->actingAs($this->user())
            ->post(route('payments.renew-bulk'), [
                'type' => 'trainee',
                'payment_ids' => [$payment->id],
                'plans' => [$payment->id => Payment::PLAN_MONTH],
            ])
            ->assertRedirect(route('payments.renewals', ['tab' => 'trainees']));

        $expectedStart = now()->addDays(3)->toDateString();

        $renewed = Payment::query()
            ->where('trainee_id', $trainee->id)
            ->whereKeyNot($payment->id)
            ->first();

        $this->assertNotNull($renewed);
        $this->assertSame($expectedStart, $renewed->start_date->toDateString());
        $this->assertSame(Payment::STATUS_APPROVED, $renewed->status);
        $this->assertSame(Payment::PLAN_MONTH, $renewed->plan);
    }

    public function test_staff_cannot_perform_a_bulk_renewal(): void
    {
        $trainee = $this->trainee();
        $payment = $this->payment($trainee, now()->subDays(25)->toDateString(), now()->addDays(2)->toDateString());

        $this->actingAs($this->user(User::ROLE_STAFF))
            ->post(route('payments.renew-bulk'), [
                'type' => 'trainee',
                'payment_ids' => [$payment->id],
                'plans' => [$payment->id => Payment::PLAN_MONTH],
            ])
            ->assertForbidden();
    }

    public function test_trainee_receipt_is_printable(): void
    {
        $trainee = $this->trainee();
        $payment = $this->payment($trainee, now()->subDays(5)->toDateString(), now()->addDays(5)->toDateString());

        $this->actingAs($this->user())
            ->get(route('payments.receipt', $payment))
            ->assertOk()
            ->assertSee('وصل سداد')
            ->assertSee('بلقاسم')
            ->assertSee('P'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_employee_receipt_is_printable(): void
    {
        $employee = Employee::create(['emp_no' => 'EMP-9002', 'first_name' => 'أمين', 'last_name' => 'زروقي']);

        $payment = EmployeePayment::create([
            'employee_id' => $employee->id,
            'plan' => EmployeePayment::PLAN_DAY,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'amount' => 50,
            'status' => EmployeePayment::STATUS_APPROVED,
        ]);

        $this->actingAs($this->user())
            ->get(route('payments.employees.receipt', $payment))
            ->assertOk()
            ->assertSee('وصل سداد')
            ->assertSee('زروقي')
            ->assertSee('M'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT));
    }

    public function test_expiry_alerts_command_notifies_once_per_payment(): void
    {
        $this->user();
        $trainee = $this->trainee();
        $payment = $this->payment($trainee, now()->subDays(25)->toDateString(), now()->addDays(2)->toDateString());

        $this->artisan('payments:expiry-alerts')->assertSuccessful();

        $this->assertNotNull($payment->fresh()->expiry_notified_at);
        $this->assertDatabaseCount('app_notifications', 1);
        $this->assertDatabaseHas('app_notifications', ['type' => 'payment.expiring']);

        // تشغيل ثانٍ لا يُكرر التنبيه
        $this->artisan('payments:expiry-alerts')->assertSuccessful();

        $this->assertDatabaseCount('app_notifications', 1);
    }

    public function test_debts_export_returns_a_csv_file(): void
    {
        $trainee = $this->trainee();
        $this->payment($trainee, now()->subDays(20)->toDateString(), now()->subDays(5)->toDateString());

        $this->actingAs($this->user())
            ->get(route('export.payment-debts', 'csv'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
