@extends('layouts.app')

@section('title', 'بطاقات الموظفين')
@section('page_title', 'بطاقات الموظفين')

@section('page_sub', 'اختر الموظفين ثم اطبع بطاقاتهم (10 بطاقات في صفحة A4)')

@section('topbar_actions')
    <a href="{{ route('cards.index') }}" class="btn btn-secondary btn-sm"><x-icon name="users" /> بطاقات المتربصين</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <div class="form-group grow">
                    <label>بحث</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="رقم الموظف، الاسم، اللقب">
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="search" /> بحث</button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('employees.cards.print') }}" target="_blank">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3>الموظفون ({{ number_format($employees->total()) }})</h3>
                <div class="grow"></div>
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="print" /> طباعة البطاقات المحددة</button>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" data-select-all></th>
                            <th>الصورة</th>
                            <th>رقم الموظف</th>
                            <th>الاسم واللقب</th>
                            <th>الرتبة</th>
                            <th>تاريخ التوظيف</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employees as $employee)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $employee->id }}" data-row-check></td>
                                <td>
                                    @if($employee->photo)
                                        <img src="{{ route('employees.photo', $employee) }}" class="avatar-sm" alt="">
                                    @else
                                        <div class="avatar-placeholder">{{ mb_substr($employee->last_name, 0, 1) }}</div>
                                    @endif
                                </td>
                                <td class="cell-nowrap">{{ $employee->emp_no ?: '—' }}</td>
                                <td>{{ $employee->full_name }}</td>
                                <td>{{ $employee->rank ?: '—' }}</td>
                                <td class="cell-nowrap">{{ ar_date($employee->hire_date) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty-state">لا توجد سجلات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $employees->links('partials.pagination') }}
        </div>
    </form>
@endsection
