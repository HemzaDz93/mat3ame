@extends('layouts.app')

@section('title', 'تعديل متربص')
@section('page_title', 'تعديل متربص')

@section('content')
    <div class="page-head">
        <a href="{{ route('trainees.show', $trainee) }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>تعديل: {{ $trainee->full_name }}</h2>
    </div>

    <form method="POST" action="{{ route('trainees.update', $trainee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('trainees._form', ['trainee' => $trainee])

        <div class="mt-2">
            <button type="submit" class="btn btn-primary"><x-icon name="save" /> حفظ التغييرات</button>
            <a href="{{ route('trainees.show', $trainee) }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
