@extends('layouts.app')

@section('title', 'لوحة التحكم')
@section('page_title', 'لوحة التحكم')

@section('content')
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon"><x-icon name="users" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['trainees_total']) }}</div>
                <div class="stat-label">إجمالي المتربصين النشطين</div>
            </div>
        </div>

        <div class="stat-card is-success">
            <div class="stat-icon"><x-icon name="check-circle" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['trainees_accepted']) }}</div>
                <div class="stat-label">الملفات المقبولة</div>
            </div>
        </div>

        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="x-circle" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['trainees_not_accepted']) }}</div>
                <div class="stat-label">الملفات المرفوضة</div>
            </div>
        </div>

        <div class="stat-card is-info">
            <div class="stat-icon"><x-icon name="user-check" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['trainees_eligible']) }}</div>
                <div class="stat-label">المتربصون المؤهلون</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon"><x-icon name="user" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['employees_total']) }}</div>
                <div class="stat-label">الموظفون النشطون</div>
            </div>
        </div>

        <div class="stat-card is-warning">
            <div class="stat-icon"><x-icon name="utensils" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['meals_today']) }}</div>
                <div class="stat-label">وجبات المتربصين اليوم</div>
            </div>
        </div>

        @if($employeeMealsToday !== null)
            <div class="stat-card is-warning">
                <div class="stat-icon"><x-icon name="utensils" /></div>
                <div>
                    <div class="stat-value">{{ number_format($employeeMealsToday) }}</div>
                    <div class="stat-label">وجبات الموظفين اليوم</div>
                </div>
            </div>
        @endif

        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="alert" /></div>
            <div>
                <div class="stat-value">{{ number_format($stats['trainees_cancelled']) }}</div>
                <div class="stat-label">حسابات ملغاة</div>
            </div>
        </div>

        @if($expiringPayments !== null)
            <a class="stat-card is-warning" href="{{ route('payments.renewals') }}" style="color: inherit;">
                <div class="stat-icon"><x-icon name="refresh" /></div>
                <div>
                    <div class="stat-value">{{ number_format($expiringPayments) }}</div>
                    <div class="stat-label">سدادات تنتهي قريبًا — التجديد</div>
                </div>
            </a>
        @endif
    </div>

    <div class="grid-2 mb-2">
        @include('dashboard._distribution', ['title' => 'التوزيع حسب الاختصاص', 'rows' => $bySpecialty, 'key' => 'specialty'])
        @include('dashboard._distribution', ['title' => 'التوزيع حسب الشهادة', 'rows' => $byDiploma, 'key' => 'diploma'])
    </div>

    <div class="grid-2 mb-2">
        @include('dashboard._distribution', ['title' => 'التوزيع حسب المستوى', 'rows' => $byLevel, 'key' => 'level'])
        @include('dashboard._distribution', ['title' => 'التوزيع حسب سنة بداية التكوين', 'rows' => $byStartYear, 'key' => 'year'])
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3>التوزيع حسب الجنس</h3></div>
            <div class="card-body">
                @php
                    $totalGender = max(1, (int) $byGender->sum('total'));
                @endphp
                @forelse($byGender as $row)
                    @php $label = $row->gender === 'F' ? 'أنثى' : ($row->gender === 'M' ? 'ذكر' : 'غير محدد'); @endphp
                    <div class="mb-1">
                        <div class="d-flex justify-between">
                            <span>{{ $label }}</span>
                            <strong>{{ number_format($row->total) }}</strong>
                        </div>
                        <div style="height:8px;background:#eef2f6;border-radius:6px;overflow:hidden;">
                            <div style="height:100%;width:{{ round($row->total / $totalGender * 100) }}%;background:var(--c-primary-600);"></div>
                        </div>
                    </div>
                @empty
                    <div class="subtle">لا توجد بيانات.</div>
                @endforelse
            </div>
        </div>

        @if($pendingPayments !== null)
            <div class="card">
                <div class="card-header">
                    <h3>طلبات السداد المعلقة</h3>
                    <div class="grow"></div>
                    <a href="{{ route('payments.index', ['status' => 'pending']) }}" class="btn btn-secondary btn-sm">عرض الكل</a>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr><th>الشخص</th><th>النوع</th><th>المبلغ</th><th>التاريخ</th></tr>
                        </thead>
                        <tbody>
                            @forelse($pendingPayments as $payment)
                                <tr>
                                    <td>{{ $payment['name'] }}</td>
                                    <td>{{ $payment['type'] }}</td>
                                    <td class="cell-nowrap">{{ money($payment['amount']) }}</td>
                                    <td class="cell-nowrap cell-muted">{{ ar_date($payment['created_at']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="empty-state">لا توجد طلبات معلقة.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
@endsection
