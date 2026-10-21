<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $notifications = AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->when($request->input('filter') === 'unread', fn ($q) => $q->whereNull('read_at'))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
        ]);
    }

    public function readAll(Request $request): RedirectResponse
    {
        AppNotification::query()
            ->where('user_id', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'تم تعليم جميع الإشعارات كمقروءة.');
    }

    public function read(Request $request, AppNotification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        if (! $notification->isRead()) {
            $notification->forceFill(['read_at' => now()])->save();
        }

        return back();
    }
}
