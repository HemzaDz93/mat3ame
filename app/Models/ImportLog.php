<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportLog extends Model
{
    protected $fillable = [
        'filename',
        'sheet',
        'total_rows',
        'accepted',
        'updated',
        'rejected',
        'skipped',
        'report',
        'status',
        'created_by',
    ];

    protected $casts = [
        'report' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
