@extends('layouts.app')

@section('title', 'الإشعارات')
@section('page_title', 'الإشعارات')

@section('topbar_actions')
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button class="btn btn-secondary btn-sm"><x-icon name="check" /> تعليم الكل كمقروء</button>
    </form>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="filter-bar">
                <a href="{{ route('notifications.index') }}" class="btn {{ request('filter') === 'unread' ? 'btn-secondary' : 'btn-primary' }} btn-sm">الكل</a>
                <a href="{{ route('notifications.index', ['filter' => 'unread']) }}" class="btn {{ request('filter') === 'unread' ? 'btn-primary' : 'btn-secondary' }} btn-sm">غير المقروءة</a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>النوع</th>
                        <th>العنوان</th>
                        <th>التفاصيل</th>
                        <th>التاريخ</th>
                        <th>الحالة</th>
                        <th>إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notifications as $notification)
                        <tr>
                            <td>
                                <span class="badge badge-{{ $notification->level === 'danger' ? 'danger' : ($notification->level === 'warning' ? 'warning' : ($notification->level === 'success' ? 'success' : 'info')) }}">
                                    {{ $notification->type }}
                                </span>
                            </td>
                            <td>{{ $notification->title }}</td>
                            <td class="cell-muted">{{ $notification->body ?: '—' }}</td>
                            <td class="cell-nowrap cell-muted">{{ ar_date($notification->created_at, 'Y-m-d H:i') }}</td>
                            <td>
                                @if($notification->isRead())
                                    <span class="badge badge-muted">مقروء</span>
                                @else
                                    <span class="badge badge-warning">غير مقروء</span>
                                @endif
                            </td>
                            <td>
                                @unless($notification->isRead())
                                    <form method="POST" action="{{ route('notifications.read', $notification) }}">
                                        @csrf
                                        <button class="btn btn-secondary btn-sm"><x-icon name="check" /> تعليم كمقروء</button>
                                    </form>
                                @else
                                    <span class="cell-muted">—</span>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">لا توجد إشعارات.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $notifications->links('partials.pagination') }}
    </div>
@endsection
