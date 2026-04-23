@extends('frontend.layouts.app')
@section('title', 'Notre Equipe')

@section('content')

<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>Equipe</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-user-md"></i>
            Notre Equipe Medicale
        </h1>
        <p class="page-hero-subtitle">Une organisation medicale et administrative pensee pour un suivi continu des patients.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section experience-section">
    <div class="section-container">
        <div class="section-header">
            <span class="section-number">01</span>
            <h2 class="section-title">
                <span class="title-bracket"><i class="fas fa-user-doctor"></i></span>
                <span class="title-text">Profils cles</span>
            </h2>
            <div class="section-line"></div>
        </div>

        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <div class="timeline-year">Pole medical</div>
                        <div class="timeline-badge">Consultation</div>
                    </div>
                    <h3 class="timeline-title">Medecins de consultation</h3>
                    <div class="timeline-company">
                        <i class="fas fa-stethoscope"></i>
                        Suivi clinique, diagnostic et ordonnance
                    </div>
                    <p class="timeline-description">
                        Les medecins travaillent a partir d un planning commun et d un dossier medical centralise.
                        Chaque consultation met a jour le suivi du patient, les notes medicales et les prochaines visites.
                    </p>
                    <div class="timeline-achievements">
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Consultation filtree par role</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Ordonnance et dossier relies</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Suivi de file en temps reel</span>
                        </div>
                    </div>
                    <div class="timeline-tags">
                        <span class="tag">Consultation</span>
                        <span class="tag">Dossier medical</span>
                        <span class="tag">Ordonnance</span>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <div class="timeline-year">Pole accueil</div>
                        <div class="timeline-badge">Coordination</div>
                    </div>
                    <h3 class="timeline-title">Secretariat medical</h3>
                    <div class="timeline-company">
                        <i class="fas fa-user-clock"></i>
                        Accueil, file d attente et creation des rendez-vous
                    </div>
                    <p class="timeline-description">
                        Le secretariat gere les patients presents, les rendez-vous physiques et le suivi des files par medecin.
                        Il dispose d une vue claire sur les consultations en cours sans acceder aux donnees sensibles.
                    </p>
                    <div class="timeline-achievements">
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Ajout de patients en base</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Notification lors du passage au patient suivant</span>
                        </div>
                    </div>
                    <div class="timeline-tags">
                        <span class="tag">Accueil</span>
                        <span class="tag">Rendez-vous</span>
                        <span class="tag">Patient Queue</span>
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-marker"></div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <div class="timeline-year">Pole patient</div>
                        <div class="timeline-badge">Autonomie</div>
                    </div>
                    <h3 class="timeline-title">Espace patient</h3>
                    <div class="timeline-company">
                        <i class="fas fa-user-shield"></i>
                        Rendez-vous, consultations et ordonnances
                    </div>
                    <p class="timeline-description">
                        Le patient consulte ses rendez-vous a venir, son dossier medical, ses consultations et ses ordonnances.
                        Les creneaux proposes respectent les vraies disponibilites des medecins.
                    </p>
                    <div class="timeline-achievements">
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Reservation authentifiee</span>
                        </div>
                        <div class="achievement-item">
                            <i class="fas fa-check-circle"></i>
                            <span>Telechargement et impression des ordonnances</span>
                        </div>
                    </div>
                    <div class="timeline-tags">
                        <span class="tag">Rendez-vous</span>
                        <span class="tag">Consultations</span>
                        <span class="tag">Ordonnances</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        initTimelineAnimations();
    });
</script>
@endpush
