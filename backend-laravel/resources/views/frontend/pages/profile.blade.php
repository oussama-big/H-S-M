@extends('frontend.layouts.app')
@section('title', 'Mon Profil')

@push('styles')
<style>
    .profile-shell {
        max-width: 820px;
        margin: 0 auto;
    }

    .profile-card {
        background: var(--bg-card);
        padding: 2rem;
        border-radius: 18px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-md);
    }

    .profile-card h3 {
        font-size: 1.3rem;
        margin-bottom: 1.5rem;
        color: var(--text-primary);
    }

    .profile-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.9rem 0;
        border-bottom: 1px solid var(--border);
    }

    .profile-row:last-child {
        border-bottom: none;
    }

    .profile-label {
        color: var(--text-secondary);
        font-weight: 600;
    }

    .profile-value {
        color: var(--text-primary);
        font-weight: 600;
        text-align: right;
    }

    .profile-actions {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    @media (max-width: 640px) {
        .profile-row {
            flex-direction: column;
        }

        .profile-value {
            text-align: left;
        }
    }
</style>
@endpush

@section('content')
<section class="page-hero-mini">
    <div class="page-hero-mini-content">
        <span class="page-breadcrumb">
            <a href="{{ route('home') }}">Accueil</a>
            <i class="fas fa-chevron-right"></i>
            <a href="{{ route('dashboard') }}">Mon Espace</a>
            <i class="fas fa-chevron-right"></i>
            <span>Profil</span>
        </span>
        <h1 class="page-hero-title">
            <i class="fas fa-user-edit"></i>
            Mon Profil
        </h1>
        <p class="page-hero-subtitle">Consultez les informations de votre compte patient.</p>
    </div>
    <div class="page-hero-bg"></div>
</section>

<section class="section">
    <div class="section-container profile-shell">
        <div class="profile-card" id="profile-card">
            <div class="loading">Chargement de vos informations...</div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (!isAuthenticated()) {
        window.location.href = '/connexion';
        return;
    }

    const authData = getAuthData();
    const user = authData.user || {};
    const fullName = [user.nom, user.prenom].filter(Boolean).join(' ') || user.name || 'Utilisateur';

    document.getElementById('profile-card').innerHTML = `
        <h3>Informations du compte</h3>
        <div class="profile-row">
            <span class="profile-label">Nom complet</span>
            <span class="profile-value">${fullName}</span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Nom</span>
            <span class="profile-value">${user.nom || 'Non renseigne'}</span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Prenom</span>
            <span class="profile-value">${user.prenom || 'Non renseigne'}</span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Email</span>
            <span class="profile-value">${user.email || 'Non renseigne'}</span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Telephone</span>
            <span class="profile-value">${user.telephone || 'Non renseigne'}</span>
        </div>
        <div class="profile-row">
            <span class="profile-label">Role</span>
            <span class="profile-value">${user.role || 'PATIENT'}</span>
        </div>
        <div class="profile-actions">
            <a href="/dashboard" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i>
                Retour au dashboard
            </a>
            <button type="button" class="btn btn-secondary" data-auth-logout>
                <i class="fas fa-sign-out-alt"></i>
                Deconnexion
            </button>
        </div>
    `;

    syncAuthUi();
});
</script>
@endpush
