@extends('layouts.app')

@section('title', 'حسابي')
@section('page_title', 'حسابي')

@section('content')
    <div class="page-head">
        <h2>بيانات الحساب وكلمة المرور</h2>
    </div>

    <div class="card" style="max-width:640px;">
        <div class="card-body">
            <form method="POST" action="{{ route('account.update') }}">
                @csrf

                <div class="info-list mb-2">
                    <li><span class="k">اسم المستخدم</span><span class="v">{{ $user->username }}</span></li>
                    <li><span class="k">الدور</span><span class="v">{{ $user->roleLabel() }}</span></li>
                </div>

                <div class="form-group">
                    <label for="name">الاسم الكامل</label>
                    <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required>
                </div>

                <div class="form-group">
                    <label for="email">البريد الإلكتروني (اختياري)</label>
                    <input id="email" type="email" name="email" class="form-control"
                           value="{{ old('email', $user->email) }}">
                </div>

                <div class="divider-line"></div>

                <div class="form-group">
                    <label for="current_password">كلمة المرور الحالية</label>
                    <input id="current_password" type="password" name="current_password" class="form-control" required>
                    <span class="form-hint">مطلوبة لتأكيد أي تغيير.</span>
                </div>

                <div class="form-group">
                    <label for="password">كلمة المرور الجديدة (اختياري)</label>
                    <input id="password" type="password" name="password" class="form-control">
                    <span class="form-hint">8 أحرف على الأقل. اتركها فارغة إن لم ترغب في تغييرها.</span>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور الجديدة</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
                </div>

                <button type="submit" class="btn btn-primary">
                    <x-icon name="save" /> حفظ التغييرات
                </button>
            </form>
        </div>
    </div>
@endsection
