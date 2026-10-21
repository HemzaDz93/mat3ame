@extends('layouts.app')

@section('title', 'نتيجة البحث')
@section('page_title', 'منصة المطعم')

@section('content')
    <div class="pos-shell">
        <div class="page-head">
            <a href="{{ route('canteen.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع للمطعم</a>
            <h2>نتيجة البحث عن: {{ $code }}</h2>
        </div>

        <div class="card">
            <div class="card-body">
                @include('canteen._result', ['person' => $person, 'code' => $code])
            </div>
        </div>
    </div>
@endsection
