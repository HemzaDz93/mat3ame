<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeMeal extends Model
{
    use HasFactory;

    protected $fillable = ['employee_id', 'day', 'served_at', 'served_by'];

    protected $casts = [
        'day' => 'date',
        'served_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class)->withTrashed();
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(User::class, 'served_by');
    }
}
