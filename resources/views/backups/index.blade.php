@extends('layouts.app')

@section('title', 'النسخ الاحتياطي')
@section('page_title', 'النسخ الاحتياطي')

@section('content')
    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('backups.store') }}">
                    @csrf
                    <div class="form-group">
                        <label for="note">ملاحظة (اختياري)</label>
                        <input id="note" name="note" class="form-control" maxlength="200" placeholder="مثال: نسخة قبل تحديث البيانات">
                    </div>
                    <button type="submit" class="btn btn-primary" data-confirm="إنشاء نسخة احتياطية جديدة لقاعدة البيانات؟">
                        <x-icon name="database" /> إنشاء نسخة احتياطية
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>ملاحظات</h3>
                <ul class="info-list">
                    <li><span class="v">تُخزَّن النسخ داخل مجلد خاص غير متاح للوصول المباشر عبر المتصفح.</span></li>
                    <li><span class="v">يقتصر الوصول إليها على المدير فقط عبر زر التنزيل.</span></li>
                    <li><span class="v">يتم الاحتفاظ بعدد محدد من النسخ الأحدث وحذف الأقدم تلقائيًا.</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card mt-2">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>الملف</th>
                        <th>الحجم</th>
                        <th>الحالة</th>
                        <th>ملاحظة</th>
                        <th>المنفّذ</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($backups as $backup)
                        <tr>
                            <td class="cell-nowrap">{{ $backup->filename }}</td>
                            <td class="cell-nowrap">{{ $backup->size > 0 ? number_format($backup->size / 1048576, 2).' م.ب' : '—' }}</td>
                            <td>
                                @if($backup->status === 'success')
                                    <span class="badge badge-success">ناجحة</span>
                                @else
                                    <span class="badge badge-danger">فاشلة</span>
                                @endif
                            </td>
                            <td class="cell-muted">{{ $backup->note ?: '—' }}</td>
                            <td>{{ $backup->creator?->name ?? '—' }}</td>
                            <td class="cell-nowrap cell-muted">{{ ar_date($backup->created_at, 'Y-m-d H:i') }}</td>
                            <td>
                                <div class="table-actions">
                                    @if($backup->status === 'success')
                                        <a href="{{ route('backups.download', $backup) }}" class="btn btn-secondary btn-sm"><x-icon name="download" /> تنزيل</a>
                                    @endif
                                    <form method="POST" action="{{ route('backups.destroy', $backup) }}" data-confirm="حذف هذه النسخة الاحتياطية نهائيًا؟">
                                        @csrf
                                        <button class="btn btn-danger btn-sm"><x-icon name="trash" /> حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty-state">لا توجد نسخ احتياطية بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $backups->links('partials.pagination') }}
    </div>
@endsection
