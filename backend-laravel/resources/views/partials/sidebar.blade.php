<div class="sidebar d-flex flex-column p-3 text-white bg-dark">
    <a href="/" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
        <span class="fs-4 fw-bold">HMS Clinic 🏥</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link text-white {{ request()->is('dashboard') ? 'active' : '' }}">
                🏠 Dashboard
            </a>
        </li>

        @if(auth()->user()->role === 'MEDECIN')
            <li class="mt-3 small text-uppercase text-muted px-3">Médical</li>
            <li><a href="{{ route('appointments.index') }}" class="nav-link text-white">📅 Rendez-vous</a></li>
            <li><a href="{{ route('consultations.index') }}" class="nav-link text-white">📋 Consultations</a></li>
            <li><a href="{{ route('doctors.index') }}" class="nav-link text-white">👨‍⚕️ Équipe Médicale</a></li>
        @endif

        @if(auth()->user()->role === 'PATIENT')
            <li class="mt-3 small text-uppercase text-muted px-3">Espace Patient</li>
            <li><a href="{{ route('appointments.index') }}" class="nav-link text-white">📅 Mes RDV</a></li>
            <li><a href="{{ route('dossier.mine') }}" class="nav-link text-white">📂 Mon Dossier</a></li>
        @endif

        <li class="mt-3">
            <a href="{{ route('notifications.index') }}" class="nav-link text-white d-flex justify-content-between align-items-center">
                🔔 Notifications
                <span class="badge bg-danger rounded-pill">{{ auth()->user()->notifications()->count() }}</span>
            </a>
        </li>
    </ul>
</div>