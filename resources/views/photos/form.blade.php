@extends('layouts.app')

@section('title', 'رفع الصور الجماعي')
@section('page_title', 'رفع الصور الجماعي')

@section('content')
    <div class="page-head">
        <h2>رفع صور المتربصين</h2>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('photos.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label for="photos">صور متعددة</label>
                        <input id="photos" type="file" name="photos[]" accept="image/png,image/jpeg,image/webp" class="form-control" multiple>
                        <span class="form-hint">يمكنك تحديد عدة صور مرة واحدة.</span>
                        @error('photos.*')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <div class="form-group">
                        <label for="archive">أو أرشيف ZIP</label>
                        <input id="archive" type="file" name="archive" accept=".zip,application/zip" class="form-control">
                        <span class="form-hint">أرشيف يحتوي على صور، بحجم أقصى 100 ميغابايت.</span>
                        @error('archive')<span class="form-error">{{ $message }}</span>@enderror
                    </div>

                    <button type="submit" class="btn btn-primary"><x-icon name="upload" /> رفع الصور</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h3>طريقة المطابقة</h3>
                <ul class="info-list">
                    <li><span class="k">اسم الملف</span><span class="v">يجب أن يكون اسم الصورة (دون الامتداد) مطابقًا لأحد المعرّفات التالية.</span></li>
                    <li><span class="k">الرقم الإلكتروني</span><span class="v">المعرّف الأساسي للمطابقة.</span></li>
                    <li><span class="k">رقم التسجيل</span><span class="v">يُستخدم عند غياب الرقم الإلكتروني.</span></li>
                    <li><span class="k">NIN</span><span class="v">رقم التعريف الوطني كخيار احتياطي.</span></li>
                </ul>
                <p class="text-muted mt-2">مثال: <strong>12345678.jpg</strong> سيُطابق المتربص الذي رقمه الإلكتروني 12345678.</p>
            </div>
        </div>
    </div>

    @if($result)
        <div class="card mt-2">
            <div class="card-header"><h3>نتيجة الرفع</h3></div>
            <div class="card-body">
                <div class="stat-grid">
                    <div class="stat-card is-success"><div class="stat-value">{{ $result['matched'] }}</div><div class="stat-label">صورة محدَّثة</div></div>
                    <div class="stat-card is-warning"><div class="stat-value">{{ count($result['unmatched']) }}</div><div class="stat-label">غير مطابقة</div></div>
                    <div class="stat-card is-danger"><div class="stat-value">{{ count($result['failed']) }}</div><div class="stat-label">فشل</div></div>
                </div>

                @if(!empty($result['unmatched']))
                    <h4 class="mt-2">أسماء غير مطابقة</h4>
                    <p class="text-muted">{{ implode('، ', array_slice($result['unmatched'], 0, 100)) }}</p>
                @endif

                @if(!empty($result['failed']))
                    <h4 class="mt-2">ملفات فشلت</h4>
                    <p class="text-muted">{{ implode('، ', array_slice($result['failed'], 0, 100)) }}</p>
                @endif
            </div>
        </div>
    @endif
@endsection
