<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $heading }} — {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        .error-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 24px; }
        .error-card { width: 100%; max-width: 520px; background: #fff; border-radius: 16px; box-shadow: var(--shadow-md); padding: 36px 28px; text-align: center; }
        .error-code { font-size: 4.6rem; font-weight: 800; line-height: 1; color: var(--c-primary); }
        .error-title { font-size: 1.3rem; font-weight: 700; margin-top: 8px; }
        .error-text { color: var(--c-muted); margin: 12px 0 22px; }
    </style>
</head>
<body>
    <div class="error-wrap">
        <div class="error-card">
            <div class="error-code">{{ $code }}</div>
            <div class="error-title">{{ $heading }}</div>
            <p class="error-text">{{ $message }}</p>
            <a href="{{ url('/') }}" class="btn btn-primary">العودة إلى الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
