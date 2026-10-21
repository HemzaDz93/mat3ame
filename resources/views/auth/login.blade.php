@extends('layouts.guest')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="login-page">
        <div class="login-card">
            <div class="login-head">
                @if(setting('institution_logo'))
                    <img class="logo" src="{{ route('settings.logo') }}" alt="الشعار">
                @endif
                <h1>{{ setting('institution_name', 'منصة الزاوية') }}</h1>
                <p>نظام إدارة مركز التكوين المهني والتمهين</p>
            </div>

            <div class="login-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <x-icon name="x-circle" />
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.attempt') }}">
                    @csrf

                    <div class="form-group">
                        <label for="username">اسم المستخدم</label>
                        <input id="username" name="username" class="form-control @error('username') is-invalid @enderror"
                               value="{{ old('username') }}" autocomplete="username" autofocus required>
                    </div>

                    <div class="form-group">
                        <label for="password">كلمة المرور</label>
                        <input id="password" type="password" name="password" class="form-control"
                               autocomplete="current-password" required>
                    </div>

                    <div class="form-check mb-2">
                        <input id="remember" type="checkbox" name="remember" value="1">
                        <label for="remember" class="mb-0">تذكرني</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        <x-icon name="lock" /> دخول
                    </button>
                </form>
            </div>

            <div class="login-foot">
                {{ setting('ministry', 'وزارة التكوين والتعليم المهنيين') }}
            </div>
        </div>
    </div>
@endsection
