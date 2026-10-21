<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $result = 'success'
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'result' => $result,
            'ip_address' => request()->ip(),
            'created_at' => now(),
        ]);
    }

    /** إزالة الحقول الحساسة من القيم المسجلة */
    private function sanitize(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        $blocked = ['password', 'password_confirmation', 'remember_token', '_token', 'token'];

        return array_diff_key($values, array_flip($blocked));
    }
}
