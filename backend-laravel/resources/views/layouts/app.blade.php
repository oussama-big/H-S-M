<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Clinic - @yield('title')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { display: flex; min-height: 100vh; flex-direction: column; }
        .sidebar { width: 250px; min-height: 100vh; position: fixed; }
        main { margin-left: 250px; flex: 1; background: #f4f7f6; }
        .active { background-color: #0d6efd !important; }
        @media (max-width: 768px) { .sidebar { display: none; } main { margin-left: 0; } }
    </style>
</head>
<body>

    <div class="d-flex">
        @include('partials.sidebar')

        <main>
            @include('partials.header')

            <div class="container-fluid p-4">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>

            @include('partials.footer')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>