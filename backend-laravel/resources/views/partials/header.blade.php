<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="{{ url('/') }}">HMS Clinic 🏥</a>
        
        <div class="ms-auto d-flex align-items-center">
            @guest
                <a href="{{ route('login') }}" class="btn btn-outline-primary me-2">Connexion</a>
                <a href="{{ route('register') }}" class="btn btn-primary">S'inscrire</a>
            @endguest

            @auth
                <span class="me-3 text-muted d-none d-md-inline">
                    Connecté en tant que : <strong>{{ auth()->user()->nom }}</strong> 
                    <span class="badge bg-info text-dark small">{{ auth()->user()->role }}</span>
                </span>

                <div class="dropdown">
                    <a href="#" class="btn btn-light rounded-circle border shadow-sm" data-bs-toggle="dropdown">
                        👤
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li><a class="dropdown-item" href="{{ route('dashboard') }}">🏠 Dashboard</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST" id="logout-form">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger fw-bold">
                                    Déconnexion
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @endauth
        </div>
    </div>
</nav>