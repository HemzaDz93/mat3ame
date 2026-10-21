<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Meal extends Model
{
    use HasFactory;

    protected $fillable = ['trainee_id', 'day', 'served_at', 'served_by'];

    protected $casts = [
        'day' => 'date',
        'served_at' => 'datetime',
    ];

    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class)->withTrashed();
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }
}
