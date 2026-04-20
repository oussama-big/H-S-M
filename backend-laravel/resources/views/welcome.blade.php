<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HMS Clinic - Système de Gestion Hospitalière</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(rgba(13, 110, 253, 0.8), rgba(13, 110, 253, 0.8)), 
                        url('https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            height: 100vh;
            color: white;
            display: flex;
            align-items: center;
        }
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="#">HMS CLINIC 🏥</a>
            <div class="ms-auto">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary">Tableau de Bord</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Connexion</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">S'inscrire</a>
                @endauth
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container text-center text-md-start">
            <div class="row">
                <div class="col-md-7">
                    <h1 class="display-3 fw-bold mb-4">La technologie au service de votre santé.</h1>
                    <p class="lead mb-5">Bienvenue sur la plateforme de gestion HMS. Gérez vos rendez-vous, consultez votre dossier médical et communiquez avec vos médecins en toute sécurité.</p>
                    @guest
                        <a href="{{ route('register') }}" class="btn btn-light btn-lg px-5 py-3 fw-bold text-primary shadow">Commencer maintenant</a>
                    @endguest
                </div>
            </div>
        </div>
    </header>

    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Nos Services Digitaux</h2>
                <p class="text-muted">Une gestion simplifiée pour les patients et les professionnels de santé.</p>
            </div>
            <div class="row g-4 text-center">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon">📅</div>
                        <h5>Prise de Rendez-vous</h5>
                        <p class="text-muted">Réservez vos consultations en ligne avec le médecin de votre choix, 24h/24.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon">📂</div>
                        <h5>Dossier Médical</h5>
                        <p class="text-muted">Accédez à votre historique complet, vos diagnostics et vos plans de traitement.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm p-4">
                        <div class="feature-icon">💊</div>
                        <h5>Ordonnances</h5>
                        <p class="text-muted">Consultez et imprimez vos prescriptions dès la fin de votre consultation.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="bg-dark text-white py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; {{ date('Y') }} HMS Clinic - FSSM Marrakech. Tous droits réservés.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>