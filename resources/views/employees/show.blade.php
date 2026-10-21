@extends('layouts.app')

@section('title', 'ملف الموظف')
@section('page_title', 'ملف الموظف')

@section('topbar_actions')
    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-secondary btn-sm"><x-icon name="edit" /> تعديل</a>
    <a href="{{ route('employees.cards', ['ids' => $employee->id]) }}" class="btn btn-secondary btn-sm"><x-icon name="id-card" /> بطاقة</a>
@endsection

@section('content')
    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3>بيانات الموظف</h3></div>
            <div class="card-body">
                <div class="person-card">
                    @if($employee->photo)
                        <img class="photo" src="{{ route('employees.photo', $employee) }}" alt="">
                    @else
                        <div class="photo"></div>
                    @endif
                    <div>
                        <div class="p-name">{{ $employee->full_name }}</div>
                        <div class="subtle">{{ $employee->rank ?: '—' }}</div>
                        <div class="check-row">
                            <span class="badge badge-primary">{{ $employee->emp_no }}</span>
                            <span class="badge badge-info">{{ number_format($employee->meals_count) }} وجبة</span>
                        </div>
                    </div>
                </div>

                <ul class="info-list mt-2">
                    <li><span class="k">تاريخ ومكان الميلاد</span><span class="v">{{ ar_date($employee->birth_date) }} — {{ $employee->birth_place ?: '—' }}</span></li>
                    <li><span class="k">تاريخ التوظيف</span><span class="v">{{ ar_date($employee->hire_date) }}</span></li>
                    <li><span class="k">الرتبة</span><span class="v">{{ $employee->rank ?: '—' }}</span></li>
                    <li><span class="k">ملاحظات</span><span class="v">{{ $employee->notes ?: '—' }}</span></li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>آخر الوجبات</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>اليوم</th><th>وقت التسليم</th><th>المستخدم</th></tr></thead>
                    <tbody>
                        @forelse($meals as $meal)
                            <tr>
                                <td class="cell-nowrap">{{ ar_date($meal->day) }}</td>
                                <td class="cell-nowrap cell-muted">{{ $meal->served_at ? ar_date($meal->served_at, 'H:i') : '—' }}</td>
                                <td>{{ $meal->server?->name ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="empty-state">لا توجد وجبات مسجلة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
