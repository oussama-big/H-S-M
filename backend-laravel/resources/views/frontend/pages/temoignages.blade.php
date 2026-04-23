@extends('frontend.layouts.app')
@section('title', 'Temoignages')

@section('content')

<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Temoignages</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-comments"></i>
            Retours Patients
        </h1>
        <p class="page-hero-subtitle">Des retours centres sur l accueil, la lisibilite du parcours de soins et la qualite du suivi.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section projects-section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-number">01</span>
            <h2 class="section-title">
                <span class="title-bracket"><i class="fas fa-star"></i></span>
                <span class="title-text">Avis verifies</span>
            </h2>
            <div class="section-line"></div>
        </div>

        <div class="global-rating">
            <div class="rating-number">4.8</div>
            <div class="rating-stars">
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star"></i><i class="fas fa-star"></i>
                <i class="fas fa-star-half-alt"></i>
            </div>
            <div class="rating-total">Base sur des retours recueillis apres consultation et suivi</div>
        </div>

        <div class="projects-grid">
            <div class="project-card">
                <div class="project-image testi-bg">
                    <div class="project-overlay">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </div>
                    <div class="project-placeholder testi-placeholder">
                        <i class="fas fa-quote-left"></i>
                    </div>
                </div>
                <div class="project-content">
                    <h3 class="project-title">Patient suivi cardio</h3>
                    <p class="project-description">
                        Les rendez-vous sont clairs, les horaires sont respectes et le compte rendu de consultation
                        reste disponible dans le dossier medical. Le suivi est beaucoup plus lisible.
                    </p>
                    <div class="project-tags">
                        <span class="tag"><i class="fas fa-heartbeat"></i> Suivi chronique</span>
                        <span class="tag">Rendez-vous planifies</span>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image testi-bg">
                    <div class="project-overlay">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </div>
                    <div class="project-placeholder testi-placeholder">
                        <i class="fas fa-quote-left"></i>
                    </div>
                </div>
                <div class="project-content">
                    <h3 class="project-title">Patient famille</h3>
                    <p class="project-description">
                        L accueil du secretariat est rassurant et la prise de rendez-vous depuis l espace patient est simple.
                        Les creneaux disponibles sont proposes clairement, sans confusion.
                    </p>
                    <div class="project-tags">
                        <span class="tag"><i class="fas fa-calendar-check"></i> Reservation</span>
                        <span class="tag">Accueil</span>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image testi-bg">
                    <div class="project-overlay">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </div>
                    <div class="project-placeholder testi-placeholder">
                        <i class="fas fa-quote-left"></i>
                    </div>
                </div>
                <div class="project-content">
                    <h3 class="project-title">Patient suivi regulier</h3>
                    <p class="project-description">
                        Les ordonnances telechargeables et les informations de prochaine visite evitent les oublis.
                        L espace patient est utile entre deux consultations.
                    </p>
                    <div class="project-tags">
                        <span class="tag"><i class="fas fa-prescription-bottle-medical"></i> Ordonnances</span>
                        <span class="tag">Dossier medical</span>
                    </div>
                </div>
            </div>

            <div class="project-card">
                <div class="project-image testi-bg">
                    <div class="project-overlay">
                        <span class="star-rating">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i>
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </span>
                    </div>
                    <div class="project-placeholder testi-placeholder">
                        <i class="fas fa-quote-left"></i>
                    </div>
                </div>
                <div class="project-content">
                    <h3 class="project-title">Patient consultation generale</h3>
                    <p class="project-description">
                        Le parcours est bien structure: rendez-vous, accueil, consultation puis recuperation de l ordonnance.
                        Tout est centralise et facile a retrouver ensuite.
                    </p>
                    <div class="project-tags">
                        <span class="tag"><i class="fas fa-file-medical"></i> Parcours de soins</span>
                        <span class="tag">Clarte</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => { animateCardsOnScroll(); });
</script>
@endpush
