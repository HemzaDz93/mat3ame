<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    /** الحد الأقصى لمحاولات الدخول الفاشلة قبل الحجب. */
    private const LOGIN_MAX_ATTEMPTS = 5;

    /** مدة الحجب بالثواني (15 دقيقة). */
    private const LOGIN_DECAY_SECONDS = 900;

    public function __construct(private AuditService $audit)
    {
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $credentials['username'] = trim($credentials['username']);

        $this->ensureIsNotRateLimited($request);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($request), self::LOGIN_DECAY_SECONDS);

            $this->audit->log(
                'auth.failed',
                null,
                'محاولة دخول فاشلة باسم المستخدم: '.$credentials['username'],
                null,
                null,
                'failure'
            );

            throw ValidationException::withMessages([
                'username' => 'بيانات الدخول غير صحيحة.',
            ]);
        }

        RateLimiter::clear($this->throttleKey($request));

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'username' => 'هذا الحساب معطّل. يرجى مراجعة المدير.',
            ]);
        }

        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();

        $this->audit->log('auth.login', $user, 'تسجيل دخول ناجح');

        return redirect()->intended($this->homeFor($user));
    }

    public function logout(Request $request): RedirectResponse
    {
        if ($user = $request->user()) {
            $this->audit->log('auth.logout', $user, 'تسجيل خروج');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** الصفحة الرئيسية حسب الدور (مسيّر المطعم ومساعد التكوين يُحوّلان لصفحتهما) */
    private function homeFor($user): string
    {
        if ($user->isCanteen()) {
            return route('canteen.index');
        }

        if ($user->isAssistant()) {
            return route('forecast.index');
        }

        return route('dashboard');
    }

    private function ensureIsNotRateLimited(Request $request): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey($request), self::LOGIN_MAX_ATTEMPTS)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'username' => 'تم تجاوز عدد المحاولات المسموح. أعد المحاولة بعد '.$seconds.' ثانية.',
        ]);
    }

    private function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower(trim((string) $request->input('username'))).'|'.$request->ip());
    }
}
