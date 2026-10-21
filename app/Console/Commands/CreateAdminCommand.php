<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin
        {--username= : اسم المستخدم للمدير}
        {--password= : كلمة المرور (يُفضل تركها فارغة لإدخالها بشكل آمن)}
        {--name= : الاسم الكامل للمدير}';

    protected $description = 'إنشاء أو تحديث حساب المدير الأول بطريقة آمنة';

    public function handle(): int
    {
        $username = $this->option('username') ?: env('INITIAL_ADMIN_USERNAME') ?: 'admin';
        $password = $this->option('password') ?: env('INITIAL_ADMIN_PASSWORD') ?: null;
        $name = $this->option('name') ?: 'مدير النظام';

        if (blank($password)) {
            $password = $this->secret('أدخل كلمة مرور المدير (لن تظهر أثناء الكتابة)');
            $confirm = $this->secret('أعد إدخال كلمة المرور للتأكيد');

            if ($password !== $confirm) {
                $this->error('كلمتا المرور غير متطابقتين.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(
            ['username' => $username, 'password' => $password],
            [
                'username' => ['required', 'string', 'max:100', 'alpha_dash'],
                'password' => ['required', Password::min(8)],
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->where('username', $username)->first();

        if ($user) {
            $user->forceFill([
                'name' => $name,
                'role' => User::ROLE_ADMIN,
                'is_active' => true,
                'password' => $password,
            ])->save();

            $this->info('تم تحديث حساب المدير: '.$username);

            return self::SUCCESS;
        }

        User::create([
            'name' => $name,
            'username' => $username,
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
            'password' => $password,
        ]);

        $this->info('تم إنشاء حساب المدير: '.$username);
        $this->warn('احتفظ بكلمة المرور في مكان آمن، ولا تضعها في المستودع.');

        return self::SUCCESS;
    }
}
