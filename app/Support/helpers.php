<?php

use App\Services\SettingsService;
use Illuminate\Support\Carbon;

if (! function_exists('setting')) {
    /** قراءة إعداد من إعدادات المؤسسة */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        /** @var SettingsService $service */
        $service = app(SettingsService::class);

        if ($key === null) {
            return $service->all();
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('setting_on')) {
    /** قراءة إعداد منطقي (0/1) من إعدادات المؤسسة */
    function setting_on(string $key, bool $default = true): bool
    {
        $value = setting($key, $default ? '1' : '0');

        return ! in_array((string) $value, ['0', '', 'false', 'off', 'no'], true);
    }
}

if (! function_exists('currency')) {
    function currency(): string
    {
        return (string) setting('currency', 'دج');
    }
}

if (! function_exists('money')) {
    /** تنسيق مبلغ مالي بالعملة */
    function money(float|int|string|null $amount): string
    {
        $value = is_numeric($amount) ? (float) $amount : 0.0;
        $formatted = rtrim(rtrim(number_format($value, 2, '.', ' '), '0'), '.');

        return $formatted.' '.currency();
    }
}

if (! function_exists('ar_date')) {
    /** تنسيق تاريخ بالأرقام */
    function ar_date(\DateTimeInterface|string|null $date, string $format = 'Y-m-d'): string
    {
        if (blank($date)) {
            return '—';
        }

        return Carbon::parse($date)->format($format);
    }
}

if (! function_exists('today_date')) {
    function today_date(): string
    {
        return now()->toDateString();
    }
}
