@extends('layouts.app')

@section('title', 'المتأخرات')
@section('page_title', 'قائمة المتأخرات (الديون)')

@php
    $isEmployees = $type === 'employee';
    $tabQuery = $isEmployees ? ['tab' => 'employees'] : [];
    $exportUrl = route('export.payment-debts', 'csv').'?'.http_build_query($tabQuery);
@endphp

@section('topbar_actions')
    <a href="{{ $exportUrl }}" class="btn btn-secondary btn-sm"><x-icon name="download" /> CSV</a>
    <a href="{{ route('payments.index', $tabQuery) }}" class="btn btn-secondary btn-sm"><x-icon name="credit-card" /> السدادات</a>
    <a href="{{ route('payments.renewals', $tabQuery) }}" class="btn btn-primary btn-sm"><x-icon name="refresh" /> التجديد</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex gap-1">
                <a href="{{ route('payments.debts') }}" class="btn {{ $isEmployees ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                    <x-icon name="users" /> متأخرات المتربصين
                </a>
                <a href="{{ route('payments.debts', ['tab' => 'employees']) }}" class="btn {{ $isEmployees ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                    <x-icon name="user" /> متأخرات الموظفين
                </a>
            </div>
        </div>
    </div>

    <div class="stat-grid mb-2">
        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="alert" /></div>
            <div>
                <div class="stat-value">{{ number_format($totals['count']) }}</div>
                <div class="stat-label">عدد المتأخرين</div>
            </div>
        </div>
        <div class="stat-card is-warning">
            <div class="stat-icon"><x-icon name="utensils" /></div>
            <div>
                <div class="stat-value">{{ number_format($totals['meals']) }}</div>
                <div class="stat-label">وجبات غير مسدَّدة</div>
            </div>
        </div>
        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="credit-card" /></div>
            <div>
                <div class="stat-value">{{ money($totals['amount']) }}</div>
                <div class="stat-label">إجمالي المبلغ المستحق</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><x-icon name="alert" /> {{ $isEmployees ? 'الموظفون' : 'المتربصون' }} الذين انتهى سدادهم ولم يجدّدوا</h3>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>{{ $isEmployees ? 'الموظف' : 'المتربص' }}</th>
                        <th>{{ $isEmployees ? 'رقم الموظف' : 'رقم التسجيل' }}</th>
                        <th>نهاية آخر سداد</th>
                        <th>أيام التأخر</th>
                        <th>وجبات غير مسدَّدة</th>
                        <th>المبلغ المستحق</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $debt)
                        <tr>
                            <td>{{ $debt['subject']->full_name }}</td>
                            <td class="cell-nowrap">{{ ($isEmployees ? $debt['subject']->emp_no : $debt['subject']->reg_no) ?? '—' }}</td>
                            <td class="cell-nowrap cell-muted">{{ ar_date($debt['last_end']) }}</td>
                            <td class="cell-nowrap"><span class="badge badge-danger">{{ $debt['days_overdue'] }} يوم</span></td>
                            <td class="cell-nowrap">{{ number_format($debt['meals']) }}</td>
                            <td class="cell-nowrap">{{ money($debt['amount']) }}</td>
                            <td>
                                <a class="btn btn-primary btn-sm"
                                   href="{{ $isEmployees
                                        ? route('payments.create', ['type' => 'employee', 'employee_id' => $debt['subject']->id])
                                        : route('payments.create', ['trainee_id' => $debt['subject']->id]) }}">
                                    <x-icon name="plus" /> تسجيل سداد
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">لا توجد متأخرات مسجّلة.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">
            <span class="form-hint">
                يُحتسب المبلغ المستحق بضرب عدد الوجبات المستلمة بعد تاريخ انتهاء آخر سداد معتمد في سعر الوجبة المحدد في الإعدادات.
            </span>
        </div>
    </div>
@endsection
