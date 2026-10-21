<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountUpdateRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function edit(): View
    {
        return view('account.edit', ['user' => auth()->user()]);
    }

    public function update(AccountUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if (! Hash::check($request->input('current_password'), $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'كلمة المرور الحالية غير صحيحة.',
            ]);
        }

        $old = $user->only(['name', 'email']);

        $user->name = $request->input('name');
        $user->email = $request->input('email');

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        $this->audit->log(
            'account.updated',
            $user,
            'تحديث بيانات الحساب الشخصي',
            $old,
            $user->only(['name', 'email'])
        );

        return back()->with('success', 'تم تحديث بيانات الحساب بنجاح.');
    }
}
