@extends('layouts.app')

@section('title', 'مستخدم جديد')
@section('page_title', 'مستخدم جديد')

@section('content')
    <div class="page-head">
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>إنشاء مستخدم</h2>
    </div>

    <div class="card" style="max-width:760px;">
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form', ['user' => null])
                <button type="submit" class="btn btn-primary mt-2"><x-icon name="save" /> حفظ</button>
            </form>
        </div>
    </div>
@endsection
