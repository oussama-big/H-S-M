<!DOCTYPE html>
<html lang="fr" dir="ltr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>Medicare - @yield('title', $title ?? 'Cabinet Medical')</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('frontend/css/medicare.css') }}">
    <style>
        :root {
            --primary: #38bdf8;
            --secondary: #0ea5e9;
            --accent: #bae6fd;
            --cyan: #7dd3fc;
            --purple: #93c5fd;
            --green: #22c55e;
            --red: #f87171;
            --bg-dark: #f7fbff;
            --bg-darker: #eef6ff;
            --bg-card: #ffffff;
            --bg-hover: #f3f9ff;
            --border: #d8e7f5;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --gradient-1: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 50%, #bae6fd 100%);
            --gradient-2: linear-gradient(135deg, #bae6fd 0%, #7dd3fc 45%, #38bdf8 100%);
            --gradient-3: linear-gradient(135deg, #0ea5e9 0%, #7dd3fc 100%);
            --shadow-sm: 0 2px 4px rgba(14, 165, 233, 0.06);
            --shadow-md: 0 10px 30px rgba(14, 165, 233, 0.1);
            --shadow-lg: 0 16px 42px rgba(14, 165, 233, 0.12);
            --shadow-xl: 0 24px 70px rgba(14, 165, 233, 0.16);
            --shadow-glow: 0 0 30px rgba(56, 189, 248, 0.2);
        }

        [data-theme="dark"] {
            --primary: #7dd3fc;
            --secondary: #38bdf8;
            --accent: #bae6fd;
            --cyan: #bae6fd;
            --purple: #93c5fd;
            --green: #4ade80;
            --bg-dark: #0f172a;
            --bg-darker: #020617;
            --bg-card: #111c31;
            --bg-hover: #17253d;
            --border: #22365c;
            --text-primary: #e2edf9;
            --text-secondary: #bfd0e8;
            --text-muted: #8ea7c5;
        }
    </style>
    @stack('styles')
</head>
<body class="body-main" data-theme="light">
    @include('frontend.layouts.header')

    <main class="main-content">
        @yield('content')
    </main>

    @include('frontend.layouts.footer')

    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
    <script src="{{ asset('frontend/js/medicare.js') }}"></script>
    <script>
        document.body.setAttribute('data-theme', localStorage.getItem('theme') || 'light');
    </script>
    @stack('scripts')
</body>
</html>
