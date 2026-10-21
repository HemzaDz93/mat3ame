@extends('layouts.app')

@section('title', 'متربص جديد')
@section('page_title', 'إضافة متربص')

@section('content')
    <div class="page-head">
        <a href="{{ route('trainees.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>إضافة متربص جديد</h2>
    </div>

    <form method="POST" action="{{ route('trainees.store') }}" enctype="multipart/form-data">
        @csrf
        @include('trainees._form', ['trainee' => $trainee])

        <div class="mt-2">
            <button type="submit" class="btn btn-primary"><x-icon name="save" /> حفظ المتربص</button>
            <a href="{{ route('trainees.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
