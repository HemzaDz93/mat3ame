@extends('layouts.app')

@section('title', 'البطاقات التعريفية')
@section('page_title', 'بطاقات المتربصين')

@section('page_sub', 'اختر المتربصين ثم اطبع بطاقاتهم (10 بطاقات في صفحة A4)')

@section('topbar_actions')
    <a href="{{ route('employees.cards') }}" class="btn btn-secondary btn-sm"><x-icon name="user" /> بطاقات الموظفين</a>
@endsection

@section('content')
    <div class="card mb-2">
        <div class="card-body">
            <form method="GET" class="filter-bar">
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
                    <label>الحالة</label>
                    <select name="status" class="form-control">
                        <option value="">الكل</option>
                        <option value="accepted" @selected(request('status') === 'accepted')>ملف مقبول</option>
                        <option value="eligible" @selected(request('status') === 'eligible')>مؤهل</option>
                        <option value="not_accepted" @selected(request('status') === 'not_accepted')>غير مقبول</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="filter" /> تصفية</button>
                </div>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('cards.print') }}" target="_blank">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3>المتربصون ({{ number_format($trainees->total()) }})</h3>
                <div class="grow"></div>
                <button type="submit" class="btn btn-primary btn-sm"><x-icon name="print" /> طباعة البطاقات المحددة</button>
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
                            </tr>
                        @empty
                            <tr><td colspan="7" class="empty-state">لا توجد سجلات مطابقة.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $trainees->links('partials.pagination') }}
        </div>
    </form>
@endsection
