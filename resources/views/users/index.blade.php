@extends('layouts.app')

@section('title', 'المستخدمون')
@section('page_title', 'المستخدمون والأدوار')

@section('topbar_actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm">
        <x-icon name="plus" /> مستخدم جديد
    </a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <div class="form-group grow">
                    <label>بحث</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="الاسم أو اسم المستخدم أو البريد">
                </div>
                <div class="form-group">
                    <label>الدور</label>
                    <select name="role" class="form-control">
                        <option value="">الكل</option>
                        @foreach($roles as $value => $label)
                            <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="search" /> تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>اسم المستخدم</th>
                        <th>الدور</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name ?: '—' }}</td>
                            <td class="cell-nowrap">{{ $user->username }}</td>
                            <td><span class="badge badge-primary">{{ $user->roleLabel() }}</span></td>
                            <td>
                                @if($user->is_active)
                                    <span class="badge badge-success">نشط</span>
                                @else
                                    <span class="badge badge-danger">معطّل</span>
                                @endif
                            </td>
                            <td class="cell-nowrap cell-muted">{{ $user->last_login_at ? ar_date($user->last_login_at, 'Y-m-d H:i') : '—' }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-secondary btn-sm"><x-icon name="edit" /> تعديل</a>

                                    <form method="POST" action="{{ route('users.toggle', $user) }}">
                                        @csrf
                                        <button class="btn btn-warning btn-sm">
                                            <x-icon name="refresh" /> {{ $user->is_active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('users.reset-password', $user) }}"
                                          data-confirm="إعادة تعيين كلمة المرور لهذا المستخدم؟">
                                        @csrf
                                        <button class="btn btn-secondary btn-sm"><x-icon name="key" /> كلمة المرور</button>
                                    </form>

                                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                                          data-confirm="حذف هذا المستخدم نهائيًا؟">
                                        @csrf
                                        <button class="btn btn-danger btn-sm"><x-icon name="trash" /> حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">لا يوجد مستخدمون مطابقون.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $users->links('partials.pagination') }}
    </div>
@endsection
