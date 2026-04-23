<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>@yield('title', 'MediCare')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            min-height: 100vh;
            font-family: 'Montserrat', sans-serif;
            background: #ecf0f3;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
    </style>

    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
