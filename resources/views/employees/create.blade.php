@extends('layouts.app')

@section('title', 'موظف جديد')
@section('page_title', 'إضافة موظف')

@section('content')
    <div class="page-head">
        <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>إضافة موظف جديد</h2>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
        @csrf
        @include('employees._form', ['employee' => $employee])
        <div class="mt-2">
            <button type="submit" class="btn btn-primary"><x-icon name="save" /> حفظ</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
