@php $user = $user ?? null; @endphp

<div class="form-grid">
    <div class="form-group">
        <label for="name">الاسم الكامل</label>
        <input id="name" name="name" class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $user?->name) }}" required>
        @error('name')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="username">اسم المستخدم</label>
        <input id="username" name="username" class="form-control @error('username') is-invalid @enderror"
               value="{{ old('username', $user?->username) }}" required>
        @error('username')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="email">البريد الإلكتروني (اختياري)</label>
        <input id="email" type="email" name="email" class="form-control"
               value="{{ old('email', $user?->email) }}">
        @error('email')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="role">الدور</label>
        <select id="role" name="role" class="form-control" required>
            @foreach($roles as $value => $label)
                <option value="{{ $value }}" @selected(old('role', $user?->role) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('role')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="password">كلمة المرور {{ $user ? '(اتركها فارغة لعدم التغيير)' : '' }}</label>
        <input id="password" type="password" name="password" class="form-control" @if(!$user) required @endif>
        @error('password')<span class="form-error">{{ $message }}</span>@enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">تأكيد كلمة المرور</label>
        <input id="password_confirmation" type="password" name="password_confirmation" class="form-control">
    </div>

    <div class="form-group">
        <label class="form-check">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user?->is_active ?? true))>
            <span>الحساب نشط</span>
        </label>
    </div>
</div>
