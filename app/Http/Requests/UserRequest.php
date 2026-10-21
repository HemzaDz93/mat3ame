<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() === true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $id = $user instanceof User ? $user->id : null;
        $isCreate = $this->isMethod('POST') && ! $id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => [
                'required', 'string', 'max:100', 'alpha_dash',
                Rule::unique('users', 'username')->ignore($id),
            ],
            'email' => ['nullable', 'email', 'max:190'],
            'role' => ['required', Rule::in(array_keys(User::roles()))],
            'is_active' => ['nullable', 'boolean'],
            'password' => $isCreate
                ? ['required', 'confirmed', 'max:255', Password::min(8)]
                : ['nullable', 'confirmed', 'max:255', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'الاسم',
            'username' => 'اسم المستخدم',
            'email' => 'البريد الإلكتروني',
            'role' => 'الدور',
            'is_active' => 'الحساب نشط',
            'password' => 'كلمة المرور',
        ];
    }
}
