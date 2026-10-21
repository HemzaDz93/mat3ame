<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'active' => EnsureUserIsActive::class,
        ]);

        // حد عام لكل نقاط الويب: 120 طلبًا في الدقيقة لكل عنوان IP (حماية من الإساءة والتخمين).
        $middleware->web(append: ['throttle:120,1']);

        // رؤوس أمان HTTP على كل الطلبات (CSP، X-Frame-Options، HSTS، ...).
        $middleware->prepend(SecurityHeaders::class);

        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ردّ ودّي عند تجاوز حدّ المحاولات (Rate Limiting).
        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            $message = 'تم تجاوز عدد المحاولات المسموح. يرجى المحاولة لاحقًا.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 429);
            }

            // صفحة الدخول تعرض أخطاء التحقق فقط.
            if ($request->routeIs('login.attempt')) {
                return redirect()->route('login')->withErrors([
                    'username' => 'تم تجاوز عدد محاولات الدخول المسموح. يرجى المحاولة بعد 15 دقيقة.',
                ]);
            }

            return back()->with('error', $message);
        });

        // ردّ ودّي عند تجاوز حجم الطلب المسموح به.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            $message = 'حجم الطلب أكبر من الحد المسموح به. يرجى اختيار ملف أصغر.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 413);
            }

            return back()->with('error', $message);
        });
    })->create();
