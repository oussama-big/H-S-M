@extends('frontend.layouts.app')
@section('title', 'A Propos')

@section('content')

<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <span>A Propos</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-hospital-alt"></i>
            A Propos de Medicare
        </h1>
        <p class="page-hero-subtitle">Un cabinet organise autour du suivi medical, de la coordination et de la continuite des soins.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section about-section">
    <div class="section-container">
        <div class="about-content">
            <div class="about-text-wrapper">
                <div class="section-header">
                    <span class="section-number">01</span>
                    <h2 class="section-title">
                        <span class="title-bracket"><i class="fas fa-clipboard-check"></i></span>
                        <span class="title-text">Notre Organisation</span>
                    </h2>
                    <div class="section-line"></div>
                </div>

                <p class="about-text">
                    Medicare rassemble les principales fonctions d un cabinet moderne dans une seule plateforme:
                    accueil administratif, agenda medical, consultation, dossier medical et ordonnances.
                    Chaque information utile est tracee et accessible selon le role de l utilisateur.
                </p>
                <p class="about-text">
                    Nous privilegions une prise en charge claire: un patient authentifie reserve son rendez-vous,
                    le secretariat organise les files d attente, le medecin consulte avec des donnees a jour et
                    le suivi reste disponible dans le dossier medical sans duplication.
                </p>

                <div class="about-stats">
                    <div class="stat-item">
                        <div class="stat-number" data-count="5">0</div>
                        <div class="stat-label">Specialites suivies</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-count="2">0</div>
                        <div class="stat-label">Flux de coordination</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number" data-count="1">0</div>
                        <div class="stat-label">Dossier medical unifie</div>
                    </div>
                </div>
            </div>

            <div class="about-image-wrapper">
                <div class="about-image-container">
                    <div class="code-block">
                        <div class="code-line">
                            <span class="code-keyword">cabinet</span>
                            <span class="code-variable"> Medicare</span>
                            <span class="code-brace"> {</span>
                        </div>
                        <div class="code-line indent">
                            <span class="code-property">horaires</span>
                            <span class="code-operator"> :</span>
                            <span class="code-string"> 'Lun-Ven 08:00-16:00'</span><span class="code-comma">,</span>
                        </div>
                        <div class="code-line indent">
                            <span class="code-property">reservation</span>
                            <span class="code-operator"> :</span>
                            <span class="code-string"> 'patient authentifie'</span><span class="code-comma">,</span>
                        </div>
                        <div class="code-line indent">
                            <span class="code-property">pause</span>
                            <span class="code-operator"> :</span>
                            <span class="code-string"> '13:00-14:00'</span><span class="code-comma">,</span>
                        </div>
                        <div class="code-line indent">
                            <span class="code-property">dossier</span>
                            <span class="code-operator"> :</span>
                            <span class="code-string"> 'consultations + ordonnances'</span>
                        </div>
                        <div class="code-line">
                            <span class="code-brace">}</span><span class="code-semicolon">;</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="values-section">
            <div class="section-header" style="margin-top:4rem">
                <span class="section-number">02</span>
                <h2 class="section-title">
                    <span class="title-bracket"><i class="fas fa-heart"></i></span>
                    <span class="title-text">Nos Engagements</span>
                </h2>
                <div class="section-line"></div>
            </div>
            <div class="values-grid">
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-user-check"></i></div>
                    <h3>Clarte</h3>
                    <p>Chaque role voit les bonnes informations au bon moment, sans surcharge ni confusion.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-shield-alt"></i></div>
                    <h3>Securite</h3>
                    <p>Les acces sont filtres par role afin de proteger les donnees medicales et administratives.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-people-arrows"></i></div>
                    <h3>Coordination</h3>
                    <p>Patient, secretariat et medecin travaillent sur un meme flux de donnees, sans ressaisie inutile.</p>
                </div>
                <div class="value-card">
                    <div class="value-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Suivi</h3>
                    <p>Les consultations, rendez-vous et ordonnances restent relies au dossier medical du patient.</p>
                </div>
            </div>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        animateStatsOnScroll();
        animateCardsOnScroll();
    });
</script>
@endpush
