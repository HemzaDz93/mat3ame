<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * بيانات أولية إلزامية: حساب المدير الأول.
     * لا تُنشئ أي بيانات وهمية هنا. البيانات التجريبية في DemoSeeder.
     */
    public function run(): void
    {
        $username = env('INITIAL_ADMIN_USERNAME') ?: 'admin';
        $password = env('INITIAL_ADMIN_PASSWORD');

        if (blank($password)) {
            $this->command?->warn('لم يتم ضبط INITIAL_ADMIN_PASSWORD. استخدم: php artisan app:create-admin');

            return;
        }

        User::query()->updateOrCreate(
            ['username' => $username],
            [
                'name' => 'مدير النظام',
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'password' => $password,
            ]
        );

        $this->command?->info('تم تهيئة حساب المدير الأول: '.$username);
    }
}
