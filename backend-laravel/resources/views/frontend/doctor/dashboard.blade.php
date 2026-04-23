<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>Dashboard Medecin | Medicare</title>
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
</head>
<body class="doctor-dashboard-page" data-theme="light">
    <div class="app-container">
        <button class="hamburger-btn" id="hamburger-btn" type="button" aria-label="Ouvrir la navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="overlay" id="overlay"></div>

        <div class="notification" id="notification">
            <i class="fas fa-circle-check"></i>
            <span id="notification-text">Dashboard charge.</span>
        </div>

        <div class="dashboard-modal" id="reschedule-modal" hidden>
            <div class="dashboard-modal__dialog">
                <div class="dashboard-modal__header">
                    <h3>Reprogrammer le rendez-vous</h3>
                    <button class="btn-icon" id="close-reschedule-modal-btn" type="button" aria-label="Fermer">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>

                <form id="reschedule-form" class="stack-form">
                    <div class="input-group">
                        <label for="reschedule-date"><i class="fas fa-calendar-days"></i> Nouvelle date</label>
                        <input type="datetime-local" id="reschedule-date" name="appointment_date" required>
                    </div>

                    <div class="input-group">
                        <label for="reschedule-reason"><i class="fas fa-clipboard-list"></i> Motif</label>
                        <textarea id="reschedule-reason" name="reason" rows="4"></textarea>
                    </div>

                    <div class="modal-actions">
                        <button class="btn btn-secondary" id="cancel-reschedule-btn" type="button">Annuler</button>
                        <button class="btn btn-warning" type="submit">
                            <i class="fas fa-clock"></i>
                            Reprogrammer
                        </button>
                    </div>
                </form>
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
                    <button class="nav-item" data-view="schedule" type="button">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Schedule</span>
                    </button>
                    <button class="nav-item" data-view="patients" type="button">
                        <i class="fas fa-users"></i>
                        <span>Patients</span>
                    </button>
                    <button class="nav-item" data-view="consultations" type="button">
                        <i class="fas fa-stethoscope"></i>
                        <span>Consultations</span>
                    </button>
                    <button class="nav-item" data-view="records" type="button">
                        <i class="fas fa-file-medical"></i>
                        <span>Dossiers medicaux</span>
                    </button>
                    <button class="nav-item" data-view="contacts" type="button">
                        <i class="fas fa-address-book"></i>
                        <span>Contacts</span>
                    </button>
                    <button class="nav-item" data-view="about" type="button">
                        <i class="fas fa-circle-info"></i>
                        <span>About</span>
                    </button>
                </nav>

                <div class="sidebar-footer">
                    <div class="doctor-profile">
                        <div class="doctor-avatar" id="sidebar-doctor-avatar">MD</div>
                        <div class="doctor-info">
                            <h3 id="sidebar-doctor-name">Chargement...</h3>
                            <p id="sidebar-doctor-specialization">Medecin</p>
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
                        <p id="current-date">Chargement de la date...</p>
                    </div>

                    <div class="header-actions">
                        <button class="btn btn-secondary" id="new-ordonnance-btn" type="button">
                            <i class="fas fa-prescription"></i>
                            <span>New ordonnance</span>
                        </button>
                        <button class="btn btn-primary" id="next-patient-btn" type="button">
                            <i class="fas fa-user-clock"></i>
                            <span>Next Patient</span>
                        </button>
                        <button class="btn btn-secondary" id="print-report-btn" type="button">
                            <i class="fas fa-print"></i>
                            <span>Print Report</span>
                        </button>
                        <button class="btn btn-success" id="print-ordonnance-btn" type="button">
                            <i class="fas fa-prescription-bottle-medical"></i>
                            <span>Print Ordonnance</span>
                        </button>
                        <button class="btn-icon" id="notifications-btn" type="button" aria-label="Notifications">
                            <i class="fas fa-bell"></i>
                        </button>
                    </div>
                </header>

                <section class="view-container active" id="dashboard-view">
                    <div class="dashboard-grid">
                        <div class="left-column">
                            <article class="current-patient-card">
                                <div class="card-header">
                                    <h2>Current Consultation</h2>
                                    <div class="live-badge" id="current-consultation-badge">LIVE</div>
                                </div>

                                <div class="patient-info">
                                    <div class="patient-avatar" id="current-patient-avatar">NA</div>
                                    <div class="patient-details">
                                        <h3 id="current-patient-name">Patient</h3>
                                        <p>
                                            <i class="fas fa-id-card"></i>
                                            <span>Numero de tour : <strong id="current-patient-turn">-</strong></span>
                                        </p>
                                        <p>
                                            <i class="fas fa-cake-candles"></i>
                                            <span id="current-patient-age">Age non renseigne</span>
                                        </p>
                                        <p>
                                            <i class="fas fa-heart-pulse"></i>
                                            <span id="current-patient-condition">Aucune consultation active.</span>
                                        </p>
                                    </div>
                                </div>

                                <div class="patient-navigation">
                                    <button class="nav-btn" id="prev-patient-btn" type="button">
                                        <i class="fas fa-chevron-left"></i>
                                        Previous
                                    </button>

                                    <div class="current-turn">
                                        <span>Current Turn</span>
                                        <h4 id="current-turn-display">-</h4>
                                    </div>

                                    <button class="nav-btn" id="next-patient-inline-btn" type="button">
                                        Next
                                        <i class="fas fa-chevron-right"></i>
                                    </button>
                                </div>

                                <form class="medical-notes" id="medical-notes-form">
                                    <h4><i class="fas fa-file-medical-alt"></i> Medical Notes</h4>

                                    <div class="consultation-meta-grid">
                                        <div class="mini-stat">
                                            <span>Statut</span>
                                            <strong id="current-consultation-status">En attente</strong>
                                        </div>
                                        <div class="mini-stat">
                                            <span>Heure</span>
                                            <strong id="current-consultation-time">--:--</strong>
                                        </div>
                                        <div class="mini-stat">
                                            <span>Cabinet</span>
                                            <strong id="current-consultation-room">--</strong>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <label for="diagnosis"><i class="fas fa-stethoscope"></i> Diagnostic</label>
                                        <input type="text" id="diagnosis" name="diagnosis">
                                    </div>

                                    <div class="input-group">
                                        <label for="symptoms"><i class="fas fa-notes-medical"></i> Symptomes</label>
                                        <input type="text" id="symptoms" name="symptoms">
                                    </div>

                                    <div class="input-group">
                                        <label for="treatment"><i class="fas fa-syringe"></i> Plan de traitement</label>
                                        <textarea id="treatment" name="treatment"></textarea>
                                    </div>

                                    <div class="input-group">
                                        <label for="next-visit"><i class="fas fa-calendar-check"></i> Prochaine visite</label>
                                        <input type="date" id="next-visit" name="next_visit">
                                    </div>

                                    <p class="helper-text" id="consultation-helper-text">
                                        Les notes enregistrees mettent a jour le dossier medical du patient et peuvent preparer une visite de suivi.
                                    </p>

                                    <section class="ordonnance-panel" id="ordonnance-panel" hidden>
                                        <div class="ordonnance-actions">
                                            <button class="btn btn-primary" id="save-ordonnance-btn" type="button">
                                                <i class="fas fa-floppy-disk"></i>
                                                Sauvegarder ordonnance
                                            </button>
                                            <button class="btn btn-secondary" id="close-ordonnance-panel-btn" type="button">
                                                <i class="fas fa-xmark"></i>
                                                Masquer
                                            </button>
                                        </div>

                                        <h5><i class="fas fa-prescription-bottle-medical"></i> Ordonnance</h5>

                                        <div class="ordonnance-patient-card">
                                            <div>
                                                <span>Patient</span>
                                                <strong id="ordonnance-patient-name">Aucun patient selectionne</strong>
                                            </div>
                                            <div>
                                                <span>Dossier</span>
                                                <strong id="ordonnance-patient-dossier">--</strong>
                                            </div>
                                            <div>
                                                <span>Contact</span>
                                                <strong id="ordonnance-patient-contact">--</strong>
                                            </div>
                                            <div>
                                                <span>Motif</span>
                                                <strong id="ordonnance-patient-condition">--</strong>
                                            </div>
                                        </div>

                                        <div class="input-group">
                                            <label for="ordonnance-details"><i class="fas fa-notes-medical"></i> Contenu de l ordonnance</label>
                                            <textarea id="ordonnance-details" name="ordonnance_details" rows="5" placeholder="Saisir le contenu de l ordonnance a partir du patient courant..."></textarea>
                                        </div>
                                    </section>

                                    <div class="action-buttons">
                                        <button class="btn btn-primary" id="save-notes-btn" type="submit">
                                            <i class="fas fa-save"></i>
                                            Save Notes
                                        </button>
                                        <button class="btn btn-success" id="complete-visit-btn" type="button">
                                            <i class="fas fa-check-circle"></i>
                                            Complete Visit
                                        </button>
                                        <button class="btn btn-warning" id="reschedule-btn" type="button">
                                            <i class="fas fa-clock"></i>
                                            Reschedule
                                        </button>
                                    </div>
                                </form>
                            </article>
                        </div>

                        <div class="right-column">
                            <article class="queue-card">
                                <div class="queue-header">
                                    <h3><i class="fas fa-list-ol"></i> Patient Queue</h3>
                                    <div class="queue-count" id="queue-count">0 waiting</div>
                                </div>

                                <div class="queue-list" id="patient-queue"></div>
                            </article>

                            <article class="stats-card">
                                <h3><i class="fas fa-chart-column"></i> Today's Statistics</h3>
                                <div class="stat-row">
                                    <span>Total Patients</span>
                                    <strong id="total-patients">0</strong>
                                </div>
                                <div class="stat-row">
                                    <span>Completed</span>
                                    <strong id="completed-patients">0</strong>
                                </div>
                                <div class="stat-row">
                                    <span>Waiting</span>
                                    <strong id="waiting-patients">0</strong>
                                </div>
                                <div class="stat-row">
                                    <span>Average Time</span>
                                    <strong id="avg-time">--</strong>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill" id="progress-fill"></div>
                                </div>
                            </article>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="schedule-view">
                    <div class="calendar-view">
                        <div class="calendar-header">
                            <h2>Schedule &amp; Appointments</h2>
                            <button class="btn btn-primary" id="add-appointment-btn" type="button">
                                <i class="fas fa-plus"></i>
                                Add Appointment
                            </button>
                        </div>

                        <div class="calendar-grid schedule-grid">
                            <div class="calendar-main">
                                <div class="calendar-controls">
                                    <div class="month-nav">
                                        <button class="btn-icon" id="prev-month" type="button" aria-label="Mois precedent">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button class="btn-icon today-chip" id="today-btn" type="button">Today</button>
                                        <button class="btn-icon" id="next-month" type="button" aria-label="Mois suivant">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    <h3 id="current-month">Mois</h3>
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
                                    <div class="calendar-days" id="calendar-days"></div>
                                </div>
                            </div>

                            <div class="appointments-sidebar">
                                <div class="appointments-list">
                                    <h3 id="today-appointments-title">Appointments du jour</h3>
                                    <div id="today-appointments"></div>
                                </div>

                                <div class="appointments-list">
                                    <h3 id="upcoming-appointments-title">Upcoming This Week</h3>
                                    <div id="upcoming-appointments"></div>
                                </div>

                                <div class="appointments-list appointment-form-panel" id="appointment-form-panel" hidden>
                                    <div class="appointments-panel-header">
                                        <h3>Nouveau rendez-vous</h3>
                                        <button class="btn-icon" id="close-appointment-form-btn" type="button" aria-label="Fermer le formulaire">
                                            <i class="fas fa-xmark"></i>
                                        </button>
                                    </div>

                                    <form id="appointment-form" class="stack-form">
                                        <div class="input-group">
                                            <label for="appointment-patient-email"><i class="fas fa-envelope"></i> Recherche par email</label>
                                            <div class="appointment-search-grid">
                                                <input type="email" id="appointment-patient-email" name="patient_email" placeholder="patient@example.com">
                                                <button class="btn btn-secondary" id="search-patient-email-btn" type="button">
                                                    <i class="fas fa-search"></i>
                                                    Rechercher
                                                </button>
                                            </div>
                                        </div>

                                        <div class="patient-lookup-result" id="appointment-patient-result">
                                            Saisissez un email pour retrouver un patient deja inscrit, ou utilisez la liste ci-dessous.
                                        </div>

                                        <div class="input-group">
                                            <label for="appointment-patient"><i class="fas fa-user-injured"></i> Patient</label>
                                            <select id="appointment-patient" name="patient_id" required></select>
                                        </div>

                                        <div class="input-group">
                                            <label for="appointment-date"><i class="fas fa-calendar-days"></i> Date et heure</label>
                                            <input type="datetime-local" id="appointment-date" name="appointment_date" required>
                                        </div>

                                        <div class="input-group">
                                            <label for="appointment-reason"><i class="fas fa-clipboard-list"></i> Motif</label>
                                            <textarea id="appointment-reason" name="reason" rows="4" placeholder="Motif du rendez-vous"></textarea>
                                        </div>

                                        <div class="modal-actions">
                                            <button class="btn btn-secondary" type="button" id="cancel-appointment-form-btn">Annuler</button>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-check"></i>
                                                Creer le rendez-vous
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="patients-view">
                    <div class="patients-view">
                        <div class="patients-header">
                            <h2>Patient Records</h2>
                            <button class="btn btn-primary" id="new-patient-btn" type="button">
                                <i class="fas fa-user-plus"></i>
                                New Patient
                            </button>
                        </div>

                        <div class="search-bar">
                            <input type="text" id="patient-search" placeholder="Search by name, ID or condition...">
                            <button class="btn btn-secondary" id="search-patient-btn" type="button">
                                <i class="fas fa-search"></i>
                                Search
                            </button>
                        </div>

                        <div class="patients-grid">
                            <div class="patients-list">
                                <h3>Patient List</h3>
                                <div id="patient-list"></div>
                            </div>

                            <div class="patient-history">
                                <h3>Medical History</h3>
                                <div class="history-list" id="patient-history-list"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="consultations-view">
                    <div class="patients-view">
                        <div class="patients-header">
                            <h2>Consultations</h2>
                            <button class="btn btn-primary" id="new-consultation-btn" type="button">
                                <i class="fas fa-plus-circle"></i>
                                Start Consultation
                            </button>
                        </div>

                        <div class="consultation-list" id="consultation-list"></div>
                    </div>
                </section>

                <section class="view-container" id="records-view">
                    <div class="patients-view">
                        <div class="patients-header">
                            <h2>Dossiers medicaux</h2>
                            <button class="btn btn-primary" id="export-records-btn" type="button">
                                <i class="fas fa-file-export"></i>
                                Export Records
                            </button>
                        </div>

                        <div class="record-search-grid">
                            <div class="input-group">
                                <label for="records-search-email"><i class="fas fa-envelope"></i> Email patient</label>
                                <input type="email" id="records-search-email" placeholder="patient@example.com">
                            </div>
                            <div class="input-group">
                                <label for="records-search-dossier"><i class="fas fa-folder-open"></i> Numero de dossier</label>
                                <input type="text" id="records-search-dossier" placeholder="PAT-001 ou numDossier">
                            </div>
                            <div class="input-group record-search-actions">
                                <label for="clear-records-search-btn">Actions</label>
                                <button class="btn btn-secondary" id="clear-records-search-btn" type="button">
                                    <i class="fas fa-rotate-left"></i>
                                    Reinitialiser
                                </button>
                            </div>
                        </div>

                        <div class="record-grid" id="record-grid"></div>
                    </div>
                </section>

                <section class="view-container" id="contacts-view">
                    <div class="patients-view">
                        <div class="patients-header">
                            <h2>Contacts</h2>
                            <button class="btn btn-primary" id="new-contact-btn" type="button">
                                <i class="fas fa-rotate-right"></i>
                                Refresh Contacts
                            </button>
                        </div>

                        <div class="contact-grid" id="contact-grid"></div>
                    </div>
                </section>

                <section class="view-container" id="about-view">
                    <div class="patients-view">
                        <div class="patients-header">
                            <h2>About</h2>
                            <button class="btn btn-secondary" id="about-action-btn" type="button">
                                <i class="fas fa-circle-question"></i>
                                Open Public Site
                            </button>
                        </div>

                        <div class="about-panel">
                            <div class="about-hero">
                                <span class="about-badge">Cabinet Medicare</span>
                                <h3>Un dashboard medecin dynamique, branche au backend Laravel et filtre sur le medecin connecte.</h3>
                                <p>
                                    Cette interface reprend le style du dashboard de reference tout en utilisant
                                    les vraies donnees de rendez-vous, de consultations et de dossiers medicaux.
                                </p>
                            </div>

                            <div class="about-columns">
                                <article class="about-card">
                                    <h4><i class="fas fa-layer-group"></i> Donnees reelles</h4>
                                    <p>Les files d attente, statistiques, patients et dossiers affiches proviennent des tables Laravel existantes.</p>
                                </article>
                                <article class="about-card">
                                    <h4><i class="fas fa-syringe"></i> Consultation active</h4>
                                    <p>Les notes mettent a jour le dossier medical, et la cloture d une visite peut programmer un suivi automatiquement.</p>
                                </article>
                                <article class="about-card">
                                    <h4><i class="fas fa-calendar-check"></i> Planning</h4>
                                    <p>Le module planning permet de creer de vrais rendez-vous et de les reprogrammer sans sortir du dashboard.</p>
                                </article>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        window.doctorDashboardConfig = {
            initialView: @json($initialView ?? 'dashboard'),
            registrationUrl: @json(route('register')),
            publicAboutUrl: @json(route('about')),
        };
    </script>
    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
    <script src="{{ asset('frontend/js/doctor-dashboard.js') }}"></script>
</body>
</html>
