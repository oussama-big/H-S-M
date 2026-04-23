@extends('frontend.layouts.app')
@section('title', 'Mon Espace')

@section('content')
<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Mon Espace</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-tachometer-alt"></i>
            Bienvenue, {{ Auth::user()->name }} !
        </h1>
        <p class="page-hero-subtitle">Gérez vos rendez-vous et votre dossier médical</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section">
    <div class="section-container">
        <div class="values-grid">
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>Mes Rendez-vous</h3>
                <p>Consultez et gérez vos prochains rendez-vous médicaux.</p>
                <a href="{{ route('contact') }}" class="btn btn-primary" style="margin-top:1rem;display:inline-flex">
                    Prendre RDV <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-file-medical"></i></div>
                <h3>Mon Dossier</h3>
                <p>Accédez à vos ordonnances, résultats d'analyses et historique médical.</p>
            </div>
            <div class="value-card">
                <div class="value-icon"><i class="fas fa-user-edit"></i></div>
                <h3>Mon Profil</h3>
                <p>Mettez à jour vos informations personnelles et préférences.</p>
                <a href="{{ route('profile') }}" class="btn btn-secondary" style="margin-top:1rem;display:inline-flex">
                    Modifier <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

