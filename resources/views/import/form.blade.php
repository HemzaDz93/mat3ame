@extends('layouts.app')

@section('title', 'استيراد Excel')
@section('page_title', 'استيراد بيانات المتربصين')

@section('content')
    <div class="page-head">
        <h2>استيراد ملف Excel (صيغة MINASA)</h2>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="file">ملف Excel <span class="text-danger">*</span></label>
                        <input id="file" type="file" name="file" accept=".xlsx,.xlsm,.csv" class="form-control" required>
                        <span class="form-hint">الصيغ المسموح بها: xlsx أو xlsm أو csv، بحجم أقصى 20 ميغابايت. يُفضَّل وجود ورقة عمل باسم MINASA.</span>
                        @error('file')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label class="form-check">
                            <input type="hidden" name="skip_first_row" value="0">
                            <input type="checkbox" name="skip_first_row" value="1" checked>
                            <span>تجاهل الصف الأول (صف العناوين)</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary"><x-icon name="eye" /> معاينة الملف</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>قواعد الاستيراد</h3>
                <ul class="info-list">
                    <li>المفتاح الأساسي للمطابقة هو <strong>رقم التسجيل</strong> (العمود 10).</li>
                    <li>يُستورد الصف فقط إذا كان <strong>الملف مقبولًا</strong> و<strong>المتربص مؤهلًا</strong>.</li>
                    <li>السجلات المحذوفة ناعمًا تُستعاد تلقائيًا عند إعادة استيرادها.</li>
                    <li>الصفوف غير المقبولة تُرفض وتظهر مع سبب الرفض في المعاينة.</li>
                    <li>لا تُكتب أي بيانات في قاعدة البيانات قبل تأكيد الاستيراد.</li>
                </ul>
            </div>
        </div>
    </div>

    @if($result)
        <div class="card mt-2">
            <div class="card-header">
                <h3>نتيجة الاستيراد — {{ $result['filename'] }}</h3>
            </div>
            <div class="card-body">
                <div class="stat-grid">
                    <div class="stat-card"><div class="stat-value">{{ $result['counts']['total'] }}</div><div class="stat-label">إجمالي الصفوف</div></div>
                    <div class="stat-card is-success"><div class="stat-value">{{ $result['counts']['created'] }}</div><div class="stat-label">مضاف</div></div>
                    <div class="stat-card is-info"><div class="stat-value">{{ $result['counts']['updated'] }}</div><div class="stat-label">محدَّث</div></div>
                    <div class="stat-card is-info"><div class="stat-value">{{ $result['counts']['restored'] }}</div><div class="stat-label">مستعاد</div></div>
                    <div class="stat-card is-danger"><div class="stat-value">{{ $result['counts']['rejected'] }}</div><div class="stat-label">مرفوض</div></div>
                    <div class="stat-card is-warning"><div class="stat-value">{{ $result['counts']['skipped'] }}</div><div class="stat-label">متجاهَل</div></div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>السطر</th><th>الإجراء</th><th>الاسم</th><th>رقم التسجيل</th><th>الملاحظات</th></tr>
                    </thead>
                    <tbody>
                        @forelse($result['rows'] as $row)
                            <tr>
                                <td class="cell-nowrap">{{ $row['row'] }}</td>
                                <td><span class="badge badge-{{ $row['action'] === 'create' ? 'success' : ($row['action'] === 'reject' ? 'danger' : ($row['action'] === 'skip' ? 'warning' : 'info')) }}">{{ $row['action'] }}</span></td>
                                <td>{{ $row['name'] ?: '—' }}</td>
                                <td class="cell-nowrap">{{ $row['reg_no'] ?: '—' }}</td>
                                <td class="cell-muted">{{ implode('؛ ', $row['reasons']) ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty-state">لا توجد تفاصيل.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if($history->isNotEmpty())
        <div class="card mt-2">
            <div class="card-header"><h3>آخر عمليات الاستيراد</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr><th>الملف</th><th>الصفوف</th><th>مضاف</th><th>محدَّث</th><th>مرفوض</th><th>المنفّذ</th><th>التاريخ</th></tr>
                    </thead>
                    <tbody>
                        @foreach($history as $log)
                            <tr>
                                <td>{{ $log->filename }}</td>
                                <td class="cell-nowrap">{{ $log->total_rows }}</td>
                                <td class="cell-nowrap">{{ $log->accepted }}</td>
                                <td class="cell-nowrap">{{ $log->updated }}</td>
                                <td class="cell-nowrap">{{ $log->rejected }}</td>
                                <td>{{ $log->creator?->name ?? '—' }}</td>
                                <td class="cell-nowrap cell-muted">{{ ar_date($log->created_at, 'Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
@endsection
