/**
 * MediCare - Authentication API Integration
 * Single-server Laravel frontend + API helper.
 */

const API_BASE_URL =
    document.querySelector('meta[name="api-base-url"]')?.getAttribute('content') ||
    `${window.location.origin}/api`;

function saveAuthData(token, user) {
    localStorage.setItem('auth_token', token);
    localStorage.setItem('user', JSON.stringify(user));
    localStorage.setItem('auth_timestamp', String(Date.now()));
    syncAuthUi();
}

function clearAuthData() {
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    localStorage.removeItem('auth_timestamp');
    syncAuthUi();
}

function getAuthData() {
    return {
        token: localStorage.getItem('auth_token'),
        user: localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')) : null,
    };
}

function isAuthenticated() {
    return Boolean(localStorage.getItem('auth_token'));
}

function resolveDashboardPath(user) {
    const role = user?.role || '';

    switch (role) {
        case 'ADMIN':
            return '/admin/dashboard';
        case 'SECRETAIRE':
            return '/secretary/dashboard';
        case 'MEDECIN':
            return '/doctor/dashboard';
        case 'PATIENT':
            return '/patient/dashboard';
        default:
            return '/dashboard';
    }
}

function firstValidationMessage(errors) {
    if (!errors || typeof errors !== 'object') {
        return null;
    }

    const firstError = Object.values(errors)[0];

    if (Array.isArray(firstError)) {
        return firstError[0];
    }

    return typeof firstError === 'string' ? firstError : null;
}

async function parseApiResponse(response) {
    const contentType = response.headers.get('content-type') || '';
    let data = {};

    if (contentType.includes('application/json')) {
        data = await response.json();
    } else {
        const text = await response.text();
        data = text ? { message: text } : {};
    }

    if (!response.ok) {
        const error = new Error(
            firstValidationMessage(data.errors) ||
            data.message ||
            'Erreur lors de la requete'
        );
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

async function apiCall(endpoint, method = 'GET', body = null) {
    const token = localStorage.getItem('auth_token');
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    };

    if (token) {
        headers.Authorization = `Bearer ${token}`;
    }

    const response = await fetch(`${API_BASE_URL}${endpoint}`, {
        method,
        headers,
        body: body ? JSON.stringify(body) : null,
    });

    if (response.status === 401) {
        clearAuthData();
    }

    return parseApiResponse(response);
}

async function login(email, password) {
    const data = await apiCall('/login', 'POST', { email, password });
    const payload = data.data || data;

    if (!payload.access_token || !payload.user) {
        throw new Error('Reponse invalide du serveur');
    }

    saveAuthData(payload.access_token, payload.user);

    return payload;
}

async function registerPatient(formData) {
    const data = await apiCall('/patients/register', 'POST', formData);
    const payload = data.data || data;
    const patient = payload.patient || null;
    const user = patient?.user
        ? {
            ...patient.user,
            patient_id: patient.id,
            dossier_medical_id: patient.dossierMedical?.id || patient.dossier_medical?.id || null,
        }
        : null;

    if (!payload.access_token || !user) {
        throw new Error('Reponse invalide du serveur');
    }

    saveAuthData(payload.access_token, user);

    return payload;
}

async function logout() {
    const token = localStorage.getItem('auth_token');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    try {
        if (token) {
            await fetch(`${API_BASE_URL}/logout`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${token}`,
                },
            });
        }

        if (csrfToken) {
            await fetch('/logout', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            });
        }
    } catch (error) {
        console.warn('Logout API warning:', error);
    } finally {
        clearAuthData();
        window.location.href = '/connexion';
    }
}

async function getCurrentUser() {
    try {
        return await apiCall('/user');
    } catch (error) {
        console.error('Get current user error:', error);
        return null;
    }
}

function syncAuthUi() {
    const { user, token } = getAuthData();
    const isLoggedIn = Boolean(user && token);
    const isPatient = isLoggedIn && user?.role === 'PATIENT';
    const authMenu = document.querySelector('.js-authenticated');
    const guestLinks = document.querySelector('.js-guest-links');
    const userName = document.getElementById('frontendAuthUserName');

    if (authMenu) {
        authMenu.hidden = !isLoggedIn;
    }

    if (guestLinks) {
        guestLinks.hidden = isLoggedIn;
    }

    if (userName && isLoggedIn) {
        userName.textContent =
            [user.nom, user.prenom].filter(Boolean).join(' ') ||
            user.name ||
            user.email ||
            'Mon compte';
    }

    document.querySelectorAll('.js-guest-only').forEach((element) => {
        element.hidden = isLoggedIn;
    });

    document.querySelectorAll('.js-auth-only').forEach((element) => {
        element.hidden = !isLoggedIn;
    });

    document.querySelectorAll('.js-patient-only').forEach((element) => {
        element.hidden = !isPatient;
    });

    document.querySelectorAll('.js-non-patient-auth-only').forEach((element) => {
        element.hidden = !isLoggedIn || isPatient;
    });

    document.querySelectorAll('[data-dashboard-link]').forEach((link) => {
        link.setAttribute('href', isLoggedIn ? resolveDashboardPath(user) : '/connexion');
    });

    document.querySelectorAll('[data-appointment-access]').forEach((link) => {
        if (!isLoggedIn) {
            link.setAttribute('href', '/connexion');
            return;
        }

        link.setAttribute('href', isPatient ? '/patient/rendez-vous' : resolveDashboardPath(user));
    });

    document.querySelectorAll('[data-auth-logout]').forEach((button) => {
        if (button.dataset.authBound === 'true') {
            return;
        }

        button.dataset.authBound = 'true';
        button.addEventListener('click', logout);
    });
}

window.addEventListener('DOMContentLoaded', syncAuthUi);
window.addEventListener('storage', syncAuthUi);

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        apiCall,
        clearAuthData,
        getAuthData,
        getCurrentUser,
        isAuthenticated,
        login,
        logout,
        registerPatient,
        resolveDashboardPath,
        saveAuthData,
        syncAuthUi,
    };
}
