@extends('layouts.app')

@section('title', 'ملف المتربص')
@section('page_title', 'ملف المتربص')

@section('topbar_actions')
    <a href="{{ route('trainees.edit', $trainee) }}" class="btn btn-secondary btn-sm"><x-icon name="edit" /> تعديل</a>
    <a href="{{ route('cards.index', ['ids' => $trainee->id]) }}" class="btn btn-secondary btn-sm"><x-icon name="id-card" /> بطاقة</a>
    <form method="POST" action="{{ route('trainees.destroy', $trainee) }}" data-confirm="حذف هذا المتربص (حذف ناعم)؟">
        @csrf
        <button class="btn btn-danger btn-sm"><x-icon name="trash" /> حذف</button>
    </form>
@endsection

@section('content')
    <div class="grid-2 mb-2">
        <div class="card">
            <div class="card-header"><h3>البيانات الشخصية</h3></div>
            <div class="card-body">
                <div class="person-card">
                    @if($trainee->photo)
                        <img class="photo" src="{{ route('trainees.photo', $trainee) }}" alt="">
                    @else
                        <div class="photo"></div>
                    @endif
                    <div>
                        <div class="p-name">{{ $trainee->full_name }}</div>
                        <div class="subtle">{{ $trainee->specialty ?: '—' }}</div>
                        <div class="check-row">
                            @if($trainee->isFileAccepted())
                                <span class="badge badge-success">ملف مقبول</span>
                            @else
                                <span class="badge badge-warning">ملف غير مقبول</span>
                            @endif
                            @if($trainee->account_cancelled)<span class="badge badge-danger">حساب ملغى</span>@endif
                            @if($trainee->eligible)<span class="badge badge-info">مؤهل</span>@endif
                        </div>
                    </div>
                </div>

                <ul class="info-list mt-2">
                    <li><span class="k">رقم التسجيل</span><span class="v">{{ $trainee->reg_no ?: '—' }}</span></li>
                    <li><span class="k">الرقم الإلكتروني</span><span class="v">{{ $trainee->electronic_reg_no ?: '—' }}</span></li>
                    <li><span class="k">الجنس</span><span class="v">{{ $trainee->gender_label }}</span></li>
                    <li><span class="k">تاريخ ومكان الميلاد</span><span class="v">{{ ar_date($trainee->birth_date) }} — {{ $trainee->birth_place ?: '—' }}</span></li>
                    <li><span class="k">ولاية الإقامة</span><span class="v">{{ $trainee->residence_wilaya ?: '—' }}</span></li>
                    <li><span class="k">العنوان</span><span class="v">{{ $trainee->address ?: '—' }}</span></li>
                    <li><span class="k">الهاتف</span><span class="v">{{ $trainee->phone ?: '—' }}</span></li>
                    <li><span class="k">البريد</span><span class="v">{{ $trainee->email ?: '—' }}</span></li>
                    <li><span class="k">NIN</span><span class="v">{{ $trainee->nin ?: '—' }}</span></li>
                    <li><span class="k">اسم الأب / الأم</span><span class="v">{{ $trainee->father_name ?: '—' }} / {{ $trainee->mother_name ?: '—' }}</span></li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>التكوين</h3></div>
            <div class="card-body">
                <ul class="info-list">
                    <li><span class="k">الاختصاص</span><span class="v">{{ $trainee->specialty ?: '—' }}</span></li>
                    <li><span class="k">الشهادة</span><span class="v">{{ $trainee->diploma ?: '—' }}</span></li>
                    <li><span class="k">المستوى</span><span class="v">{{ $trainee->level ?: '—' }}</span></li>
                    <li><span class="k">نمط التكوين</span><span class="v">{{ $trainee->training_mode ?: '—' }}</span></li>
                    <li><span class="k">فترة التكوين</span><span class="v">{{ ar_date($trainee->start_date) }} → {{ ar_date($trainee->end_date) }}</span></li>
                    <li><span class="k">الاحتياجات الخاصة</span><span class="v">{{ $trainee->special_needs ?: 'لا' }}</span></li>
                    <li><span class="k">تاريخ التسجيل</span><span class="v">{{ ar_date($trainee->registered_at) }}</span></li>
                    <li><span class="k">ملاحظات</span><span class="v">{{ $trainee->notes ?: '—' }}</span></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-header"><h3>الوثائق</h3></div>
            <div class="card-body">
                <form method="POST" action="{{ route('documents.store', $trainee) }}" enctype="multipart/form-data" class="filter-bar mb-2">
                    @csrf
                    <div class="form-group">
                        <select name="type" class="form-control" required>
                            @foreach($documentTypes as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="form-group grow">
                        <input type="file" name="file" class="form-control" accept="image/*,application/pdf" required>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary"><x-icon name="upload" /> رفع</button>
                    </div>
                </form>

                <div class="table-wrap">
                    <table class="table">
                        <thead><tr><th>النوع</th><th>الحجم</th><th>إجراءات</th></tr></thead>
                        <tbody>
                            @forelse($trainee->documents as $document)
                                <tr>
                                    <td>{{ $document->typeLabel() }}</td>
                                    <td class="cell-nowrap cell-muted">{{ number_format($document->file_size / 1024, 1) }} KB</td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="{{ route('documents.download', [$trainee, $document]) }}" class="btn btn-secondary btn-sm"><x-icon name="download" /></a>
                                            <form method="POST" action="{{ route('documents.destroy', $document) }}" data-confirm="حذف هذه الوثيقة؟">
                                                @csrf
                                                <button class="btn btn-danger btn-sm"><x-icon name="trash" /></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="empty-state">لا توجد وثائق.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>السدادات</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead><tr><th>النوع</th><th>الفترة</th><th>المبلغ</th><th>الحالة</th></tr></thead>
                    <tbody>
                        @forelse($trainee->payments as $payment)
                            <tr>
                                <td>{{ $payment->planLabel() }}</td>
                                <td class="cell-nowrap cell-muted">{{ ar_date($payment->start_date) }} → {{ ar_date($payment->end_date) }}</td>
                                <td class="cell-nowrap">{{ money($payment->amount) }}</td>
                                <td><span class="badge badge-{{ $payment->status === 'approved' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">{{ $payment->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">لا توجد سدادات.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
