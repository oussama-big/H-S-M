@extends('frontend.layouts.app')
@section('title', 'Rendez-vous')

@section('content')

<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Rendez-vous</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-calendar-check"></i>
            Organisation des rendez-vous
        </h1>
        <p class="page-hero-subtitle">Les demandes de rendez-vous se font depuis l espace patient authentifie afin de garantir la coherence du planning medical.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section contact-section">
    <div class="section-container">
        <div class="contact-content">
            <div class="contact-info">
                <div class="section-header" style="margin-bottom:2rem">
                    <span class="section-number">01</span>
                    <h2 class="section-title" style="font-size:1.6rem">
                        <span class="title-text">Coordonnees du cabinet</span>
                    </h2>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="contact-details">
                        <h4 class="contact-label">Telephone</h4>
                        <a href="tel:+212522001122" class="contact-value">+212 522 00 11 22</a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                    <div class="contact-details">
                        <h4 class="contact-label">Email</h4>
                        <a href="mailto:contact@medicare.ma" class="contact-value">contact@medicare.ma</a>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="contact-details">
                        <h4 class="contact-label">Adresse</h4>
                        <span class="contact-value">12 Rue Ibn Sina, Casablanca 20250</span>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon"><i class="fas fa-clock"></i></div>
                    <div class="contact-details">
                        <h4 class="contact-label">Horaires</h4>
                        <span class="contact-value">Lundi a vendredi, 08h00 a 16h00</span>
                        <span class="emergency-badge">Pause dejeuner: 13h00 a 14h00</span>
                    </div>
                </div>
            </div>

            <div class="contact-form-wrapper">
                <div class="section-header" style="margin-bottom:2rem">
                    <span class="section-number">02</span>
                    <h2 class="section-title" style="font-size:1.6rem">
                        <span class="title-text">Acces patient</span>
                    </h2>
                </div>

                <div class="contact-form js-guest-only">
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-lock"></i></div>
                        <div class="contact-details">
                            <h4 class="contact-label">Reservation securisee</h4>
                            <span class="contact-value">La prise de rendez-vous en ligne est reservee aux patients connectes.</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-user-plus"></i></div>
                        <div class="contact-details">
                            <h4 class="contact-label">Etapes</h4>
                            <span class="contact-value">1. Connectez-vous ou creez un compte patient.</span>
                            <span class="contact-value">2. Choisissez un medecin et un creneau disponible.</span>
                            <span class="contact-value">3. Retrouvez ensuite votre rendez-vous dans votre dashboard.</span>
                        </div>
                    </div>
                    <div class="hero-buttons" style="margin-bottom:0">
                        <a href="{{ route('login') }}" class="btn btn-primary">Connexion patient</a>
                        <a href="{{ route('register') }}" class="btn btn-secondary">Inscription</a>
                    </div>
                </div>

                <div class="contact-form js-patient-only" hidden>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-calendar-plus"></i></div>
                        <div class="contact-details">
                            <h4 class="contact-label">Espace rendez-vous</h4>
                            <span class="contact-value">Vous pouvez reserver, modifier et suivre vos rendez-vous depuis votre dashboard patient.</span>
                        </div>
                    </div>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-circle-info"></i></div>
                        <div class="contact-details">
                            <h4 class="contact-label">Regles de disponibilite</h4>
                            <span class="contact-value">Creneaux du lundi au vendredi, de 08h00 a 12h00 puis de 14h00 a 16h00.</span>
                        </div>
                    </div>
                    <div class="hero-buttons" style="margin-bottom:0">
                        <a href="{{ route('contact') }}" class="btn btn-primary" data-appointment-access>Ouvrir mon planning patient</a>
                    </div>
                </div>

                <div class="contact-form js-non-patient-auth-only" hidden>
                    <div class="contact-item">
                        <div class="contact-icon"><i class="fas fa-user-shield"></i></div>
                        <div class="contact-details">
                            <h4 class="contact-label">Reservation reservee aux patients</h4>
                            <span class="contact-value">Votre session est active, mais la reservation de rendez-vous est disponible uniquement dans l espace patient.</span>
                        </div>
                    </div>
                    <div class="hero-buttons" style="margin-bottom:0">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary" data-dashboard-link>Retourner a mon dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection
