<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// تنبيه يومي بالسدادات التي تنتهي خلال مدة التنبيه المحددة في الإعدادات
Schedule::command('payments:expiry-alerts')->dailyAt('07:00');
