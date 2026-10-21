@extends('layouts.app')

@section('title', 'السدادات')
@section('page_title', 'إدارة السدادات')

@php
    $isEmployees = $tab === 'employees';
    $exportUrl = $isEmployees ? route('export.employee-payments', 'csv') : route('export.payments', 'csv');
@endphp

@section('topbar_actions')
    <a href="{{ route('payments.renewals', $isEmployees ? ['tab' => 'employees'] : []) }}" class="btn btn-secondary btn-sm"><x-icon name="refresh" /> التجديد</a>
    <a href="{{ route('payments.debts', $isEmployees ? ['tab' => 'employees'] : []) }}" class="btn btn-secondary btn-sm"><x-icon name="alert" /> المتأخرات</a>
    <a href="{{ $exportUrl }}?{{ http_build_query(request()->query()) }}" class="btn btn-secondary btn-sm"><x-icon name="download" /> CSV</a>
    <a href="{{ route('payments.create', ['type' => $isEmployees ? 'employee' : 'trainee']) }}" class="btn btn-primary btn-sm"><x-icon name="plus" /> سداد جديد</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex gap-1">
                <a href="{{ route('payments.index') }}" class="btn {{ $isEmployees ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                    <x-icon name="users" /> سدادات المتربصين
                </a>
                <a href="{{ route('payments.index', ['tab' => 'employees']) }}" class="btn {{ $isEmployees ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                    <x-icon name="user" /> سدادات الموظفين
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                @if($isEmployees)<input type="hidden" name="tab" value="employees">@endif
                <div class="form-group grow">
                    <label>بحث عن {{ $isEmployees ? 'موظف' : 'متربص' }}</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ $isEmployees ? 'الاسم أو اللقب أو رقم الموظف' : 'الاسم أو اللقب أو رقم التسجيل أو NIN' }}">
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">الكل</option>
                        @foreach($statuses as $value => $label)<option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>النوع</label>
                    <select name="plan" class="form-control">
                        <option value="">الكل</option>
                        @foreach($plans as $value => $label)<option value="{{ $value }}" @selected(request('plan') === $value)>{{ $label }}</option>@endforeach
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
            @if($isEmployees)
                <table class="table">
                    <thead>
                        <tr>
                            <th>الموظف</th>
                            <th>رقم الموظف</th>
                            <th>النوع</th>
                            <th>الفترة</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>طالب السداد</th>
                            <th>المعتمد</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employeePayments as $payment)
                            <tr>
                                <td>{{ $payment->employee?->full_name ?? '—' }}</td>
                                <td class="cell-nowrap">{{ $payment->employee?->emp_no ?? '—' }}</td>
                                <td>{{ $payment->planLabel() }}</td>
                                <td class="cell-nowrap cell-muted">{{ ar_date($payment->start_date) }} → {{ ar_date($payment->end_date) }}</td>
                                <td class="cell-nowrap">{{ money($payment->amount) }}</td>
                                <td><span class="badge badge-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">{{ $payment->statusLabel() }}</span></td>
                                <td>{{ $payment->requester?->name ?? '—' }}</td>
                                <td>{{ $payment->approver?->name ?? '—' }}</td>
                                <td>
                                    @if(auth()->user()->canDecidePayments())
                                        <div class="table-actions">
                                            @if($payment->status === 'pending')
                                                <form method="POST" action="{{ route('payments.employees.decide') }}" class="d-flex gap-1">
                                                    @csrf
                                                    <input type="hidden" name="employee_payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button class="btn btn-success btn-sm"><x-icon name="check" /> اعتماد</button>
                                                </form>
                                                <form method="POST" action="{{ route('payments.employees.decide') }}" data-confirm="رفض هذا السداد؟">
                                                    @csrf
                                                    <input type="hidden" name="employee_payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button class="btn btn-danger btn-sm"><x-icon name="x" /> رفض</button>
                                                </form>
                                            @elseif($payment->status === 'approved')
                                                <form method="POST" action="{{ route('payments.employees.decide') }}" data-confirm="إلغاء هذا السداد المعتمد؟">
                                                    @csrf
                                                    <input type="hidden" name="employee_payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button class="btn btn-warning btn-sm"><x-icon name="x-circle" /> إلغاء</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                    <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('payments.employees.receipt', $payment) }}">
                                        <x-icon name="print" /> وصل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="empty-state">لا توجد سدادات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="table">
                    <thead>
                        <tr>
                            <th>المتربص</th>
                            <th>النوع</th>
                            <th>الفترة</th>
                            <th>المبلغ</th>
                            <th>الحالة</th>
                            <th>طالب السداد</th>
                            <th>المعتمد</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td>{{ $payment->trainee?->full_name ?? '—' }}</td>
                                <td>{{ $payment->planLabel() }}</td>
                                <td class="cell-nowrap cell-muted">{{ ar_date($payment->start_date) }} → {{ ar_date($payment->end_date) }}</td>
                                <td class="cell-nowrap">{{ money($payment->amount) }}</td>
                                <td><span class="badge badge-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">{{ $payment->statusLabel() }}</span></td>
                                <td>{{ $payment->requester?->name ?? '—' }}</td>
                                <td>{{ $payment->approver?->name ?? '—' }}</td>
                                <td>
                                    @if(auth()->user()->canDecidePayments())
                                        <div class="table-actions">
                                            @if($payment->status === 'pending')
                                                <form method="POST" action="{{ route('payments.decide') }}" class="d-flex gap-1">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button class="btn btn-success btn-sm"><x-icon name="check" /> اعتماد</button>
                                                </form>
                                                <form method="POST" action="{{ route('payments.decide') }}" data-confirm="رفض هذا السداد؟">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button class="btn btn-danger btn-sm"><x-icon name="x" /> رفض</button>
                                                </form>
                                            @elseif($payment->status === 'approved')
                                                <form method="POST" action="{{ route('payments.decide') }}" data-confirm="إلغاء هذا السداد المعتمد؟">
                                                    @csrf
                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                    <input type="hidden" name="action" value="cancel">
                                                    <button class="btn btn-warning btn-sm"><x-icon name="x-circle" /> إلغاء</button>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                    <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener" href="{{ route('payments.receipt', $payment) }}">
                                        <x-icon name="print" /> وصل
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">لا توجد سدادات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>
        {{ ($isEmployees ? $employeePayments : $payments)->links('partials.pagination') }}
    </div>
@endsection
