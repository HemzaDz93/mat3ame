<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reg_no',
        'electronic_reg_no',
        'last_name',
        'first_name',
        'last_name_fr',
        'first_name_fr',
        'gender',
        'birth_date',
        'birth_place',
        'birth_wilaya',
        'residence_wilaya',
        'address',
        'email',
        'phone',
        'father_name',
        'mother_last_name',
        'mother_name',
        'nin',
        'specialty',
        'diploma',
        'level',
        'training_mode',
        'start_date',
        'end_date',
        'eligible',
        'file_accepted',
        'deliberation_accepted',
        'account_cancelled',
        'special_needs',
        'photo',
        'source_photo',
        'notes',
        'registered_at',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'registered_at' => 'datetime',
        'eligible' => 'boolean',
        'file_accepted' => 'boolean',
        'deliberation_accepted' => 'boolean',
        'account_cancelled' => 'boolean',
    ];

    /** الاسم الكامل */
    public function getFullNameAttribute(): string
    {
        return trim($this->last_name.' '.$this->first_name);
    }

    public function getGenderLabelAttribute(): string
    {
        return $this->gender === 'F' ? 'أنثى' : ($this->gender === 'M' ? 'ذكر' : '—');
    }

    /** هل الملف مقبول (منطق المصدر: file_accepted = 1 و eligible = 1) */
    public function isFileAccepted(): bool
    {
        return (bool) $this->file_accepted && (bool) $this->eligible;
    }

    /** المفتاح المستخدم في البطاقة ورمز QR (رقم التسجيل أولًا) */
    public function identityCode(): string
    {
        return (string) ($this->reg_no ?: $this->electronic_reg_no ?: $this->id);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(Meal::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        return $query->where(function (Builder $q) use ($term) {
            $q->where('last_name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('reg_no', 'like', "%{$term}%")
                ->orWhere('electronic_reg_no', 'like', "%{$term}%")
                ->orWhere('nin', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(! empty($filters['specialty']), fn (Builder $q) => $q->where('specialty', $filters['specialty']))
            ->when(! empty($filters['diploma']), fn (Builder $q) => $q->where('diploma', $filters['diploma']))
            ->when(! empty($filters['level']), fn (Builder $q) => $q->where('level', $filters['level']))
            ->when(! empty($filters['gender']), fn (Builder $q) => $q->where('gender', $filters['gender']))
            ->when(! empty($filters['training_mode']), fn (Builder $q) => $q->where('training_mode', $filters['training_mode']))
            ->when(! empty($filters['start_from']), fn (Builder $q) => $q->whereDate('start_date', '>=', $filters['start_from']))
            ->when(! empty($filters['start_to']), fn (Builder $q) => $q->whereDate('start_date', '<=', $filters['start_to']))
            ->when(! empty($filters['end_from']), fn (Builder $q) => $q->whereDate('end_date', '>=', $filters['end_from']))
            ->when(! empty($filters['end_to']), fn (Builder $q) => $q->whereDate('end_date', '<=', $filters['end_to']))
            ->when(isset($filters['status']) && $filters['status'] !== '', function (Builder $q) use ($filters) {
                match ($filters['status']) {
                    'accepted' => $q->where('file_accepted', 1)->where('eligible', 1),
                    'not_accepted' => $q->where(fn (Builder $w) => $w->where('file_accepted', 0)->orWhere('eligible', 0)),
                    'cancelled' => $q->where('account_cancelled', 1),
                    'eligible' => $q->where('eligible', 1),
                    default => $q,
                };
            });
    }
}
