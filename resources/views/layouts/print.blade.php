<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'طباعة') — {{ setting('institution_name', config('app.name')) }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @include('partials.card-style', ['prefix' => $cardPrefix ?? ''])
    <link rel="stylesheet" href="{{ asset('css/card.css') }}">
    <link rel="stylesheet" href="{{ asset('css/cards.css') }}">
    @stack('styles')
</head>
<body class="print-layout">
    <div class="cards-toolbar no-print" style="padding:14px 18px;">
        <button type="button" class="btn btn-primary" onclick="window.print()">
            <x-icon name="print" size="16" /> طباعة
        </button>
        <button type="button" class="btn btn-secondary" onclick="window.close()">إغلاق</button>
        @yield('toolbar')
    </div>
    @yield('content')
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
