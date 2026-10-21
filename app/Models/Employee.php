<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emp_no',
        'first_name',
        'last_name',
        'birth_date',
        'birth_place',
        'hire_date',
        'rank',
        'photo',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->last_name.' '.$this->first_name);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(EmployeeMeal::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(EmployeePayment::class);
    }

    /** توليد رقم موظف فريد بصيغة EMP-XXXXXX */
    public static function generateEmpNo(): string
    {
        $last = static::withTrashed()->where('emp_no', 'like', 'EMP-%')
            ->orderByDesc('emp_no')->value('emp_no');

        $next = $last ? ((int) substr($last, 4)) + 1 : 1;

        do {
            $candidate = 'EMP-'.str_pad((string) $next, 6, '0', STR_PAD_LEFT);
            $next++;
        } while (static::withTrashed()->where('emp_no', $candidate)->exists());

        return $candidate;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('emp_no', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%");
        });
    }
}
