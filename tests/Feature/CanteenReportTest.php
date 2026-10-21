<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\EmployeeMeal;
use App\Models\EmployeePayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CanteenReportTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'username' => 'admin',
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password' => 'Password123',
        ]);
    }

    private function employee(string $number): Employee
    {
        return Employee::create([
            'emp_no' => $number,
            'last_name' => 'بوعلام',
            'first_name' => 'سعيد',
        ]);
    }

    public function test_employee_meal_is_included_in_the_daily_distribution(): void
    {
        $employee = $this->employee('EMP-000009');

        EmployeeMeal::create([
            'employee_id' => $employee->id,
            'day' => now()->toDateString(),
            'served_at' => now(),
        ]);

        $response = $this->actingAs($this->admin())->get(route('canteen.report'));

        $response->assertOk();
        $response->assertSee('توزيع العمليات حسب التاريخ');
        $response->assertSee(now()->toDateString());
    }

    public function test_approved_employee_payment_is_shown_in_the_report(): void
    {
        $admin = $this->admin();
        $employee = $this->employee('EMP-000010');

        EmployeePayment::create([
            'employee_id' => $employee->id,
            'plan' => EmployeePayment::PLAN_MONTH,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->endOfMonth()->toDateString(),
            'amount' => 1650,
            'status' => EmployeePayment::STATUS_APPROVED,
            'requested_by' => $admin->id,
            'approved_by' => $admin->id,
            'decided_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('canteen.report'));

        $response->assertOk();
        $response->assertSee('السدادات المعتمدة حسب النوع');
        $response->assertSee('السدادات حسب الحالة');
        $response->assertSee('موظفون بسداد ساري');
        $response->assertSee('1 650');
    }
}
