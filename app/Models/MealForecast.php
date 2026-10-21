<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MealForecast extends Model
{
    use HasFactory;

    protected $fillable = [
        'day',
        'expected_count',
        'note',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'day' => 'date',
        'expected_count' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** توقّع يوم محدّد (أو null إن لم يُسجّل) */
    public static function forDay(DateTimeInterface|string|null $day): ?self
    {
        if (blank($day)) {
            return null;
        }

        $date = $day instanceof DateTimeInterface ? $day->format('Y-m-d') : (string) $day;

        return static::query()->whereDate('day', $date)->first();
    }
}
