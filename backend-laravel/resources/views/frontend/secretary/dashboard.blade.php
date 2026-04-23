<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>Dashboard Secretaire | Medicare</title>
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
    <link rel="stylesheet" href="{{ asset('frontend/css/secretary-dashboard.css') }}">
</head>
<body class="doctor-dashboard-page secretary-dashboard-page" data-theme="light">
    <div class="app-container">
        <button class="hamburger-btn" id="hamburger-btn" type="button" aria-label="Ouvrir la navigation">
            <i class="fas fa-bars"></i>
        </button>

        <div class="overlay" id="overlay"></div>

        <div class="notification" id="notification">
            <i class="fas fa-circle-check"></i>
            <span id="notification-text">Dashboard secretaire charge.</span>
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
                    <button class="nav-item" data-view="patients" type="button">
                        <i class="fas fa-user-plus"></i>
                        <span>Patients</span>
                    </button>
                    <button class="nav-item" data-view="appointments" type="button">
                        <i class="fas fa-calendar-check"></i>
                        <span>Rendez-vous</span>
                    </button>
                    <button class="nav-item" data-view="queues" type="button">
                        <i class="fas fa-list-ol"></i>
                        <span>Patient Queue</span>
                    </button>
                </nav>

                <div class="sidebar-footer">
                    <div class="doctor-profile">
                        <div class="doctor-avatar" id="sidebar-secretary-avatar">SC</div>
                        <div class="doctor-info">
                            <h3 id="sidebar-secretary-name">Chargement...</h3>
                            <p id="sidebar-secretary-meta">Secretaire</p>
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
                        <button class="btn btn-secondary" id="open-patient-view-btn" type="button">
                            <i class="fas fa-user-plus"></i>
                            <span>Ajouter patient</span>
                        </button>
                        <button class="btn btn-primary" id="open-appointment-view-btn" type="button">
                            <i class="fas fa-calendar-plus"></i>
                            <span>Creer rendez-vous</span>
                        </button>
                    </div>
                </header>

                <section class="queue-toolbar" id="secretary-queue-toolbar">
                    <div class="queue-toolbar__inner">
                        <div class="input-group queue-filter-group">
                            <label for="secretary-queue-doctor-select"><i class="fas fa-user-doctor"></i> Patient Queue par medecin</label>
                            <div class="queue-toolbar__controls">
                                <select id="secretary-queue-doctor-select">
                                    <option value="">Tous les medecins actifs</option>
                                </select>
                                <button class="btn btn-primary" id="secretary-queue-apply-btn" type="button">
                                    <i class="fas fa-list-check"></i>
                                    <span>Afficher la file</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container active" id="dashboard-view">
                    <div class="patients-view secretary-shell">
                        <div class="secretary-stat-grid">
                            <article class="patient-stat-card">
                                <span>Rendez-vous du jour</span>
                                <strong id="secretary-stat-total">0</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>En attente</span>
                                <strong id="secretary-stat-waiting">0</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>Completes</span>
                                <strong id="secretary-stat-completed">0</strong>
                            </article>
                            <article class="patient-stat-card">
                                <span>Medecins actifs</span>
                                <strong id="secretary-stat-doctors">0</strong>
                            </article>
                        </div>

                        <div class="secretary-notification-feed" id="secretary-notification-feed"></div>

                        <div class="secretary-doctor-board" id="secretary-dashboard-panels"></div>
                    </div>
                </section>

                <section class="view-container" id="patients-view">
                    <div class="patients-view secretary-shell">
                        <div class="patients-header">
                            <h2>Ajouter un patient</h2>
                        </div>

                        <div class="patients-grid">
                            <div class="patients-list secretary-form-card">
                                <form id="secretary-patient-form" class="stack-form">
                                    <div class="input-group">
                                        <label for="secretary-patient-nom"><i class="fas fa-user"></i> Nom</label>
                                        <input type="text" id="secretary-patient-nom" name="nom" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-prenom"><i class="fas fa-user"></i> Prenom</label>
                                        <input type="text" id="secretary-patient-prenom" name="prenom" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-email"><i class="fas fa-envelope"></i> Email</label>
                                        <input type="email" id="secretary-patient-email" name="email" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-telephone"><i class="fas fa-phone"></i> Telephone</label>
                                        <input type="text" id="secretary-patient-telephone" name="telephone" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-birth"><i class="fas fa-cake-candles"></i> Date de naissance</label>
                                        <input type="date" id="secretary-patient-birth" name="date_of_birth" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-gender"><i class="fas fa-venus-mars"></i> Genre</label>
                                        <select id="secretary-patient-gender" name="gender" required>
                                            <option value="">Choisir</option>
                                            <option value="M">Homme</option>
                                            <option value="F">Femme</option>
                                            <option value="Autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-blood"><i class="fas fa-droplet"></i> Groupe sanguin</label>
                                        <input type="text" id="secretary-patient-blood" name="blood_type">
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-emergency"><i class="fas fa-phone-volume"></i> Contact urgence</label>
                                        <input type="text" id="secretary-patient-emergency" name="emergency_contact">
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-password"><i class="fas fa-lock"></i> Mot de passe</label>
                                        <input type="password" id="secretary-patient-password" name="password" required>
                                    </div>
                                    <div class="input-group">
                                        <label for="secretary-patient-password-confirmation"><i class="fas fa-lock"></i> Confirmation</label>
                                        <input type="password" id="secretary-patient-password-confirmation" name="password_confirmation" required>
                                    </div>
                                    <div class="modal-actions">
                                        <button class="btn btn-secondary" id="secretary-patient-reset-btn" type="button">Reinitialiser</button>
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fas fa-check"></i>
                                            Enregistrer
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <div class="patient-history">
                                <h3>Patients enregistres</h3>
                                <div class="history-list" id="secretary-patient-list"></div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="appointments-view">
                    <div class="calendar-view secretary-shell">
                        <div class="calendar-header">
                            <h2>Creer un rendez-vous</h2>
                        </div>

                        <div class="calendar-grid schedule-grid">
                            <div class="calendar-main">
                                <div class="secretary-form-card">
                                    <form id="secretary-appointment-form" class="stack-form">
                                        <div class="input-group">
                                            <label for="secretary-appointment-patient"><i class="fas fa-user-injured"></i> Patient</label>
                                            <select id="secretary-appointment-patient" name="patient_id" required></select>
                                        </div>
                                        <div class="input-group">
                                            <label for="secretary-appointment-doctor"><i class="fas fa-user-doctor"></i> Medecin</label>
                                            <select id="secretary-appointment-doctor" name="doctor_id" required></select>
                                        </div>
                                        <div class="input-group">
                                            <label for="secretary-appointment-date"><i class="fas fa-calendar-days"></i> Date</label>
                                            <input type="date" id="secretary-appointment-date" name="appointment_date" required>
                                        </div>
                                        <div class="input-group">
                                            <label><i class="fas fa-clock"></i> Creneaux disponibles</label>
                                            <div class="slot-grid" id="secretary-available-slots"></div>
                                        </div>
                                        <div class="input-group">
                                            <label for="secretary-appointment-reason"><i class="fas fa-clipboard-list"></i> Motif</label>
                                            <textarea id="secretary-appointment-reason" name="reason" rows="4"></textarea>
                                        </div>
                                        <div class="modal-actions">
                                            <button class="btn btn-secondary" id="secretary-appointment-reset-btn" type="button">Reinitialiser</button>
                                            <button class="btn btn-primary" type="submit">
                                                <i class="fas fa-check"></i>
                                                Valider
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="appointments-sidebar">
                                <div class="appointments-list">
                                    <h3>Rendez-vous a venir</h3>
                                    <div id="secretary-upcoming-appointments"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="view-container" id="queues-view">
                    <div class="patients-view secretary-shell">
                        <div class="patients-header">
                            <h2>Current Consultation &amp; Patient Queue</h2>
                        </div>
                        <div class="secretary-doctor-board" id="secretary-queue-panels"></div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <script>
        window.secretaryDashboardConfig = {
            initialView: @json($initialView ?? 'dashboard'),
        };
    </script>
    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
    <script src="{{ asset('frontend/js/secretary-dashboard.js') }}"></script>
</body>
</html>
