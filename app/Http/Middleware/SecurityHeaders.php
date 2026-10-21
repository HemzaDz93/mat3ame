<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * يضيف رؤوس أمان HTTP لكل الاستجابات (دفاع في العمق).
 * CSP مضبوط بما يتوافق مع الواجهة الحالية:
 *  - سكربتات داخلية (inline) و سمات onclick موجودة في الواجهات => 'unsafe-inline' مطلوب للسكربتات.
 *  - خطوط Google Fonts و bunny.net مستخدمة => مسموح بها في style-src/font-src.
 *  - الماسح الضوئي يستخدم الكاميرا و blob: => مسموح به في img/media/connect.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            // منع تضمين الموقع في إطار خارجي (حماية من Clickjacking).
            'X-Frame-Options' => 'SAMEORIGIN',
            // منع تخمين نوع المحتوى (حماية من MIME sniffing).
            'X-Content-Type-Options' => 'nosniff',
            // عدم تسريب المسار الكامل في الـ Referer.
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            // تقييد الوصول إلى أجهزة المستخدم (الكاميرا مطلوبة للماسح الضوئي).
            'Permissions-Policy' => 'camera=(self), microphone=(), geolocation=()',
            // سياسة أمان المحتوى.
            'Content-Security-Policy' => $this->contentSecurityPolicy(),
        ];

        // HSTS: يُفعّل فقط عبر HTTPS (الشهادة المحلية مطبّقة على الشبكة).
        if ($request->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            // لا نستبدل رؤوسًا ضبطها التطبيق صراحةً (مثل BarcodeController).
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function contentSecurityPolicy(): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline'",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net",
            "font-src 'self' data: https://fonts.gstatic.com https://fonts.bunny.net",
            "img-src 'self' data: blob:",
            "media-src 'self' blob:",
            "connect-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ];

        return implode('; ', $directives);
    }
}
