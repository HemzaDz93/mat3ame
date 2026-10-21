@extends('layouts.app')

@section('title', 'المتربصون')
@section('page_title', 'إدارة المتربصين')

@section('topbar_actions')
    <a href="{{ route('export.trainees', 'csv') }}?{{ http_build_query(request()->query()) }}" class="btn btn-secondary btn-sm">
        <x-icon name="download" /> CSV
    </a>
    <a href="{{ route('export.trainees', 'xlsx') }}?{{ http_build_query(request()->query()) }}" class="btn btn-secondary btn-sm">
        <x-icon name="file-spreadsheet" /> Excel
    </a>
    <a href="{{ route('trainees.create') }}" class="btn btn-primary btn-sm"><x-icon name="plus" /> متربص جديد</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <input type="hidden" name="trashed" value="{{ $trashed ? 1 : 0 }}">
                <div class="form-group grow">
                    <label>بحث</label>
                    <input name="q" value="{{ request('q') }}" class="form-control" placeholder="الاسم، اللقب، رقم التسجيل، الرقم الإلكتروني، NIN، الهاتف">
                </div>
                <div class="form-group">
                    <label>الاختصاص</label>
                    <select name="specialty" class="form-control">
                        <option value="">الكل</option>
                        @foreach($specialties as $s)<option value="{{ $s }}" @selected(request('specialty') === $s)>{{ $s }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الشهادة</label>
                    <select name="diploma" class="form-control">
                        <option value="">الكل</option>
                        @foreach($diplomas as $d)<option value="{{ $d }}" @selected(request('diploma') === $d)>{{ $d }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>المستوى</label>
                    <select name="level" class="form-control">
                        <option value="">الكل</option>
                        @foreach($levels as $l)<option value="{{ $l }}" @selected(request('level') === $l)>{{ $l }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الجنس</label>
                    <select name="gender" class="form-control">
                        <option value="">الكل</option>
                        <option value="M" @selected(request('gender') === 'M')>ذكر</option>
                        <option value="F" @selected(request('gender') === 'F')>أنثى</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">الكل</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>ملف مقبول</option>
                        <option value="not_accepted" @selected(request('status') === 'not_accepted')>ملف مرفوض</option>
                        <option value="eligible" @selected(request('status') === 'eligible')>مؤهل</option>
                        <option value="cancelled" @selected(request('status') === 'cancelled')>حساب ملغى</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>بداية التكوين من</label>
                    <input type="date" name="start_from" value="{{ request('start_from') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>بداية التكوين إلى</label>
                    <input type="date" name="start_to" value="{{ request('start_to') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>نهاية التكوين من</label>
                    <input type="date" name="end_from" value="{{ request('end_from') }}" class="form-control">
                </div>
                <div class="form-group">
                    <label>نهاية التكوين إلى</label>
                    <input type="date" name="end_to" value="{{ request('end_to') }}" class="form-control">
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="filter" /> تصفية</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('trainees.index', $trashed ? [] : ['trashed' => 1]) }}" class="btn btn-ghost">
                        <x-icon name="restore" /> {{ $trashed ? 'عرض النشطين' : 'عرض المحذوفين' }}
                    </a>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" id="bulk-form" action="{{ $trashed ? route('trainees.bulk-restore') : route('trainees.bulk-delete') }}"
          data-confirm="{{ $trashed ? 'استعادة السجلات المحددة؟' : 'حذف السجلات المحددة (حذف ناعم)؟' }}">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3>السجلات ({{ number_format($trainees->total()) }})</h3>
                <div class="grow"></div>
                <button type="submit" class="btn {{ $trashed ? 'btn-success' : 'btn-danger' }} btn-sm">
                    <x-icon name="{{ $trashed ? 'restore' : 'trash' }}" /> {{ $trashed ? 'استعادة المحدد' : 'حذف المحدد' }}
                </button>
            </div>

            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width:36px;"><input type="checkbox" data-select-all></th>
                            <th>الصورة</th>
                            <th>الاسم واللقب</th>
                            <th>رقم التسجيل</th>
                            <th>الرقم الإلكتروني</th>
                            <th>الاختصاص</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($trainees as $trainee)
                            <tr>
                                <td><input type="checkbox" name="ids[]" value="{{ $trainee->id }}" data-row-check></td>
                                <td>
                                    @if($trainee->photo)
                                        <img src="{{ route('trainees.photo', $trainee) }}" class="avatar-sm" alt="">
                                    @else
                                        <div class="avatar-placeholder">{{ mb_substr($trainee->last_name, 0, 1) }}</div>
                                    @endif
                                </td>
                                <td>{{ $trainee->full_name }}</td>
                                <td class="cell-nowrap">{{ $trainee->reg_no ?: '—' }}</td>
                                <td class="cell-nowrap">{{ $trainee->electronic_reg_no ?: '—' }}</td>
                                <td>{{ $trainee->specialty ?: '—' }}</td>
                                <td>
                                    @if($trainee->account_cancelled)
                                        <span class="badge badge-danger">ملغى</span>
                                    @elseif($trainee->isFileAccepted())
                                        <span class="badge badge-success">مقبول</span>
                                    @else
                                        <span class="badge badge-warning">غير مقبول</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="{{ route('trainees.show', $trainee) }}" class="btn btn-secondary btn-sm"><x-icon name="eye" /> عرض</a>
                                        @if($trashed)
                                            <button type="submit" form="restore-{{ $trainee->id }}" class="btn btn-success btn-sm"><x-icon name="restore" /> استعادة</button>
                                        @else
                                            <a href="{{ route('trainees.edit', $trainee) }}" class="btn btn-secondary btn-sm"><x-icon name="edit" /></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="empty-state">لا توجد سجلات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $trainees->links('partials.pagination') }}
        </div>
    </form>

    @if($trashed)
        @foreach($trainees as $trainee)
            <form method="POST" id="restore-{{ $trainee->id }}" action="{{ route('trainees.restore', $trainee->id) }}" data-confirm="استعادة هذا السجل؟">
                @csrf
            </form>
        @endforeach
    @endif
@endsection
