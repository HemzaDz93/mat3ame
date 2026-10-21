@extends('layouts.print')

@section('title', 'طباعة بطاقات المتربصين')

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
    <span class="form-hint">عدد البطاقات: {{ $trainees->count() }} — {{ $pages->count() }} صفحة A4</span>
    <span class="form-hint">للطباعة المزدوجة: اختر «الوجهين» مع قلب الورقة على الحافة الطويلة.</span>
@endsection

@section('content')
    {{-- الوجه الأمامي --}}
    <div class="print-front-side">
        @foreach($pages as $page)
            <div class="card-sheet">
                <div class="cards-grid">
                    @foreach($page as $trainee)
                        @include('partials.card-front', [
                            'prefix' => $cardPrefix,
                            'code' => $trainee->identityCode(),
                            'photoUrl' => $trainee->photo ? route('trainees.photo', $trainee) : null,
                            'rows' => [
                                ['setting' => 'card_show_reg_no', 'k' => 'رقم التسجيل', 'v' => $trainee->reg_no ?: '—'],
                                ['setting' => 'card_show_name', 'k' => 'الاسم واللقب', 'v' => $trainee->full_name],
                                ['setting' => 'card_show_birth', 'k' => 'الميلاد', 'v' => ar_date($trainee->birth_date).' — '.($trainee->birth_place ?: '—')],
                                ['setting' => 'card_show_specialty', 'k' => 'الاختصاص', 'v' => $trainee->specialty ?: '—'],
                                ['setting' => 'card_show_period', 'k' => 'مدة التكوين', 'v' => ar_date($trainee->start_date).' ← '.ar_date($trainee->end_date)],
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
                    @foreach($page as $trainee)
                        @include('partials.card-back', [
                            'prefix' => $cardPrefix,
                            'code' => $trainee->identityCode(),
                            'rows' => [
                                ['k' => 'الاسم واللقب', 'v' => $trainee->full_name],
                                ['k' => 'الاختصاص', 'v' => $trainee->specialty ?: '—'],
                                ['k' => setting('card_foot_label', 'رقم التعريف'), 'v' => $trainee->identityCode()],
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
