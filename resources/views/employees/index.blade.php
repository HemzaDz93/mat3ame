@extends('layouts.app')

@section('title', 'الموظفون')
@section('page_title', 'إدارة الموظفين')

@section('topbar_actions')
    <a href="{{ route('employees.cards') }}" class="btn btn-secondary btn-sm"><x-icon name="id-card" /> البطاقات</a>
    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" /> موظف جديد</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <input type="hidden" name="trashed" value="{{ $trashed ? 1 : 0 }}">
                <div class="form-group grow">
                    <label>بحث</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="رقم الموظف أو الاسم أو اللقب">
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="search" /> بحث</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('employees.index', $trashed ? [] : ['trashed' => 1]) }}" class="btn btn-ghost">
                        <x-icon name="restore" /> {{ $trashed ? 'عرض النشطين' : 'عرض المحذوفين' }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>رقم الموظف</th>
                        <th>الاسم واللقب</th>
                        <th>الرتبة</th>
                        <th>عدد الوجبات</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                @if($employee->photo)
                                    <img src="{{ route('employees.photo', $employee) }}" class="avatar-sm" alt="">
                                @else
                                    <div class="avatar-placeholder">{{ mb_substr($employee->last_name, 0, 1) }}</div>
                                @endif
                            </td>
                            <td class="cell-nowrap">{{ $employee->emp_no }}</td>
                            <td>{{ $employee->full_name }}</td>
                            <td>{{ $employee->rank ?: '—' }}</td>
                            <td class="cell-nowrap">{{ number_format($employee->meals_count) }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-sm"><x-icon name="eye" /></a>
                                    @if($trashed)
                                        <form method="POST" action="{{ route('employees.restore', $employee->id) }}" data-confirm="استعادة هذا الموظف؟">
                                            @csrf
                                            <button class="btn btn-success btn-sm"><x-icon name="restore" /> استعادة</button>
                                        </form>
                                    @else
                                        <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm"><x-icon name="edit" /></a>
                                        <form method="POST" action="{{ route('employees.destroy', $employee) }}" data-confirm="حذف هذا الموظف (حذف ناعم)؟">
                                            @csrf
                                            <button class="btn btn-danger btn-sm"><x-icon name="trash" /></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">لا يوجد موظفون مطابقون.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $employees->links('partials.pagination') }}
    </div>
@endsection
