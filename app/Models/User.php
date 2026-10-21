<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_SUPERVISOR = 'supervisor';
    public const ROLE_STAFF = 'staff';
    public const ROLE_CANTEEN = 'canteen';
    public const ROLE_ASSISTANT = 'assistant';

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN => 'مدير',
            self::ROLE_SUPERVISOR => 'مراقب',
            self::ROLE_STAFF => 'موظف إداري',
            self::ROLE_CANTEEN => 'مسيّر المطعم',
            self::ROLE_ASSISTANT => 'مساعد التكوين',
        ];
    }

    public function roleLabel(): string
    {
        return self::roles()[$this->role] ?? $this->role;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSupervisor(): bool
    {
        return $this->role === self::ROLE_SUPERVISOR;
    }

    public function isStaff(): bool
    {
        return $this->role === self::ROLE_STAFF;
    }

    public function isCanteen(): bool
    {
        return $this->role === self::ROLE_CANTEEN;
    }

    public function isAssistant(): bool
    {
        return $this->role === self::ROLE_ASSISTANT;
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    /** الأدوار المخولة بإدارة المستخدمين والإعدادات */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /** الأدوار المخولة باعتماد أو رفض أو إلغاء السداد */
    public function canDecidePayments(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN, self::ROLE_SUPERVISOR);
    }

    /** الأدوار المخولة بالاطلاع على تقارير المطعم المحمية */
    public function canViewCanteenReports(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN, self::ROLE_SUPERVISOR);
    }

    /** الأدوار المخولة بالاستيراد والتصدير ورفع الصور الجماعي */
    public function canBulkImport(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN, self::ROLE_STAFF);
    }

    /** الأدوار المخولة بإعلان العدد المتوقّع لوجبات المتربصين */
    public function canManageMealForecast(): bool
    {
        return $this->hasRole(self::ROLE_ASSISTANT, self::ROLE_ADMIN);
    }
}
