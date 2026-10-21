@extends('layouts.print')

@section('title', 'وصل سداد')
@section('toolbar')
    <span class="cell-muted">رقم الوصل: {{ $code }}</span>
@endsection

@push('styles')
<style>
    .receipt {
        width: 180mm;
        margin: 8mm auto 14mm;
        color: #0f172a;
        font-size: 12.5px;
        line-height: 1.7;
    }
    .receipt-head {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        border-bottom: 2px solid #0e7490;
        padding-bottom: 8px;
    }
    .receipt-head .head-logo { width: 22mm; text-align: center; }
    .receipt-head .head-logo img { max-width: 20mm; max-height: 20mm; }
    .receipt-head .head-body { flex: 1; text-align: center; }
    .receipt-head .head-body .line-1 { font-weight: 700; font-size: 13px; }
    .receipt-head .head-body .line-2 { font-weight: 600; font-size: 12.5px; }
    .receipt-head .head-body .line-3 { font-size: 12px; }
    .receipt-head .head-body .inst { font-weight: 800; font-size: 15px; margin-top: 4px; color: #0e7490; }
    .receipt-head .head-meta { width: 40mm; text-align: left; font-size: 11.5px; }
    .receipt-head .head-meta .box {
        border: 1px solid #94a3b8;
        border-radius: 3px;
        padding: 4px 6px;
        margin-bottom: 4px;
        background: #f8fafc;
    }
    .receipt-title {
        text-align: center;
        margin: 12px 0 14px;
    }
    .receipt-title h1 {
        display: inline-block;
        margin: 0;
        padding: 4px 22px;
        font-size: 19px;
        font-weight: 800;
        border: 2px solid #0e7490;
        border-radius: 4px;
        letter-spacing: 0.5px;
    }
    .receipt-title .sub { font-size: 11.5px; color: #475569; margin-top: 4px; }
    .receipt-section-title {
        font-weight: 700;
        font-size: 12.5px;
        background: #eef2f6;
        border-inline-start: 3px solid #0e7490;
        padding: 3px 8px;
        margin: 0 0 6px;
    }
    .receipt-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }
    .receipt-table th,
    .receipt-table td {
        border: 1px solid #cbd5e1;
        padding: 5px 8px;
        text-align: start;
        vertical-align: middle;
    }
    .receipt-table th {
        background: #f8fafc;
        font-weight: 600;
        width: 32%;
    }
    .receipt-amount {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 2px solid #0e7490;
        border-radius: 4px;
        padding: 8px 14px;
        margin: 0 0 14px;
        background: #f0fbfd;
    }
    .receipt-amount .label { font-weight: 700; font-size: 13px; }
    .receipt-amount .value { font-weight: 800; font-size: 18px; color: #0e7490; }
    .receipt-foot {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 12px;
        margin-top: 18px;
    }
    .receipt-foot .sign {
        flex: 1;
        text-align: center;
        border-top: 1px dashed #94a3b8;
        padding-top: 6px;
        font-weight: 600;
        font-size: 12px;
    }
    .receipt-foot .qr { width: 26mm; text-align: center; font-size: 10px; color: #64748b; }
    .receipt-foot .qr img { width: 24mm; height: 24mm; }
    .receipt-note {
        margin-top: 14px;
        border-top: 1px solid #e2e8f0;
        padding-top: 6px;
        font-size: 11px;
        color: #64748b;
        text-align: center;
    }
    .receipt-badge {
        display: inline-block;
        padding: 1px 8px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid currentColor;
    }
    .receipt-badge.ok { color: #15803d; }
    .receipt-badge.wait { color: #b45309; }
    .receipt-badge.no { color: #b91c1c; }
    @media print {
        .receipt { margin: 0 auto; }
    }
</style>
@endpush

@php
    $isEmployee = $type === 'employee';
    $statusClass = match ($payment->status) {
        'approved' => 'ok',
        'pending' => 'wait',
        default => 'no',
    };
    $details = $isEmployee
        ? [
            'الاسم واللقب' => $subject?->full_name ?? '—',
            'رقم الموظف' => $subject?->emp_no ?? '—',
            'الرتبة' => $subject?->rank ?: '—',
        ]
        : [
            'الاسم واللقب' => $subject?->full_name ?? '—',
            'رقم التسجيل' => $subject?->reg_no ?? '—',
            'الاختصاص' => $subject?->specialty ?: '—',
            'المستوى' => $subject?->level ?: '—',
        ];
    $qrPayload = $code.'|'.$payment->amount.'|'.$payment->start_date?->format('Y-m-d').'|'.$payment->end_date?->format('Y-m-d');
@endphp

@section('content')
    <div class="receipt">
        <div class="receipt-head">
            <div class="head-logo">
                @if(setting('institution_logo'))
                    <img src="{{ route('settings.logo') }}" alt="الشعار">
                @endif
            </div>

            <div class="head-body">
                <div class="line-1">{{ setting('country', 'الجمهورية الجزائرية الديمقراطية الشعبية') }}</div>
                <div class="line-2">{{ setting('ministry', 'وزارة التكوين والتعليم المهنيين') }}</div>
                @if(setting('directorate'))
                    <div class="line-3">{{ setting('directorate') }}</div>
                @endif
                <div class="inst">{{ setting('institution_name', 'منصة الزاوية') }}</div>
            </div>

            <div class="head-meta">
                <div class="box">رقم الوصل: <strong>{{ $code }}</strong></div>
                <div class="box">تاريخ الإصدار: <strong>{{ now()->format('Y-m-d') }}</strong></div>
            </div>
        </div>

        <div class="receipt-title">
            <h1>وصل سداد</h1>
            <div class="sub">{{ $isEmployee ? 'سداد وجبات موظف' : 'سداد وجبات متربص' }} — نسخة للطباعة</div>
        </div>

        <div class="receipt-section-title">بيانات المستفيد</div>
        <table class="receipt-table">
            <tbody>
                <tr>
                    @foreach($details as $label => $value)
                        <th>{{ $label }}</th>
                        <td>{{ $value }}</td>
                        @if($loop->iteration % 2 === 0 && ! $loop->last)
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </tbody>
        </table>

        <div class="receipt-section-title">تفاصيل السداد</div>
        <table class="receipt-table">
            <tbody>
                <tr>
                    <th>نوع السداد</th>
                    <td>{{ $payment->planLabel() }}</td>
                    <th>الحالة</th>
                    <td><span class="receipt-badge {{ $statusClass }}">{{ $payment->statusLabel() }}</span></td>
                </tr>
                <tr>
                    <th>فترة السريان</th>
                    <td colspan="3" style="text-align:center;font-weight:600;">
                        من {{ ar_date($payment->start_date) }} إلى {{ ar_date($payment->end_date) }}
                    </td>
                </tr>
                <tr>
                    <th>طالب السداد</th>
                    <td>{{ $payment->requester?->name ?? '—' }}</td>
                    <th>تاريخ الطلب</th>
                    <td>{{ ar_date($payment->created_at) }}</td>
                </tr>
                <tr>
                    <th>المعتمد</th>
                    <td>{{ $payment->approver?->name ?? '—' }}</td>
                    <th>تاريخ الاعتماد</th>
                    <td>{{ $payment->decided_at ? ar_date($payment->decided_at) : '—' }}</td>
                </tr>
                @if($payment->note)
                    <tr>
                        <th>ملاحظة</th>
                        <td colspan="3">{{ $payment->note }}</td>
                    </tr>
                @endif
            </tbody>
        </table>

        <div class="receipt-amount">
            <span class="label">المبلغ الإجمالي المسدَّد</span>
            <span class="value">{{ money($payment->amount) }}</span>
        </div>

        <div class="receipt-foot">
            <div class="sign">إمضاء المستفيد</div>
            <div class="qr">
                <img src="{{ route('barcode.qr', $qrPayload) }}" alt="رمز QR">
                <div>تحقّق من الوصل</div>
            </div>
            <div class="sign">ختم وتوقيع أمين المال</div>
        </div>

        <div class="receipt-note">
            {{ setting('institution_name', 'منصة الزاوية') }}
            @if(setting('institution_address')) — {{ setting('institution_address') }} @endif
            @if(setting('institution_phone')) — الهاتف: {{ setting('institution_phone') }} @endif
        </div>
    </div>
@endsection
