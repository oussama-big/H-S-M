<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>Dashboard Patient | Medicare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script>
        (function () {
            var theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('frontend/css/doctor-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/patient-dashboard.css') }}">
</head>
<body class="doctor-dashboard-page patient-dashboard-page" data-theme="light">
    <div class="app-container">
        <button class="hamburger-btn" id="hamburger-btn" type="button" aria-label="Ouvrir la navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="overlay" id="overlay"></div>

        <div class="notification" id="notification">
            <i class="fas fa-circle-check"></i>
            <span id="notification-text">Dashboard patient charge.</span>
        </div>

        <div class="dashboard-modal" id="patient-ordonnance-modal" hidden>
            <div class="dashboard-modal__dialog">
                <div class="dashboard-modal__header">
                    <h3 id="patient-ordonnance-modal-title">Ordonnance</h3>
                    <button class="btn-icon" id="close-patient-ordonnance-modal-btn" type="button" aria-label="Fermer">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                <div class="patient-ordonnance-modal-body" id="patient-ordonnance-modal-body"></div>
            </div>
        </div>

        <div class="doctor-view">
            <aside class="sidebar" id="sidebar">
                <div class="logo">
                    <i class="fas fa-heartbeat"></i>
                    <span>Medicare</span>
                </div>

                <nav class="nav-menu" aria-label="Navigation principale">
                    <button class="nav-item active" data-view="dashboard" type="button">
                        <i class="fas fa-chart-line"></i>
                        <span>Dashboard</span>
                    </button>
                    <button class="nav-item" data-view="appointments" type="button">
                        <i class="fas fa-calendar-check"></i>
                        <span>Rendez-vous</span>
                    </button>
                    <button class="nav-item" data-view="records" type="button">
                        <i class="fas fa-file-medical"></i>
                        <span>Dossier medical</span>
                    </button>
                    <button class="nav-item" data-view="consultations" type="button">
                        <i class="fas fa-stethoscope"></i>
                        <span>Consultations</span>
                    </button>
                    <button class="nav-item" data-view="ordonnances" type="button">
                        <i class="fas fa-prescription-bottle-medical"></i>
                        <span>Ordonnances</span>
                    </button>
                </nav>

                <div class="sidebar-footer">
                    <div class="doctor-profile">
                        <div class="doctor-avatar" id="sidebar-patient-avatar">PT</div>
                        <div class="doctor-info">
                            <h3 id="sidebar-patient-name">Chargement...</h3>
                            <p id="sidebar-patient-meta">Patient</p>
                        </div>
                    </div>

                    <button class="btn sidebar-logout" id="sidebar-logout-btn" type="button" data-auth-logout>
                        <i class="fas fa-right-from-bracket"></i>
                        <span>Logout</span>
                    </button>
                </div>
            </aside>

            <main class="main-content">
                <header class="header">
                    <div class="date-info">
                        <h1 id="view-title">Dashboard</h1>
                        <p id="current-date">Chargement...</p>
                    </div>

                    <div class="header-actions">
                        <button class="btn btn-primary" id="create-appointment-header-btn" type="button">
                            <i class="fas fa-plus"></i>
                            <span>Creer rendez-vous</span>
                        </button>
                    </div>
                </header>

                <section class="view-container active" id="dashboard-view">
                    <div class="patients-view patient-dashboard-shell">
                        <article class="patient-next-card" id="next-appointment-card">
                            <div class="patient-next-card__header">
                                <div>
                                    <span class="about-badge">Your Next Appointment</span>
                                    <h2 id="next-appointment-date">Aucun rendez-vous planifie</h2>
                                    <p id="next-appointment-reason">Votre prochain rendez-vous s affichera ici des qu il sera confirme.</p>
                                </div>
                                <div class="patient-turn-pill" id="next-appointment-turn">--</div>
                            </div>

                            <div class="patient-next-grid">
                                <div class="record-detail">
                                    <span>Heure</span>
                                    <strong id="next-appointment-time">--:--</strong>
                                </div>
                                <div class="record-detail">
                                    <span>Medecin</span>
                                    <strong id="next-appointment-doctor">--</strong>
                                </div>
                                <div class="record-detail">
                                    <span>Lieu</span>
                                    <strong id="next-appointment-location">--</strong>
                                </div>
                                <div class="record-detail">
                                    <span>Specialite</span>
                                    <strong id="next-appointment-specialization">--</strong>
                                </div>
                            </div>

                            <p class="patient-next-message" id="next-appointment-message">
                                Your Turn Number Is --. Please proceed to your assigned room. Your turn will be called shortly.
                            </p>
                        </article>

                        <div class="patient-summary-grid">
                            <article class="patient-stat-card">
                                <span>Age</span>
                                <strong id="stat-age">--</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>Dossier</span>
                                <strong id="stat-dossier">--</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>Consultations</span>
                                <strong id="stat-consultations">0</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>Ordonnances</span>
                                <strong id="stat-ordonnances">0</strong>
                            </article>
                        </div>

                        <div class="record-grid">
                            <article class="record-card">
                                <h3>Informations sante</h3>
                                <div class="record-details" id="patient-health-summary"></div>
                            </article>
                            <article class="record-card">
                                <h3>Notifications recentes</h3>
                                <div class="patient-notification-list" id="patient-notification-list"></div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="appointments-view">
                    <div class="calendar-view patient-dashboard-shell">
                        <div class="calendar-header">
                            <h2>Rendez-vous</h2>
                            <button class="btn btn-secondary" id="reset-patient-appointment-form-btn" type="button">
                                <i class="fas fa-rotate-left"></i>
                                Reinitialiser
                            </button>
                        </div>

                        <div class="calendar-grid schedule-grid">
                            <div class="calendar-main">
                                <div class="patient-form-card">
                                    <div class="appointments-panel-header">
                                        <h3 id="patient-appointment-mode">Nouveau rendez-vous</h3>
                                    </div>

                                    <form id="patient-appointment-form" class="stack-form">
                                        <div class="input-group">
                                            <label for="patient-appointment-doctor"><i class="fas fa-user-doctor"></i> Medecin</label>
                                            <select id="patient-appointment-doctor" name="doctor_id" required></select>
                                        </div>

                                        <div class="calendar-controls">
                                            <div class="month-nav">
                                                <button class="btn-icon" id="patient-prev-month" type="button" aria-label="Mois precedent">
                                                    <i class="fas fa-chevron-left"></i>
                                                </button>
                                                <button class="btn-icon today-chip" id="patient-today-btn" type="button">Today</button>
                                                <button class="btn-icon" id="patient-next-month" type="button" aria-label="Mois suivant">
                                                    <i class="fas fa-chevron-right"></i>
                                                </button>
                                            </div>
                                            <h3 id="patient-current-month">Mois</h3>
                                        </div>

                                        <div class="calendar-container">
                                            <div class="calendar-weekdays">
                                                <div>Dim</div>
                                                <div>Lun</div>
                                                <div>Mar</div>
                                                <div>Mer</div>
                                                <div>Jeu</div>
                                                <div>Ven</div>
                                                <div>Sam</div>
                                            </div>
                                            <div class="calendar-days" id="patient-calendar-days"></div>
                                        </div>

                                        <div class="input-group">
                                            <label><i class="fas fa-clock"></i> Creneaux disponibles</label>
                                            <div class="slot-picker-meta" id="patient-selected-date-label">Choisissez un medecin puis une date pour voir les creneaux libres.</div>
                                            <div class="slot-grid" id="patient-available-slots"></div>
                                        </div>

                                        <div class="input-group">
                                            <label for="patient-appointment-reason"><i class="fas fa-clipboard-list"></i> Motif</label>
                                            <textarea id="patient-appointment-reason" name="reason" rows="4" placeholder="Motif du rendez-vous"></textarea>
                                        </div>

                                        <div class="modal-actions">
                                            <button class="btn btn-secondary" id="patient-appointment-cancel-btn" type="button">Annuler</button>
                                            <button class="btn btn-primary" id="patient-appointment-submit" type="submit">
                                                <i class="fas fa-check"></i>
                                                Enregistrer
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="appointments-sidebar">
                                <div class="appointments-list">
                                    <h3>Upcoming Appointments</h3>
                                    <div id="patient-upcoming-appointments"></div>
                                </div>

                                <div class="appointments-list">
                                    <h3>Historique des rendez-vous</h3>
                                    <div id="patient-appointment-history"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="records-view">
                    <div class="patients-view patient-dashboard-shell">
                        <div class="patients-header">
                            <h2>Dossier medical</h2>
                        </div>

                        <div class="record-grid">
                            <article class="record-card">
                                <h3>Informations personnelles</h3>
                                <div class="patient-info-list" id="patient-personal-info"></div>
                            </article>
                            <article class="record-card">
                                <h3>Resume medical</h3>
                                <div class="patient-info-list" id="patient-dossier-summary"></div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="consultations-view">
                    <div class="patients-view patient-dashboard-shell">
                        <div class="patients-header">
                            <h2>Consultations</h2>
                        </div>
                        <div class="consultation-list" id="patient-consultation-list"></div>
                    </div>
                </section>

                <section class="view-container" id="ordonnances-view">
                    <div class="patients-view patient-dashboard-shell">
                        <div class="patients-header">
                            <h2>Ordonnances</h2>
                        </div>
                        <div class="consultation-list" id="patient-ordonnance-list"></div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        window.patientDashboardConfig = {
            initialView: @json($initialView ?? 'dashboard'),
        };
    </script>
    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
    <script src="{{ asset('frontend/js/patient-dashboard.js') }}"></script>
</body>
</html>
