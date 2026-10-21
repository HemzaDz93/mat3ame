<?php

namespace App\Services;

use App\Models\AppNotification;
use App\Models\User;

class NotificationService
{
    /** إنشاء إشعار لمجموعة من المستخدمين حسب الدور */
    public function notify(array $roles, string $type, string $title, ?string $body = null, string $level = 'info', array $data = []): void
    {
        $users = User::query()->whereIn('role', $roles)->where('is_active', true)->get();

        foreach ($users as $user) {
            AppNotification::create([
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'level' => $level,
                'audience' => implode(',', $roles),
                'data' => $data,
                'user_id' => $user->id,
            ]);
        }
    }

    public function unreadCount(?int $userId = null): int
    {
        return AppNotification::query()
            ->where('user_id', $userId ?? auth()->id())
            ->whereNull('read_at')
            ->count();
    }
}
