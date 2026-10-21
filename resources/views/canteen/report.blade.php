@extends('layouts.app')

@section('title', 'تقارير المطعم')
@section('page_title', 'تقارير المطعم')

@section('topbar_actions')
    <a href="{{ route('canteen.report.export', ['format' => 'csv'] + request()->query()) }}" class="btn btn-secondary btn-sm"><x-icon name="download" /> CSV</a>
    <a href="{{ route('canteen.report.export', ['format' => 'xlsx'] + request()->query()) }}" class="btn btn-secondary btn-sm"><x-icon name="file-spreadsheet" /> Excel</a>
    <button type="button" class="btn btn-secondary btn-sm no-print" onclick="window.print()"><x-icon name="print" /> طباعة</button>
@endsection

@section('content')
    <div class="card mb-2 no-print">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <div class="form-group">
                    <label>من تاريخ</label>
                    <input type="date" name="from" value="{{ $from }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to" value="{{ $to }}" class="form-control">
                </div>
                <div class="form-group"><button class="btn btn-primary"><x-icon name="chart" /> عرض التقرير</button></div>
            </form>
        </div>
    </div>

    <div class="print-only mb-2">
        <strong>{{ setting('country') }}</strong><br>
        {{ setting('ministry') }}<br>
        {{ setting('institution_name') }}<br>
        تقرير المطعم من {{ $from }} إلى {{ $to }}
    </div>

    <div class="stat-grid">
        <div class="stat-card is-success">
            <div class="stat-icon"><x-icon name="utensils" /></div>
            <div><div class="stat-value">{{ number_format($traineeMeals) }}</div><div class="stat-label">وجبات المتربصين</div></div>
        </div>
        <div class="stat-card is-info">
            <div class="stat-icon"><x-icon name="user" /></div>
            <div><div class="stat-value">{{ number_format($employeeMeals) }}</div><div class="stat-label">وجبات الموظفين</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon"><x-icon name="user-check" /></div>
            <div><div class="stat-value">{{ number_format($activePayers) }}</div><div class="stat-label">متربصون بسداد ساري</div></div>
        </div>
        <div class="stat-card is-info">
            <div class="stat-icon"><x-icon name="id-card" /></div>
            <div><div class="stat-value">{{ number_format($activeEmployeePayers) }}</div><div class="stat-label">موظفون بسداد ساري</div></div>
        </div>
        <div class="stat-card is-warning">
            <div class="stat-icon"><x-icon name="credit-card" /></div>
            <div><div class="stat-value">{{ money($approvedTotal) }}</div><div class="stat-label">إجمالي السدادات المعتمدة</div></div>
        </div>
        <div class="stat-card is-warning">
            <div class="stat-icon"><x-icon name="clipboard" /></div>
            <div><div class="stat-value">{{ number_format($expectedTotal) }}</div><div class="stat-label">الوجبات المتوقّعة (الفترة)</div></div>
        </div>
        <div class="stat-card is-success">
            <div class="stat-icon"><x-icon name="check-circle" /></div>
            <div><div class="stat-value">{{ number_format($serveSuccess) }}</div><div class="stat-label">عمليات تسليم ناجحة</div></div>
        </div>
        <div class="stat-card is-danger">
            <div class="stat-icon"><x-icon name="x-circle" /></div>
            <div><div class="stat-value">{{ number_format($serveRejected) }}</div><div class="stat-label">عمليات تسليم مرفوضة</div></div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3>السدادات المعتمدة حسب النوع</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>الفئة</th><th>النوع</th><th>العدد</th><th>المبلغ</th></tr></thead>
                    <tbody>
                        @forelse($byType as $row)
                            <tr>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $row['plan_label'] }}</td>
                                <td class="cell-nowrap">{{ number_format($row['total']) }}</td>
                                <td class="cell-nowrap">{{ money($row['amount']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">لا توجد سدادات معتمدة في الفترة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>السدادات حسب الحالة</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>الفئة</th><th>الحالة</th><th>العدد</th><th>المبلغ</th></tr></thead>
                    <tbody>
                        @foreach($byStatus as $row)
                            <tr>
                                <td>{{ $row['category'] }}</td>
                                <td><span class="badge badge-{{ $row['tone'] }}">{{ $row['status'] }}</span></td>
                                <td class="cell-nowrap">{{ number_format($row['total']) }}</td>
                                <td class="cell-nowrap">{{ money($row['amount']) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="card-header"><h3>توزيع العمليات حسب التاريخ</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>التاريخ</th><th>المتوقّع (متربصون)</th><th>وجبات المتربصين</th><th>وجبات الموظفين</th><th>الإجمالي</th><th>الفرق</th></tr></thead>
                <tbody>
                    @forelse($byDate as $row)
                        @php $diff = $row->trainee_meals - $row->expected; @endphp
                        <tr>
                            <td class="cell-nowrap">{{ $row->day }}</td>
                            <td class="cell-nowrap">{{ number_format($row->expected) }}</td>
                            <td class="cell-nowrap">{{ number_format($row->trainee_meals) }}</td>
                            <td class="cell-nowrap">{{ number_format($row->employee_meals) }}</td>
                            <td class="cell-nowrap">{{ number_format($row->trainee_meals + $row->employee_meals) }}</td>
                            <td class="cell-nowrap">
                                <span class="badge badge-{{ $diff === 0 ? 'success' : ($diff > 0 ? 'warning' : 'danger') }}">
                                    {{ $diff > 0 ? '+' : '' }}{{ number_format($diff) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-state">لا توجد عمليات في الفترة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
