@extends('layouts.app')

@section('title', 'تعديل موظف')
@section('page_title', 'تعديل موظف')

@section('content')
    <div class="page-head">
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>تعديل: {{ $employee->full_name }}</h2>
    </div>

    <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('employees._form', ['employee' => $employee])
        <div class="mt-2">
            <button type="submit" class="btn btn-primary"><x-icon name="save" /> حفظ التغييرات</button>
            <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
