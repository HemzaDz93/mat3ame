@extends('layouts.app')

@section('title', 'تعديل مستخدم')
@section('page_title', 'تعديل مستخدم')

@section('content')
    <div class="page-head">
        <a href="{{ route('users.index') }}" class="btn btn-secondary btn-sm"><x-icon name="arrow-right" /> رجوع</a>
        <h2>تعديل المستخدم: {{ $user->username }}</h2>
    </div>

    <div class="card" style="max-width:760px;">
        <div class="card-body">
            <form method="POST" action="{{ route('users.update', $user) }}">
                @csrf
                @method('PUT')
                @include('users._form', ['user' => $user])
                <button type="submit" class="btn btn-primary mt-2"><x-icon name="save" /> حفظ التغييرات</button>
            </form>
        </div>
    </div>
@endsection
