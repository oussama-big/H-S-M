@extends('frontend.layouts.app')
@section('title', 'Tableau de Bord - MediCare')
@section('content')

<style>
:root {
    --primary:        #38bdf8;
    --secondary:      #0ea5e9;
    --accent:         #bae6fd;
    --light-accent:   #7dd3fc;

    --bg-light:       #f7fbff;
    --bg-card:        #ffffff;
    --border-light:   #d8e7f5;

    --text-primary:   #0f172a;
    --text-secondary: #475569;
    --text-muted:     #94a3b8;

    --gradient-primary: linear-gradient(135deg, #38bdf8 0%, #0ea5e9 50%, #bae6fd 100%);
    --shadow-sm:      0 2px 4px rgba(14, 165, 233, 0.06);
    --shadow-md:      0 10px 30px rgba(14, 165, 233, 0.1);
    --shadow-lg:      0 16px 42px rgba(14, 165, 233, 0.12);
}

.dashboard-wrapper {
    min-height: 100vh;
    background-color: var(--bg-light);
    padding: 40px 20px;
}

.dashboard-container {
    max-width: 1200px;
    margin: 0 auto;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding: 20px;
    background: var(--bg-card);
    border-radius: 15px;
    box-shadow: var(--shadow-md);
}

.navbar-brand {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

.navbar-menu {
    display: flex;
    gap: 20px;
}

.navbar-link {
    color: var(--text-secondary);
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
}

.navbar-link:hover {
    color: var(--primary);
}

.navbar-button {
    padding: 10px 20px;
    background: var(--gradient-primary);
    color: white;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 700;
    transition: all 0.3s ease;
}

.navbar-button:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.welcome-section {
    margin-bottom: 40px;
    padding: 40px;
    background: var(--gradient-primary);
    border-radius: 20px;
    color: white;
    box-shadow: var(--shadow-lg);
    animation: slideUp 0.6s ease;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.welcome-title {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 10px;
}

.welcome-subtitle {
    font-size: 1.1rem;
    opacity: 0.95;
    margin-bottom: 5px;
}

.welcome-date {
    font-size: 0.95rem;
    opacity: 0.85;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.card {
    background: var(--bg-card);
    padding: 25px;
    border-radius: 15px;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 1px solid var(--border-light);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.card-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.card-title {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.card-value {
    font-size: 2rem;
    font-weight: 700;
    color: var(--primary);
    margin-bottom: 10px;
}

.card-text {
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.5;
}

.card-button {
    display: inline-block;
    margin-top: 15px;
    padding: 10px 20px;
    background: var(--primary);
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.3s ease;
}

.card-button:hover {
    background: #3d7cc4;
}

.loading {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 20px;
    color: var(--text-secondary);
}

.loading::before {
    content: '';
    width: 20px;
    height: 20px;
    border: 3px solid var(--border-light);
    border-top: 3px solid var(--primary);
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.profile-info {
    background: var(--bg-card);
    padding: 25px;
    border-radius: 15px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-light);
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-light);
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: var(--text-secondary);
}

.info-value {
    color: var(--text-primary);
    font-weight: 500;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: var(--bg-card);
    border-radius: 15px;
    border: 2px dashed var(--border-light);
}

.empty-state-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state-text {
    color: var(--text-secondary);
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .navbar {
        flex-direction: column;
        gap: 15px;
    }

    .navbar-menu {
        flex-direction: column;
        width: 100%;
    }

    .navbar-link,
    .navbar-button {
        width: 100%;
        text-align: center;
    }

    .welcome-title {
        font-size: 1.8rem;
    }

    .dashboard-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<div class="dashboard-wrapper">
    <div class="dashboard-container">
        <!-- Navbar -->
        <div class="navbar">
            <div class="navbar-brand">
                <i class="fas fa-heartbeat"></i>
                MediCare
            </div>
            <div class="navbar-menu">
                <a href="#" class="navbar-link">Mes Rendez-vous</a>
                <a href="/profil" class="navbar-link">Profil</a>
                <button onclick="logout()" class="navbar-button">Se déconnecter</button>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="welcome-section" id="welcome-section">
            <h1 class="welcome-title" id="welcome-title"></h1>
            <p class="welcome-subtitle">Bienvenue sur votre espace patient MediCare</p>
            <p class="welcome-date" id="welcome-date"></p>
        </div>

        <!-- Dashboard Grid -->
        <div class="dashboard-grid">
            <!-- Profil Card -->
            <div class="card">
                <div class="card-icon">👤</div>
                <h3 class="card-title">Mon Profil</h3>
                <p class="card-text">Consultez et modifiez vos informations personnelles</p>
                <a href="/profil" class="card-button">Voir mon profil →</a>
            </div>

            <!-- Appointments Card -->
            <div class="card">
                <div class="card-icon">📅</div>
                <h3 class="card-title">Rendez-vous</h3>
                <p class="card-text" id="appointments-text">Chargement de vos rendez-vous...</p>
                <div id="appointments-status"></div>
            </div>

            <!-- Medical Record Card -->
            <div class="card">
                <div class="card-icon">📋</div>
                <h3 class="card-title">Dossier Médical</h3>
                <p class="card-text">Accédez à votre historique médical et consultations</p>
                <a href="#" class="card-button">Consulter →</a>
            </div>
        </div>

        <!-- Profile Info Section -->
        <div class="profile-info" id="profile-info">
            <div class="loading">
                Chargement de vos informations...
            </div>
        </div>
    </div>
</div>

<script>
// Format date
function formatDate(date) {
    const options = { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' };
    return new Date(date).toLocaleDateString('fr-FR', options);
}

// Initialize Dashboard
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const authData = getAuthData();
        let user = authData.user;

        if (!user) {
            user = await getCurrentUser();
        }

        if (!user) {
            console.error('User data not found');
            window.location.href = '/connexion';
            return;
        }

        if (user.role && user.role !== 'PATIENT') {
            window.location.replace(resolveDashboardPath(user));
            return;
        }

        // Set welcome message
        const firstName = user.prenom || user.name || 'Utilisateur';
        const lastName = user.nom || '';
        const fullName = lastName ? `${lastName} ${firstName}` : firstName;
        
        document.getElementById('welcome-title').textContent = `Bonjour, ${fullName} 👋`;
        document.getElementById('welcome-date').textContent = `Aujourd'hui, ${formatDate(new Date())}`;

        // Display profile info
        displayProfileInfo(user);

        // Try to fetch appointments if API is available
        try {
            // This would require the API endpoint
            // fetchAppointments(user.id);
        } catch (error) {
            console.log('Appointments feature not available:', error);
        }
    } catch (error) {
        console.error('Dashboard initialization error:', error);
    }
});

function displayProfileInfo(user) {
    const profileHtml = `
        <h3 class="card-title" style="margin-bottom: 20px;">Vos Informations</h3>
        <div class="info-row">
            <span class="info-label">Nom:</span>
            <span class="info-value">${user.nom || 'N/A'}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Prénom:</span>
            <span class="info-value">${user.prenom || 'N/A'}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span class="info-value">${user.email || 'N/A'}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Téléphone:</span>
            <span class="info-value">${user.telephone || 'Non renseigné'}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Rôle:</span>
            <span class="info-value">${user.role || 'Patient'}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Inscrit depuis:</span>
            <span class="info-value">${formatDate(user.created_at || new Date())}</span>
        </div>
    `;
    document.getElementById('profile-info').innerHTML = profileHtml;
}

async function fetchAppointments(userId) {
    try {
        const data = await apiCall(`/patients/${userId}/appointments`);
        const statusDiv = document.getElementById('appointments-status');
        
        if (data.data && data.data.length > 0) {
            statusDiv.innerHTML = `<a href="#" class="card-button">Voir mes ${data.data.length} rendez-vous →</a>`;
        } else {
            statusDiv.innerHTML = '<p style="color: var(--text-secondary); margin-top: 10px;">Aucun rendez-vous prévu</p>';
        }
    } catch (error) {
        console.log('Could not fetch appointments:', error);
        document.getElementById('appointments-text').textContent = 'Rendez-vous à venir';
    }
}
</script>

@endsection

