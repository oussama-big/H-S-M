@extends('frontend.layouts.app')
@section('title', 'Accueil')

@section('content')

<section id="home" class="hero-section">
    <div class="hero-background">
        <div class="medical-grid-bg"></div>
        <div class="floating-particles" id="particles"></div>
    </div>

    <div class="hero-container">
        <div class="hero-content">
            <div class="hero-greeting">
                <span class="greeting-badge">
                    <i class="fas fa-shield-alt"></i> Parcours de soins coordonne
                </span>
            </div>

            <h1 class="hero-name" id="heroName">
                <span class="name-prefix">Cabinet Medical</span>
                <span class="name-value">Medicare</span>
            </h1>

            <div class="hero-title">
                <span class="title-prefix"><i class="fas fa-heartbeat"></i></span>
                <span class="title-text">Consultations, rendez-vous et suivi medical dans un espace unifie</span>
            </div>

            <p class="hero-description">
                Medicare accompagne les patients, les medecins et le secretariat avec une organisation claire:
                prise de rendez-vous securisee, suivi des consultations, ordonnances et dossier medical centralise.
                Notre objectif est simple: une prise en charge fiable, lisible et professionnelle.
            </p>

            <div class="hero-buttons">
                <a href="{{ route('contact') }}" class="btn btn-primary" data-appointment-access>
                    <span>Acceder aux rendez-vous</span>
                    <i class="fas fa-calendar-plus"></i>
                </a>
                <a href="{{ route('services') }}" class="btn btn-secondary">
                    <span>Voir les services</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="hero-social">
                <a href="tel:+212522001122" class="social-icon" title="Telephone">
                    <i class="fas fa-phone"></i>
                </a>
                <a href="mailto:contact@medicare.ma" class="social-icon" title="Email">
                    <i class="fas fa-envelope"></i>
                </a>
                <a href="{{ route('about') }}" class="social-icon" title="A Propos">
                    <i class="fas fa-hospital"></i>
                </a>
                <a href="{{ route('contact') }}" class="social-icon" title="Rendez-vous" data-appointment-access>
                    <i class="fas fa-calendar-check"></i>
                </a>
            </div>
        </div>

        <div class="hero-image-wrapper">
            <div class="hero-image-container">
                <div class="profile-image-glow"></div>
                <div class="profile-image-frame">
                    <div class="profile-image" id="profileImage">
                        <div class="profile-placeholder">
                            <i class="fas fa-user-md"></i>
                        </div>
                    </div>
                </div>
                <div class="floating-badge badge-1">
                    <i class="fas fa-stethoscope"></i>
                    <div class="badge-content">
                        <span class="badge-title">Consultation generale</span>
                        <span class="badge-libs">Suivi regulier et orientation</span>
                    </div>
                </div>
                <div class="floating-badge badge-2">
                    <i class="fas fa-file-medical"></i>
                    <div class="badge-content">
                        <span class="badge-title">Dossier centralise</span>
                        <span class="badge-libs">Consultations et ordonnances</span>
                    </div>
                </div>
                <div class="floating-badge badge-3">
                    <i class="fas fa-calendar-day"></i>
                    <div class="badge-content">
                        <span class="badge-title">Planning maitrise</span>
                        <span class="badge-libs">Lun-Ven, 08h00 a 16h00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="scroll-indicator">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span class="scroll-text">Decouvrir</span>
    </div>
</section>

<section class="stats-strip">
    <div class="stats-strip-container">
        <div class="strip-stat">
            <i class="fas fa-users"></i>
            <div>
                <span class="strip-number" data-count="3200">0</span><span>+</span>
                <span class="strip-label">Patients suivis</span>
            </div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-stat">
            <i class="fas fa-user-doctor"></i>
            <div>
                <span class="strip-number" data-count="5">0</span>
                <span class="strip-label">Specialites principales</span>
            </div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-stat">
            <i class="fas fa-clock"></i>
            <div>
                <span class="strip-number" data-count="8">0</span>
                <span class="strip-label">Heures d ouverture par jour</span>
            </div>
        </div>
        <div class="strip-divider"></div>
        <div class="strip-stat">
            <i class="fas fa-star"></i>
            <div>
                <span class="strip-number" data-count="97">0</span><span>%</span>
                <span class="strip-label">Satisfaction patients</span>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        generateParticles();
        initHeroPage();
        animateStripStats();
    });
</script>
@endpush
