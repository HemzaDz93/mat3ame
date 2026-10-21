@extends('layouts.print')

@section('title', 'طباعة بطاقات الموظفين')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/cards.css') }}">
@endpush

@section('toolbar')
    <div class="btn-group-actions" style="margin-inline-start:12px;">
        <button type="button" class="btn btn-primary" onclick="printCards('both')">
            <x-icon name="print" size="16" /> طباعة الوجهين
        </button>
        <button type="button" class="btn btn-secondary" onclick="printCards('front')">
            الوجه الأمامي فقط
        </button>
        <button type="button" class="btn btn-secondary" onclick="printCards('back')">
            الوجه الخلفي فقط
        </button>
    </div>
    <span class="form-hint">عدد البطاقات: {{ $employees->count() }} — {{ $pages->count() }} صفحة A4</span>
    <span class="form-hint">للطباعة المزدوجة: اختر «الوجهين» مع قلب الورقة على الحافة الطويلة.</span>
@endsection

@section('content')
    {{-- الوجه الأمامي --}}
    <div class="print-front-side">
        @foreach($pages as $page)
            <div class="card-sheet">
                <div class="cards-grid">
                    @foreach($page as $employee)
                        @include('partials.card-front', [
                            'prefix' => $cardPrefix,
                            'code' => $employee->emp_no,
                            'photoUrl' => $employee->photo ? route('employees.photo', $employee) : null,
                            'rows' => [
                                ['setting' => 'employee_card_show_reg_no', 'k' => 'رقم الموظف', 'v' => $employee->emp_no ?: '—'],
                                ['setting' => 'employee_card_show_name', 'k' => 'الاسم واللقب', 'v' => $employee->full_name],
                                ['setting' => 'employee_card_show_birth', 'k' => 'الميلاد', 'v' => ar_date($employee->birth_date).' — '.($employee->birth_place ?: '—')],
                                ['setting' => 'employee_card_show_specialty', 'k' => 'الرتبة', 'v' => $employee->rank ?: '—'],
                                ['setting' => 'employee_card_show_period', 'k' => 'تاريخ التوظيف', 'v' => ar_date($employee->hire_date)],
                            ],
                        ])
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>

    {{-- الوجه الخلفي --}}
    <div class="print-back-side">
        @foreach($pages as $page)
            <div class="card-sheet">
                <div class="cards-grid">
                    @foreach($page as $employee)
                        @include('partials.card-back', [
                            'prefix' => $cardPrefix,
                            'code' => $employee->emp_no,
                            'rows' => [
                                ['k' => 'الاسم واللقب', 'v' => $employee->full_name],
                                ['k' => 'الرتبة', 'v' => $employee->rank ?: '—'],
                                ['k' => setting('employee_card_foot_label', 'رقم التعريف'), 'v' => $employee->emp_no ?: '—'],
                            ],
                        ])
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/cards.js') }}"></script>
@endpush
