<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Payment;
use App\Models\Trainee;
use App\Models\User;
use App\Services\PricingService;
use Illuminate\Database\Seeder;

/**
 * بيانات تجريبية للعرض والتجربة فقط.
 * لا تُشغَّل ضمن DatabaseSeeder الإنتاجي، ولا يجوز تشغيلها على قاعدة بيانات حقيقية.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // لا يُسمح بالبيانات التجريبية إلا في بيئات التطوير/الاختبار (وليس في الإنتاج أو أي بيئة أخرى).
        if (! app()->environment(['local', 'testing'])) {
            $this->command?->error('لا يمكن تشغيل البيانات التجريبية خارج بيئة التطوير (local/testing).');

            return;
        }

        $this->seedUsers();
        $this->seedTrainees();
        $this->seedEmployees();

        $this->command?->info('تم إدراج البيانات التجريبية (DemoSeeder).');
    }

    private function seedUsers(): void
    {
        $accounts = [
            ['username' => 'admin', 'name' => 'مدير النظام', 'role' => User::ROLE_ADMIN],
            ['username' => 'supervisor', 'name' => 'مراقب المصلحة', 'role' => User::ROLE_SUPERVISOR],
            ['username' => 'staff', 'name' => 'موظف إداري', 'role' => User::ROLE_STAFF],
            ['username' => 'canteen', 'name' => 'مسيّر المطعم', 'role' => User::ROLE_CANTEEN],
        ];

        foreach ($accounts as $account) {
            User::query()->updateOrCreate(
                ['username' => $account['username']],
                [
                    'name' => $account['name'],
                    'role' => $account['role'],
                    'is_active' => true,
                    'password' => env('DEMO_PASSWORD') ?: 'Password123',
                ]
            );
        }
    }

    private function seedTrainees(): void
    {
        $pricing = app(PricingService::class);
        $admin = User::query()->where('username', 'admin')->first();

        $rows = [
            ['بن عمر', 'أمينة', 'F', 'إعلام آلي وتسيير قواعد المعطيات', 'متربص', 1, 1, 0, 1],
            ['زيتوني', 'محمد', 'M', 'كهرباء صناعية', 'متربص', 1, 1, 0, 1],
            ['حمداني', 'سارة', 'F', 'خياطة وحرف', 'متربص', 1, 0, 0, 1],
            ['لعموري', 'ياسين', 'M', 'لحام وتلحيم', 'متمهن', 0, 1, 0, 0],
            ['بوزيد', 'نور الهدى', 'F', 'طبخ جماعي', 'متربص', 1, 1, 1, 1],
        ];

        foreach ($rows as $index => [$last, $first, $gender, $specialty, $mode, $eligible, $fileAccepted, $cancelled, $withPayment]) {
            $trainee = Trainee::query()->updateOrCreate(
                ['electronic_reg_no' => 'DEMO'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT)],
                [
                    'reg_no' => '2026'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                    'last_name' => $last,
                    'first_name' => $first,
                    'gender' => $gender,
                    'specialty' => $specialty,
                    'training_mode' => $mode,
                    'birth_place' => 'الجزائر',
                    'birth_wilaya' => 'الجزائر',
                    'residence_wilaya' => 'الجزائر',
                    'phone' => '0550'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                    'nin' => '109'.str_pad((string) ($index + 1), 12, '0', STR_PAD_LEFT),
                    'start_date' => now()->startOfMonth()->toDateString(),
                    'end_date' => now()->addMonths(9)->toDateString(),
                    'eligible' => (bool) $eligible,
                    'file_accepted' => (bool) $fileAccepted,
                    'deliberation_accepted' => (bool) $eligible,
                    'account_cancelled' => (bool) $cancelled,
                    'registered_at' => now(),
                ]
            );

            if (! $withPayment) {
                continue;
            }

            $start = now()->startOfMonth();
            $plan = Payment::PLAN_MONTH;

            Payment::query()->updateOrCreate(
                ['trainee_id' => $trainee->id],
                [
                    'plan' => $plan,
                    'start_date' => $start->toDateString(),
                    'end_date' => $pricing->computeEndDate($start, $plan)->toDateString(),
                    'amount' => $pricing->priceForPlan($plan),
                    'status' => Payment::STATUS_APPROVED,
                    'requested_by' => $admin?->id,
                    'approved_by' => $admin?->id,
                    'decided_at' => now(),
                    'note' => 'سداد تجريبي معتمد.',
                ]
            );
        }
    }

    private function seedEmployees(): void
    {
        $rows = [
            ['قاسمي', 'عبد القادر', 'مقتصد'],
            ['مرابط', 'فاطمة', 'أمين مخزن'],
            ['شريف', 'رشيد', 'حارس'],
        ];

        foreach ($rows as $index => [$last, $first, $rank]) {
            Employee::query()->updateOrCreate(
                ['emp_no' => 'EMP-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT)],
                [
                    'last_name' => $last,
                    'first_name' => $first,
                    'rank' => $rank,
                    'hire_date' => now()->subYears(3)->toDateString(),
                ]
            );
        }
    }
}
