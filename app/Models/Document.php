<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    public const TYPES = [
        'medical' => 'الوثيقة الطبية',
        'school' => 'الوثيقة المدرسية',
        'national_education' => 'التربية الوطنية',
        'apprenticeship_contract' => 'عقد التمهين',
        'training_experience' => 'التكوين والخبرة',
        'birth_certificate' => 'شهادة الميلاد',
        'residence_grant' => 'الإقامة أو المنحة',
        'unemployment_orientation' => 'توجيه البطالة',
        'other' => 'وثيقة أخرى',
    ];

    protected $fillable = [
        'trainee_id',
        'type',
        'path',
        'original_name',
        'mime_type',
        'file_size',
        'uploaded_by',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function isImage(): bool
    {
        return $this->mime_type && str_starts_with($this->mime_type, 'image/');
    }
}
