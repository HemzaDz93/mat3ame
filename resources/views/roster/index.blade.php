@extends('layouts.app')

@section('title', 'القوائم الاسمية')
@section('page_title', 'القوائم الاسمية')

@section('page_sub', 'توليد قوائم المتربصين حسب الاختصاص والمستوى والشهادة ونمط التكوين')

@section('topbar_actions')
    <a href="{{ route('roster.export', ['format' => 'csv']) }}?{{ http_build_query(request()->query()) }}" class="btn btn-secondary btn-sm">
        <x-icon name="download" /> CSV
    </a>
    <a href="{{ route('roster.export', ['format' => 'xlsx']) }}?{{ http_build_query(request()->query()) }}" class="btn btn-secondary btn-sm">
        <x-icon name="file-spreadsheet" /> Excel
    </a>
    <button type="button" class="btn btn-primary btn-sm" onclick="window.print()"><x-icon name="print" /> طباعة</button>
@endsection

@push('styles')
    <style>
        .roster-head { display: none; }
        @media print {
            .roster-head {
                display: block;
                text-align: center;
                margin-bottom: 8mm;
                border-bottom: 1px solid #94a3b8;
                padding-bottom: 3mm;
            }
            .roster-head .rep { font-size: 10pt; font-weight: 700; }
            .roster-head .inst { font-size: 13pt; font-weight: 800; color: #0b2f3f; }
            .roster-head .meta { font-size: 9pt; margin-top: 2mm; }
            .roster-title { text-align: center; font-weight: 700; font-size: 11pt; margin: 3mm 0; }
        }
    </style>
@endpush

@section('content')
    <div class="card mb-2 no-print">
        <div class="card-body">
            <form method="GET" class="filter-bar">
                <div class="form-group grow">
                    <label>الاختصاص</label>
                    <input name="specialty" value="{{ request('specialty') }}" class="form-control" list="specialty-list" placeholder="اسم الاختصاص أو جزء منه">
                    <datalist id="specialty-list">
                        @foreach($specialties as $s)<option value="{{ $s }}"></option>@endforeach
                    </datalist>
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
                    <label>نمط التكوين</label>
                    <select name="training_mode" class="form-control">
                        <option value="">الكل</option>
                        @foreach($modes as $m)<option value="{{ $m }}" @selected(request('training_mode') === $m)>{{ $m }}</option>@endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>الترتيب</label>
                    <select name="sort" class="form-control">
                        <option value="name" @selected(request('sort', 'name') === 'name')>حسب الاسم</option>
                        <option value="reg_no" @selected(request('sort') === 'reg_no')>حسب رقم التسجيل</option>
                    </select>
                </div>
                <div class="form-group">
                    <button class="btn btn-secondary"><x-icon name="filter" /> توليد القائمة</button>
                </div>
                <div class="form-group">
                    <a href="{{ route('roster') }}" class="btn btn-ghost"><x-icon name="refresh" /> إعادة تعيين</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="roster-head">
            <div class="rep">{{ setting('country', 'الجمهورية الجزائرية الديمقراطية الشعبية') }}</div>
            <div>{{ setting('ministry', 'وزارة التكوين والتعليم المهنيين') }}</div>
            @if(setting('directorate'))
                <div>{{ setting('directorate') }}</div>
            @endif
            <div class="inst">{{ setting('institution_name', 'منصة الزاوية') }}</div>
            <div class="meta">
                تاريخ إنشاء التقرير: {{ $generatedAt->format('Y-m-d H:i') }}
                @if(request('specialty')) — الاختصاص: {{ request('specialty') }}@endif
                — عدد السجلات: {{ $total }}
            </div>
        </div>

        <div class="roster-title print-only">
            القائمة الاسمية{{ request('specialty') ? ': '.request('specialty') : '' }}
        </div>

        <div class="card-header no-print">
            <h3>القائمة ({{ number_format($total) }})</h3>
            <div class="grow"></div>
            <span class="subtle">تاريخ الإنشاء: {{ $generatedAt->format('Y-m-d H:i') }}</span>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:48px;">م</th>
                        <th>رقم التسجيل</th>
                        <th>الرقم الإلكتروني</th>
                        <th>الاسم واللقب</th>
                        <th>الاختصاص</th>
                        <th>المستوى</th>
                        <th>الشهادة</th>
                        <th>نمط التكوين</th>
                        <th>الجنس</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="cell-nowrap">{{ $row->reg_no ?: '—' }}</td>
                            <td class="cell-nowrap">{{ $row->electronic_reg_no ?: '—' }}</td>
                            <td>{{ $row->full_name }}</td>
                            <td>{{ $row->specialty ?: '—' }}</td>
                            <td>{{ $row->level ?: '—' }}</td>
                            <td>{{ $row->diploma ?: '—' }}</td>
                            <td>{{ $row->training_mode ?: '—' }}</td>
                            <td>{{ $row->gender_label }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="empty-state">لا توجد سجلات مطابقة للمعايير المحددة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
