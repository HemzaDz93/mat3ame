@extends('layouts.app')

@section('title', 'سجل العمليات')
@section('page_title', 'سجل العمليات')

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <div class="form-group grow">
                    <label>بحث</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="الإجراء أو الوصف">
                </div>
                <div class="form-group">
                    <label>الإجراء</label>
                    <select name="action" class="form-control">
                        <option value="">الكل</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>المستخدم</label>
                    <select name="user" class="form-control">
                        <option value="">الكل</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) request('user') === (string) $user->id)>{{ $user->name ?: $user->username }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>النتيجة</label>
                    <select name="result" class="form-control">
                        <option value="">الكل</option>
                        <option value="success" @selected(request('result') === 'success')>ناجح</option>
                        <option value="failed" @selected(request('result') === 'failed')>فاشل</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                </div>
                <div class="form-group"><button class="btn btn-secondary"><x-icon name="filter" /> تصفية</button></div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>التاريخ</th>
                        <th>المستخدم</th>
                        <th>الإجراء</th>
                        <th>الوصف</th>
                        <th>النتيجة</th>
                        <th>عنوان IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td class="cell-nowrap cell-muted">{{ ar_date($log->created_at, 'Y-m-d H:i:s') }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td class="cell-nowrap"><span class="badge badge-muted">{{ $log->action }}</span></td>
                            <td>{{ $log->description ?: '—' }}</td>
                            <td>
                                @if($log->result === 'failed')
                                    <span class="badge badge-danger">فاشل</span>
                                @else
                                    <span class="badge badge-success">ناجح</span>
                                @endif
                            </td>
                            <td class="cell-nowrap cell-muted">{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">لا توجد عمليات مسجّلة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links('partials.pagination') }}
    </div>
@endsection
