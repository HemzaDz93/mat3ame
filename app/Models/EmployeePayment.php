<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeePayment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';

    public const PLAN_DAY = 'day';
    public const PLAN_MONTH = 'month';
    public const PLAN_QUARTER = 'quarter';

    protected $fillable = [
        'employee_id',
        'plan',
        'start_date',
        'end_date',
        'amount',
        'status',
        'requested_by',
        'approved_by',
        'note',
        'decided_at',
        'expiry_notified_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'amount' => 'decimal:2',
        'decided_at' => 'datetime',
        'expiry_notified_at' => 'datetime',
    ];

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'معلق',
            self::STATUS_APPROVED => 'معتمد',
            self::STATUS_REJECTED => 'مرفوض',
            self::STATUS_CANCELLED => 'ملغى',
        ];
    }

    public static function plans(): array
    {
        return [
            self::PLAN_DAY => 'يوم واحد',
            self::PLAN_MONTH => 'شهر واحد',
            self::PLAN_QUARTER => 'ثلاثة أشهر',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? $this->status;
    }

    public function planLabel(): string
    {
        return self::plans()[$this->plan] ?? $this->plan;
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function coversDate(\DateTimeInterface|string $date): bool
    {
        $date = $date instanceof \DateTimeInterface ? $date->format('Y-m-d') : $date;

        return $this->start_date->format('Y-m-d') <= $date
            && $this->end_date->format('Y-m-d') >= $date;
    }

    /** عدد الأيام المتبقية حتى انتهاء السداد (سالب إذا انتهى) */
    public function daysLeft(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }

    /** هل انتهى السداد؟ */
    public function hasExpired(): bool
    {
        return $this->end_date->format('Y-m-d') < now()->toDateString();
    }

    /** السدادات المعتمدة التي ينتهي سريانها بين تاريخين */
    public function scopeExpiringBetween(Builder $query, string $from, string $to): Builder
    {
        return $query
            ->where('status', self::STATUS_APPROVED)
            ->whereDate('end_date', '>=', $from)
            ->whereDate('end_date', '<=', $to);
    }
}
