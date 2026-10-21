<?php

namespace App\Providers;

use App\Services\NotificationService;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // في الإنتاج يكون الاتصال عبر HTTPS دائماً (شهادة SSL على الطبقة الأمامية
        // أو خادم ويب)، فنفرض توليد كل الروابط بـ https لتجنب المحتوى المختلط.
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        View::composer(['partials.sidebar', 'partials.topbar'], function ($view) {
            $count = auth()->check()
                ? app(NotificationService::class)->unreadCount(auth()->id())
                : 0;

            $view->with('unreadCount', $count);
        });
    }
}
