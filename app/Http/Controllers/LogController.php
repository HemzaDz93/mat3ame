<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function index(Request $request): View
    {
        $logs = ActivityLog::query()
            ->with('user')
            ->when($request->input('q'), function ($q, $term) {
                $term = trim($term);
                $q->where(fn ($w) => $w->where('description', 'like', "%{$term}%")
                    ->orWhere('action', 'like', "%{$term}%"));
            })
            ->when($request->input('action'), fn ($q, $action) => $q->where('action', $action))
            ->when($request->input('user'), fn ($q, $userId) => $q->where('user_id', $userId))
            ->when($request->input('result'), fn ($q, $result) => $q->where('result', $result))
            ->when($request->input('from'), fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->input('to'), fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('id')
            ->paginate(40)
            ->withQueryString();

        return view('logs.index', [
            'logs' => $logs,
            'actions' => ActivityLog::query()->whereNotNull('action')->distinct()->orderBy('action')->pluck('action'),
            'users' => User::query()->orderBy('name')->get(['id', 'name', 'username']),
        ]);
    }
}
