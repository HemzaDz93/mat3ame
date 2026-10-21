<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(private AuditService $audit)
    {
    }

    public function index(): View
    {
        $users = User::query()
            ->when(request('q'), function ($q, $term) {
                $term = trim($term);
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%{$term}%")
                        ->orWhere('username', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%");
                });
            })
            ->when(request('role'), fn ($q, $role) => $q->where('role', $role))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'roles' => User::roles(),
        ]);
    }

    public function create(): View
    {
        return view('users.create', ['roles' => User::roles()]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'role' => $request->input('role'),
            'is_active' => $request->boolean('is_active', true),
            'password' => $request->input('password'),
        ]);

        $this->audit->log('user.created', $user, 'إنشاء مستخدم: '.$user->username, null, $user->only(['name', 'username', 'role', 'is_active']));

        return redirect()->route('users.index')->with('success', 'تم إنشاء المستخدم بنجاح.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', ['user' => $user, 'roles' => User::roles()]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $old = $user->only(['name', 'username', 'email', 'role', 'is_active']);

        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->role = $request->input('role');

        // لا يمكن للمدير تعطيل حسابه الخاص
        $isActive = $request->boolean('is_active', true);
        if ($user->id === auth()->id() && ! $isActive) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك الخاص.');
        }
        $user->is_active = $isActive;

        if ($request->filled('password')) {
            $user->password = $request->input('password');
        }

        $user->save();

        if (! $user->is_active) {
            $this->invalidateSessions($user);
        }

        $this->audit->log('user.updated', $user, 'تعديل مستخدم: '.$user->username, $old, $user->only(['name', 'username', 'email', 'role', 'is_active']));

        return redirect()->route('users.index')->with('success', 'تم تحديث المستخدم بنجاح.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك حذف حسابك الخاص.');
        }

        if ($user->isAdmin() && $this->adminCount() <= 1) {
            return back()->with('error', 'لا يمكن حذف آخر حساب مدير في النظام.');
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $this->audit->log('user.deleted', $user, 'حذف مستخدم: '.$user->username, $user->only(['name', 'username', 'role']));

        $user->delete();

        return redirect()->route('users.index')->with('success', 'تم حذف المستخدم.');
    }

    public function toggle(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'لا يمكنك تعطيل حسابك الخاص.');
        }

        if ($user->is_active && $user->isAdmin() && $this->adminCount() <= 1) {
            return back()->with('error', 'لا يمكن تعطيل آخر حساب مدير في النظام.');
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        if (! $user->is_active) {
            $this->invalidateSessions($user);
        }

        $this->audit->log('user.toggled', $user, ($user->is_active ? 'تفعيل' : 'تعطيل').' حساب: '.$user->username);

        return back()->with('success', ($user->is_active ? 'تم تفعيل' : 'تم تعطيل').' الحساب.');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = bin2hex(random_bytes(5));

        $user->password = $newPassword;
        $user->save();

        $this->audit->log('user.password_reset', $user, 'إعادة تعيين كلمة مرور: '.$user->username);

        return back()->with('success', 'تم تعيين كلمة مرور جديدة: '.$newPassword.' — انسخها وسلّمها للمستخدم.');
    }

    private function adminCount(): int
    {
        return User::query()->where('role', User::ROLE_ADMIN)->where('is_active', true)->count();
    }

    private function invalidateSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }
}
