@extends('layouts.app')

@section('title', 'معاينة الاستيراد')
@section('page_title', 'معاينة ملف الاستيراد')

@section('topbar_actions')
    <a href="{{ route('import.form') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> إلغاء</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <div class="page-head">
                <h2>{{ $filename }}</h2>
                <div class="grow"></div>
                <form method="POST" action="{{ route('import.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="filename" value="{{ $filename }}">
                    <input type="hidden" name="skip_first_row" value="{{ $skipFirstRow ? 1 : 0 }}">
                    <button type="submit" class="btn btn-primary" data-confirm="تأكيد تنفيذ الاستيراد وكتابة البيانات في قاعدة البيانات؟">
                        <x-icon name="check" /> تأكيد الاستيراد
                    </button>
                </form>
            </div>

            <p class="text-muted">لم تُكتب أي بيانات بعد. راجع النتائج أدناه ثم اضغط «تأكيد الاستيراد».</p>

            <div class="stat-grid">
                <div class="stat-card"><div class="stat-value">{{ $counts['total'] }}</div><div class="stat-label">إجمالي الصفوف</div></div>
                <div class="stat-card is-success"><div class="stat-value">{{ $counts['created'] }}</div><div class="stat-label">سيُضاف</div></div>
                <div class="stat-card is-info"><div class="stat-value">{{ $counts['updated'] }}</div><div class="stat-label">سيُحدَّث</div></div>
                <div class="stat-card is-info"><div class="stat-value">{{ $counts['restored'] }}</div><div class="stat-label">سيُستعاد</div></div>
                <div class="stat-card is-danger"><div class="stat-value">{{ $counts['rejected'] }}</div><div class="stat-label">مرفوض</div></div>
                <div class="stat-card is-warning"><div class="stat-value">{{ $counts['skipped'] }}</div><div class="stat-label">متجاهَل</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>تفاصيل الصفوف</h3>
            <div class="grow"></div>
            <span class="text-muted">يُعرض أول {{ min(count($rows), 300) }} صف من {{ count($rows) }}</span>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>السطر</th>
                        <th>الإجراء</th>
                        <th>الاسم واللقب</th>
                        <th>رقم التسجيل</th>
                        <th>الملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(array_slice($rows, 0, 300) as $row)
                        <tr>
                            <td class="cell-nowrap">{{ $row['row'] }}</td>
                            <td>
                                @php
                                    $map = [
                                        'create' => ['success', 'إضافة'],
                                        'update' => ['info', 'تحديث'],
                                        'restore' => ['primary', 'استعادة'],
                                        'skip' => ['warning', 'تجاهل'],
                                        'reject' => ['danger', 'رفض'],
                                    ];
                                    [$variant, $label] = $map[$row['action']] ?? ['muted', $row['action']];
                                @endphp
                                <span class="badge badge-{{ $variant }}">{{ $label }}</span>
                            </td>
                            <td>{{ $row['name'] ?: '—' }}</td>
                            <td class="cell-nowrap">{{ $row['reg_no'] ?: '—' }}</td>
                            <td class="cell-muted">{{ implode('؛ ', $row['reasons']) ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-state">لا توجد صفوف صالحة في الملف.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
