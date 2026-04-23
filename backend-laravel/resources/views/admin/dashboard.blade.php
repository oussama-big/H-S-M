<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="api-base-url" content="{{ url('/api') }}">
    <title>Dashboard Admin | H-S-M</title>
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
    <link rel="stylesheet" href="{{ asset('frontend/css/admin-dashboard-new.css') }}">
</head>
<body class="admin-dashboard-page" data-theme="light">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="logo">
                <i class="fas fa-hospital"></i>
                <span>Medicare</span>
            </div>

            <nav class="admin-nav" aria-label="Navigation admin">
                <button class="menu-item active" type="button" data-section="dashboard">
                    <i class="fas fa-chart-line"></i>
                    <span>Dashboard</span>
                </button>
                <button class="menu-item" type="button" data-section="patients">
                    <i class="fas fa-user-injured"></i>
                    <span>Patients</span>
                </button>
                <button class="menu-item" type="button" data-section="doctors">
                    <i class="fas fa-user-doctor"></i>
                    <span>Doctors</span>
                </button>
                <button class="menu-item" type="button" data-section="secretaries">
                    <i class="fas fa-user-nurse"></i>
                    <span>Secretaire</span>
                </button>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user-card">
                    <div class="sidebar-user-avatar" id="sidebarUserAvatar">AD</div>
                    <div class="sidebar-user-meta">
                        <strong id="sidebarUserName">Chargement...</strong>
                        <span id="sidebarUserRole">Administrateur</span>
                    </div>
                </div>

                <button class="menu-item sidebar-logout" id="logoutButton" type="button">
                    <i class="fas fa-right-from-bracket"></i>
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div class="header-copy">
                    <h1 id="pageContextTitle">Dashboard Admin</h1>
                    <p id="pageContextDate">Chargement du contexte...</p>
                </div>

                <div class="header-tools">
                    <div class="search-bar">
                        <i class="fas fa-search"></i>
                        <input type="text" id="globalSearch" placeholder="Rechercher patients, medecins, rendez-vous...">
                    </div>
                </div>
            </header>

            <div class="admin-content">
                <div class="notice" id="dashboardNotice"></div>

                <section class="content-section active" data-section="dashboard">
                    <div class="page-title">
                        <i class="fas fa-chart-column"></i>
                        <span>Vue d ensemble administrative</span>
                    </div>

                    <div class="cards-grid">
                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-user-injured"></i></div>
                            <div class="stat-card__value" id="statTotalPatients">--</div>
                            <div class="stat-card__label">Patients inscrits</div>
                            <p class="stat-card__meta">Volume global du registre patient.</p>
                        </article>

                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-user-doctor"></i></div>
                            <div class="stat-card__value" id="statTotalDoctors">--</div>
                            <div class="stat-card__label">Medecins actifs</div>
                            <p class="stat-card__meta">Comptes relies aux consultations et rendez-vous.</p>
                        </article>

                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="stat-card__value" id="statTodayAppointments">--</div>
                            <div class="stat-card__label">Rendez-vous du jour</div>
                            <p class="stat-card__meta">Planning prevu pour aujourd hui.</p>
                        </article>

                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-notes-medical"></i></div>
                            <div class="stat-card__value" id="statTotalConsultations">--</div>
                            <div class="stat-card__label">Consultations</div>
                            <p class="stat-card__meta">Historique total des actes realises.</p>
                        </article>

                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-ban"></i></div>
                            <div class="stat-card__value" id="statCancellationRate">--</div>
                            <div class="stat-card__label">Taux d annulation</div>
                            <p class="stat-card__meta">Indicateur global SAFE des rendez-vous.</p>
                        </article>

                        <article class="stat-card">
                            <div class="stat-card__icon"><i class="fas fa-fire"></i></div>
                            <div class="stat-card__value" id="statCompletionRate">--</div>
                            <div class="stat-card__label">Taux de realisation</div>
                            <p class="stat-card__meta">Performance globale du cabinet.</p>
                        </article>
                    </div>

                    <div class="insights-stack">
                        <article class="panel">
                            <div class="section-title">
                                <i class="fas fa-wave-square"></i>
                                <span>Performance medicale</span>
                            </div>
                            <p class="section-subtitle">Indicateurs generaux relies directement aux donnees backend du cabinet.</p>

                            <div class="mini-stats">
                                <div class="mini-stat">
                                    <span>Medecin le plus actif</span>
                                    <strong id="mostActiveDoctorName">--</strong>
                                    <small id="mostActiveDoctorMeta">Aucune activite</small>
                                </div>
                                <div class="mini-stat">
                                    <span>Rendez-vous annules</span>
                                    <strong id="cancelledAppointmentsCount">0</strong>
                                    <small>Taux global securise</small>
                                </div>
                                <div class="mini-stat">
                                    <span>Rendez-vous realises</span>
                                    <strong id="completedAppointmentsCount">0</strong>
                                    <small>Base du taux de performance</small>
                                </div>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="section-title">
                                <i class="fas fa-user-doctor"></i>
                                <span>Nombre de rendez-vous par medecin</span>
                            </div>
                            <div class="table-container compact">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Medecin</th>
                                            <th>Rendez-vous</th>
                                        </tr>
                                    </thead>
                                    <tbody id="appointmentsByDoctorBody"></tbody>
                                </table>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="section-title">
                                <i class="fas fa-stethoscope"></i>
                                <span>Consultations realisees par medecin</span>
                            </div>
                            <div class="table-container compact">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Medecin</th>
                                            <th>Consultations</th>
                                        </tr>
                                    </thead>
                                    <tbody id="consultationsByDoctorBody"></tbody>
                                </table>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="section-title">
                                <i class="fas fa-users"></i>
                                <span>Patients uniques suivis par medecin</span>
                            </div>
                            <div class="table-container compact">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Medecin</th>
                                            <th>Patients</th>
                                        </tr>
                                    </thead>
                                    <tbody id="patientsFollowedByDoctorBody"></tbody>
                                </table>
                            </div>
                        </article>

                        <article class="panel">
                            <div class="section-title">
                                <i class="fas fa-chart-area"></i>
                                <span>Flux de consultations par jour</span>
                            </div>
                            <div class="progress-stack" id="consultationFlowBars"></div>
                        </article>
                    </div>

                    <div class="panel patient-search">
                        <div class="section-title">
                            <i class="fas fa-magnifying-glass"></i>
                            <span>Recherche patient</span>
                        </div>
                        <div class="search-grid">
                            <div class="form-group">
                                <label for="patientId">Identifiant patient</label>
                                <input type="text" id="patientId" placeholder="PAT-001 ou nom du patient">
                            </div>

                            <div class="form-group form-group-actions">
                                <label for="searchButton">Actions</label>
                                <div class="button-row">
                                    <button class="btn btn-primary" id="searchButton" type="button">
                                        <i class="fas fa-search"></i>
                                        Rechercher
                                    </button>
                                    <button class="btn btn-secondary" id="resetSearchButton" type="button">
                                        <i class="fas fa-rotate-left"></i>
                                        Reinitialiser
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tabs" id="patientTabs">
                        <button class="tab active" type="button" data-filter="all">Tous</button>
                        <button class="tab" type="button" data-filter="outpatients">Externes</button>
                        <button class="tab" type="button" data-filter="inpatients">Internes</button>
                        <button class="tab" type="button" data-filter="appointments">Rendez-vous</button>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Nom</th>
                                    <th>Hopital</th>
                                    <th>Admission</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="dashboardPatientsTableBody"></tbody>
                        </table>
                    </div>

                    <div class="panel patient-details" id="patientDetails">
                        <div class="patient-header">
                            <div>
                                <h2 class="patient-title" id="patientDetailName">Patient</h2>
                                <p class="patient-id" id="patientDetailId">Patient ID: --</p>
                            </div>
                            <button class="btn btn-secondary" id="closeDetails" type="button">
                                <i class="fas fa-xmark"></i>
                                Fermer
                            </button>
                        </div>

                        <div class="patient-grid">
                            <div class="patient-info">
                                <h3>Email</h3>
                                <p id="patientDetailEmail">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Hopital</h3>
                                <p id="patientDetailHospital">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>ID interne</h3>
                                <p id="patientDetailIdNumber">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Telephone</h3>
                                <p id="patientDetailPhone">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Date d admission</h3>
                                <p id="patientDetailAdmissionDate">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Statut</h3>
                                <p id="patientDetailStatus">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Medecin</h3>
                                <p id="patientDetailDoctor">--</p>
                            </div>
                            <div class="patient-info">
                                <h3>Cabinet</h3>
                                <p id="patientDetailWard">--</p>
                            </div>
                        </div>

                        <div class="section-title">
                            <i class="fas fa-file-medical"></i>
                            <span>Informations medicales</span>
                        </div>

                        <div class="search-grid readonly-grid">
                            <div class="form-group">
                                <label for="patientDiagnosis">Diagnostic</label>
                                <input type="text" id="patientDiagnosis" readonly>
                            </div>
                            <div class="form-group">
                                <label for="patientPrescription">Ordonnance</label>
                                <input type="text" id="patientPrescription" readonly>
                            </div>
                            <div class="form-group">
                                <label for="patientTests">Plan de traitement</label>
                                <input type="text" id="patientTests" readonly>
                            </div>
                        </div>

                        <div class="section-title">
                            <i class="fas fa-clock-rotate-left"></i>
                            <span>Timeline des soins</span>
                        </div>

                        <div class="timeline" id="patientTimeline"></div>

                        <div class="button-row button-row-end">
                            <button class="btn btn-primary" id="updateRecordButton" type="button">
                                <i class="fas fa-pen"></i>
                                Mettre a jour
                            </button>
                            <button class="btn btn-secondary" id="resetPatientPasswordButton" type="button">
                                <i class="fas fa-key"></i>
                                Reset password
                            </button>
                            <button class="btn btn-secondary" id="deletePatientButton" type="button">
                                <i class="fas fa-trash"></i>
                                Supprimer
                            </button>
                            <button class="btn btn-secondary" id="closePatientBottomButton" type="button">
                                <i class="fas fa-eye-slash"></i>
                                Hide Details
                            </button>
                        </div>
                    </div>
                </section>

                <section class="content-section" data-section="patients">
                    <div class="page-title">
                        <i class="fas fa-users"></i>
                        <span>Registre des patients</span>
                    </div>

                    <div class="panel form-shell hidden" id="patientFormPanel">
                        <div class="section-title">
                            <i class="fas fa-user-pen"></i>
                            <span id="patientFormTitle">Mise a jour du patient</span>
                        </div>
                        <div id="patientFeedback" class="feedback-box"></div>
                        <form id="patientUpdateForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="patientNom">Nom</label>
                                    <input id="patientNom" name="nom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="patientPrenom">Prenom</label>
                                    <input id="patientPrenom" name="prenom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="patientEmailEdit">Email</label>
                                    <input id="patientEmailEdit" name="email" type="email">
                                </div>
                                <div class="form-group">
                                    <label for="patientTelephoneEdit">Telephone</label>
                                    <input id="patientTelephoneEdit" name="telephone" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="patientBirthEdit">Date de naissance</label>
                                    <input id="patientBirthEdit" name="date_of_birth" type="date">
                                </div>
                                <div class="form-group">
                                    <label for="patientGenderEdit">Genre</label>
                                    <select id="patientGenderEdit" name="gender">
                                        <option value="">Choisir</option>
                                        <option value="M">Homme</option>
                                        <option value="F">Femme</option>
                                        <option value="Autre">Autre</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="patientBloodEdit">Groupe sanguin</label>
                                    <input id="patientBloodEdit" name="blood_type" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="patientEmergencyEdit">Contact urgence</label>
                                    <input id="patientEmergencyEdit" name="emergency_contact" type="text">
                                </div>
                            </div>
                            <div class="button-row button-row-end">
                                <button class="btn btn-secondary" id="closePatientFormBtn" type="button">Annuler</button>
                                <button class="btn btn-primary" id="submitPatientFormBtn" type="submit">
                                    <i class="fas fa-save"></i>
                                    Mettre a jour
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Nom</th>
                                    <th>Hopital</th>
                                    <th>Admission</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="patientsRegistryTableBody"></tbody>
                        </table>
                    </div>
                </section>

                <section class="content-section" data-section="doctors">
                    <div class="page-title">
                        <i class="fas fa-user-doctor"></i>
                        <span>Gestion des medecins</span>
                    </div>

                    <div class="toolbar">
                        <p class="toolbar-note">Ajoutez, modifiez, reinitialisez le mot de passe ou supprimez un medecin depuis cette section.</p>
                        <button class="btn btn-primary" id="openDoctorFormBtn" type="button">
                            <i class="fas fa-plus"></i>
                            Ajouter medecin
                        </button>
                    </div>

                    <div class="panel form-shell hidden" id="doctorFormPanel">
                        <div class="section-title">
                            <i class="fas fa-user-plus"></i>
                            <span id="doctorFormTitle">Creation d un medecin</span>
                        </div>
                        <div id="doctorFeedback" class="feedback-box"></div>
                        <form id="doctorCreateForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="doctorNom">Nom</label>
                                    <input id="doctorNom" name="nom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorPrenom">Prenom</label>
                                    <input id="doctorPrenom" name="prenom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorLogin">Login</label>
                                    <input id="doctorLogin" name="login" type="text" placeholder="Optionnel">
                                </div>
                                <div class="form-group">
                                    <label for="doctorTelephone">Telephone</label>
                                    <input id="doctorTelephone" name="telephone" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="doctorEmail">Email</label>
                                    <input id="doctorEmail" name="email" type="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorSpecialization">Specialisation</label>
                                    <input id="doctorSpecialization" name="specialization" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorLicense">Numero de licence</label>
                                    <input id="doctorLicense" name="license_number" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorCabinet">Cabinet</label>
                                    <select id="doctorCabinet" name="cabinet_id">
                                        <option value="">Sans cabinet</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="doctorPassword">Password</label>
                                    <input id="doctorPassword" name="password" type="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="doctorPasswordConfirmation">Password confirmation</label>
                                    <input id="doctorPasswordConfirmation" name="password_confirmation" type="password" required>
                                </div>
                            </div>
                            <div class="button-row button-row-end">
                                <button class="btn btn-secondary" id="closeDoctorFormBtn" type="button">Annuler</button>
                                <button class="btn btn-primary" id="submitDoctorFormBtn" type="submit">
                                    <i class="fas fa-save"></i>
                                    Creer le medecin
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Login</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
                                    <th>Specialite</th>
                                    <th>Licence</th>
                                    <th>Cabinet</th>
                                    <th>RDV</th>
                                    <th>Consultations</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctorsTableBody"></tbody>
                        </table>
                    </div>
                </section>

                <section class="content-section" data-section="secretaries">
                    <div class="page-title">
                        <i class="fas fa-user-nurse"></i>
                        <span>Gestion des secretaires</span>
                    </div>

                    <div class="toolbar">
                        <p class="toolbar-note">Ajoutez, modifiez, reinitialisez le mot de passe ou supprimez une secretaire depuis cette section.</p>
                        <button class="btn btn-primary" id="openSecretaryFormBtn" type="button">
                            <i class="fas fa-plus"></i>
                            Ajouter secretaire
                        </button>
                    </div>

                    <div class="panel form-shell hidden" id="secretaryFormPanel">
                        <div class="section-title">
                            <i class="fas fa-user-plus"></i>
                            <span id="secretaryFormTitle">Creation d une secretaire</span>
                        </div>
                        <div id="secretaryFeedback" class="feedback-box"></div>
                        <form id="secretaryCreateForm">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="secretaryNom">Nom</label>
                                    <input id="secretaryNom" name="nom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="secretaryPrenom">Prenom</label>
                                    <input id="secretaryPrenom" name="prenom" type="text" required>
                                </div>
                                <div class="form-group">
                                    <label for="secretaryLogin">Login</label>
                                    <input id="secretaryLogin" name="login" type="text" placeholder="Optionnel">
                                </div>
                                <div class="form-group">
                                    <label for="secretaryTelephone">Telephone</label>
                                    <input id="secretaryTelephone" name="telephone" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="secretaryEmail">Email</label>
                                    <input id="secretaryEmail" name="email" type="email" required>
                                </div>
                                <div class="form-group">
                                    <label for="secretaryOffice">Numero de bureau</label>
                                    <input id="secretaryOffice" name="office_number" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="secretaryAssignment">Affectation</label>
                                    <input id="secretaryAssignment" name="assignment" type="text">
                                </div>
                                <div class="form-group">
                                    <label for="secretaryPassword">Password</label>
                                    <input id="secretaryPassword" name="password" type="password" required>
                                </div>
                                <div class="form-group">
                                    <label for="secretaryPasswordConfirmation">Password confirmation</label>
                                    <input id="secretaryPasswordConfirmation" name="password_confirmation" type="password" required>
                                </div>
                            </div>
                            <div class="button-row button-row-end">
                                <button class="btn btn-secondary" id="closeSecretaryFormBtn" type="button">Annuler</button>
                                <button class="btn btn-primary" id="submitSecretaryFormBtn" type="submit">
                                    <i class="fas fa-save"></i>
                                    Creer la secretaire
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Login</th>
                                    <th>Email</th>
                                    <th>Telephone</th>
                                    <th>Bureau</th>
                                    <th>Affectation</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="secretariesTableBody"></tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>
    </div>

    <script src="{{ asset('frontend/js/auth-api.js') }}"></script>
    <script src="{{ asset('frontend/js/admin-dashboard-new.js') }}"></script>
</body>
</html>
