@extends('frontend.layouts.app')
@section('title', 'Nos Services')

@section('content')

<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Services</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-stethoscope"></i>
            Nos Services Medicaux
        </h1>
        <p class="page-hero-subtitle">Des services structures autour de la consultation, du suivi et de la coordination des rendez-vous.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section skills-section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-number">01</span>
            <h2 class="section-title">
                <span class="title-bracket"><i class="fas fa-briefcase-medical"></i></span>
                <span class="title-text">Parcours de prise en charge</span>
            </h2>
            <div class="section-line"></div>
        </div>

        <div class="skills-grid">
            <div class="skill-category">
                <h3 class="category-title">Consultations</h3>
                <div class="skill-items">
                    <div class="skill-item" data-percent="100">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-user-doctor"></i> Medecine generale</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="92">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-heart-pulse"></i> Suivi cardiometabolique</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="88">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-child-reaching"></i> Suivi pediatrique</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                </div>
            </div>

            <div class="skill-category">
                <h3 class="category-title">Examens et suivi</h3>
                <div class="skill-items">
                    <div class="skill-item" data-percent="95">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-file-medical"></i> Dossier medical centralise</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="90">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-notes-medical"></i> Compte rendu de consultation</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="94">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-prescription-bottle-medical"></i> Ordonnances et suivi</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                </div>
            </div>

            <div class="skill-category">
                <h3 class="category-title">Coordination</h3>
                <div class="skill-items">
                    <div class="skill-item" data-percent="100">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-calendar-days"></i> Rendez-vous planifies</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="100">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-user-clock"></i> File d attente par medecin</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                    <div class="skill-item" data-percent="96">
                        <div class="skill-header">
                            <span class="skill-name"><i class="fas fa-bell"></i> Notifications de suivi</span>
                            <span class="skill-percent">0%</span>
                        </div>
                        <div class="skill-bar"><div class="skill-progress"></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-header" style="margin-top:5rem">
            <span class="section-number">02</span>
            <h2 class="section-title">
                <span class="title-bracket"><i class="fas fa-tags"></i></span>
                <span class="title-text">Formules de consultation</span>
            </h2>
            <div class="section-line"></div>
        </div>

        <div class="tarifs-grid">
            <div class="tarif-card">
                <div class="tarif-icon"><i class="fas fa-stethoscope"></i></div>
                <h3>Consultation standard</h3>
                <div class="tarif-price">200 <span>MAD</span></div>
                <ul class="tarif-features">
                    <li><i class="fas fa-check"></i> Evaluation clinique</li>
                    <li><i class="fas fa-check"></i> Notes medicales</li>
                    <li><i class="fas fa-check"></i> Ordonnance si necessaire</li>
                </ul>
                <a href="{{ route('contact') }}" class="btn btn-primary" data-appointment-access>Acceder aux rendez-vous</a>
            </div>

            <div class="tarif-card tarif-featured">
                <div class="tarif-badge">Parcours suivi</div>
                <div class="tarif-icon"><i class="fas fa-heart-pulse"></i></div>
                <h3>Suivi maladie chronique</h3>
                <div class="tarif-price">250 <span>MAD</span></div>
                <ul class="tarif-features">
                    <li><i class="fas fa-check"></i> Consultation de suivi</li>
                    <li><i class="fas fa-check"></i> Mise a jour du dossier</li>
                    <li><i class="fas fa-check"></i> Plan de traitement</li>
                    <li><i class="fas fa-check"></i> Prochaine visite planifiee</li>
                </ul>
                <a href="{{ route('contact') }}" class="btn btn-primary" data-appointment-access>Acceder aux rendez-vous</a>
            </div>

            <div class="tarif-card">
                <div class="tarif-icon"><i class="fas fa-file-waveform"></i></div>
                <h3>Bilan oriente</h3>
                <div class="tarif-price">350 <span>MAD</span></div>
                <ul class="tarif-features">
                    <li><i class="fas fa-check"></i> Consultation + orientation</li>
                    <li><i class="fas fa-check"></i> Coordination examens</li>
                    <li><i class="fas fa-check"></i> Restitution medicale</li>
                </ul>
                <a href="{{ route('contact') }}" class="btn btn-primary" data-appointment-access>Acceder aux rendez-vous</a>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initSkillAnimations('body');
        animateCardsOnScroll();
    });
</script>
@endpush
