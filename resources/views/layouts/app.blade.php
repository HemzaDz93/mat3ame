<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') — {{ setting('institution_name', config('app.name')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
    @stack('styles')
</head>
<body>
    <div class="app-shell">
        @include('partials.sidebar')
        <div class="sidebar-backdrop"></div>

        <div class="app-main">
            @include('partials.topbar')

            <main class="container-page">
                @include('partials.flash')
                @yield('content')
            </main>

            <footer class="app-footer no-print">
                <span class="app-footer__copy">&copy; جميع الحقوق محفوظة</span>
                <span class="app-footer__sep" aria-hidden="true"></span>
                <span class="app-footer__author">تصميم: <strong>بن سليليح المسعود</strong></span>
            </footer>
        </div>
    </div>

    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
