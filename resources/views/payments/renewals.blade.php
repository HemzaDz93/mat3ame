@extends('layouts.app')

@section('title', 'التجديد والانتهاء')
@section('page_title', 'الانتهاء القريب والتجديد')

@php
    $isEmployees = $type === 'employee';
    $tabQuery = $isEmployees ? ['tab' => 'employees'] : [];
@endphp

@section('topbar_actions')
    <a href="{{ route('payments.index', $tabQuery) }}" class="btn btn-secondary btn-sm"><x-icon name="credit-card" /> السدادات</a>
    <a href="{{ route('payments.debts', $tabQuery) }}" class="btn btn-secondary btn-sm"><x-icon name="alert" /> المتأخرات</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="d-flex gap-1">
                <a href="{{ route('payments.renewals') }}" class="btn {{ $isEmployees ? 'btn-secondary' : 'btn-primary' }} btn-sm">
                    <x-icon name="users" /> سدادات المتربصين
                </a>
                <a href="{{ route('payments.renewals', ['tab' => 'employees']) }}" class="btn {{ $isEmployees ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                    <x-icon name="user" /> سدادات الموظفين
                </a>
            </div>
        </div>
    </div>

    <div class="stat-grid mb-2">
        <div class="stat-card is-warning">
            <div class="stat-icon"><x-icon name="calendar" /></div>
            <div>
                <div class="stat-value">{{ number_format($expiringCount) }}</div>
                <div class="stat-label">تنتهي خلال {{ $alertDays }} أيام</div>
            </div>
        </div>
        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="alert" /></div>
            <div>
                <div class="stat-value">{{ number_format($expiredCount) }}</div>
                <div class="stat-label">انتهى سريانها</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><x-icon name="refresh" /> {{ $isEmployees ? 'سدادات الموظفين' : 'سدادات المتربصين' }} المرشّحة للتجديد</h3>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('settings.edit') }}" class="btn btn-secondary btn-sm"><x-icon name="settings" /> مدة التنبيه</a>
            @endif
        </div>

        @if($renewable)
            <form method="POST" action="{{ route('payments.renew-bulk') }}">
                @csrf
                <input type="hidden" name="type" value="{{ $type }}">
        @endif

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        @if($renewable)
                            <th style="width:36px;"><input type="checkbox" title="تحديد الكل" onclick="document.querySelectorAll('.renew-check').forEach(function (c) { c.checked = this.checked; }.bind(this))"></th>
                        @endif
                        <th>{{ $isEmployees ? 'الموظف' : 'المتربص' }}</th>
                        <th>{{ $isEmployees ? 'رقم الموظف' : 'رقم التسجيل' }}</th>
                        <th>نهاية السداد</th>
                        <th>المتبقّي</th>
                        <th>النوع الحالي</th>
                        <th>نوع التجديد</th>
                        <th>يبدأ من</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $payment)
                        @php
                            $subject = $isEmployees ? $payment->employee : $payment->trainee;
                            $daysLeft = $payment->daysLeft();
                            $start = $payment->end_date->copy()->startOfDay()->addDay();
                            if ($start->isPast()) { $start = now(); }
                        @endphp
                        <tr>
                            @if($renewable)
                                <td>
                                    <input type="checkbox" class="renew-check" name="payment_ids[]" value="{{ $payment->id }}">
                                </td>
                            @endif
                            <td>{{ $subject?->full_name ?? '—' }}</td>
                            <td class="cell-nowrap">{{ ($isEmployees ? $subject?->emp_no : $subject?->reg_no) ?? '—' }}</td>
                            <td class="cell-nowrap cell-muted">{{ ar_date($payment->end_date) }}</td>
                            <td class="cell-nowrap">
                                @if($daysLeft >= 0)
                                    <span class="badge badge-warning">متبقٍ {{ $daysLeft }} يوم</span>
                                @else
                                    <span class="badge badge-danger">متأخر {{ abs($daysLeft) }} يوم</span>
                                @endif
                            </td>
                            <td>{{ $payment->planLabel() }}</td>
                            <td>
                                @if($renewable)
                                    <select name="plans[{{ $payment->id }}]" class="form-control form-control-sm">
                                        @foreach($plans as $value => $label)
                                            <option value="{{ $value }}" @selected($payment->plan === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    {{ $payment->planLabel() }}
                                @endif
                            </td>
                            <td class="cell-nowrap cell-muted">{{ ar_date($start) }}</td>
                            <td>
                                <a class="btn btn-secondary btn-sm" target="_blank" rel="noopener"
                                   href="{{ $isEmployees ? route('payments.employees.receipt', $payment) : route('payments.receipt', $payment) }}">
                                    <x-icon name="print" /> وصل
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $renewable ? 9 : 8 }}" class="empty-state">
                                لا توجد سدادات منتهية أو تنتهي خلال {{ $alertDays }} أيام.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($renewable && $rows->isNotEmpty())
            <div class="card-body">
                <div class="form-group mb-1">
                    <label for="renew_note">ملاحظة تُضاف إلى السدادات المجدَّدة (اختياري)</label>
                    <input id="renew_note" name="note" class="form-control" maxlength="500" placeholder="مثال: تجديد تلقائي">
                </div>
                <button class="btn btn-primary"><x-icon name="refresh" /> تجديد السدادات المحددة</button>
            </div>
        @endif

        @if($renewable)
            </form>
        @endif
    </div>
@endsection
