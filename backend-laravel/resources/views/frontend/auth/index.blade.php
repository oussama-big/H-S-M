@extends('frontend.layouts.auth-neumorphism')
@section('title', 'Authentification - MediCare')
@section('content')

<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

<style>
*, *::after, *::before {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  user-select: none;
}

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

body {
    width: 100%;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Montserrat', 'Plus Jakarta Sans', sans-serif;
    font-size: 12px;
    background-color: var(--bg-light);
    color: var(--text-secondary);
    overflow-x: hidden;
}

.auth-wrapper {
    position: relative;
    width: 100%;
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

.btn-back {
    position: fixed;
    top: 20px;
    left: 20px;
    z-index: 1001;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: var(--bg-card);
    border: 1.5px solid var(--border-light);
    border-radius: 10px;
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    box-shadow: var(--shadow-sm);
}

.btn-back:hover {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
    transform: translateX(-2px);
    box-shadow: var(--shadow-md);
}

.auth-container {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
}

.main {
    position: relative;
    width: 1000px;
    min-width: 1000px;
    min-height: 650px;
    padding: 25px;
    background-color: var(--bg-light);
    box-shadow: 0 16px 40px rgba(26, 124, 135, 0.1);
    border-radius: 20px;
    overflow: visible;
}

@media (max-width: 1200px) {
    .main { transform: scale(0.9); }
}

@media (max-width: 1000px) {
    .main { transform: scale(0.8); }
}

@media (max-width: 800px) {
    .main { transform: scale(0.7); }
}

@media (max-width: 600px) {
    .main { transform: scale(0.6); }
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
    overflow-y: visible;
    flex-direction: column;
    position: absolute;
    top: 0;
    width: 600px;
    height: 100%;
    padding: 25px;
    background-color: var(--bg-light);
    transition: 1.25s;
    box-sizing: border-box;
}

.form {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    width: 100%;
}

.form__input {
    width: 350px;
    height: 40px;
    margin: 4px 0;
    padding-left: 25px;
    font-size: 13px;
    letter-spacing: 0.15px;
    border: 1.5px solid var(--border-light);
    outline: none;
    font-family: 'Montserrat', sans-serif;
    background-color: var(--bg-card);
    transition: all 0.3s ease;
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    color: var(--text-primary);
}

.form__input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(26, 124, 135, 0.08), var(--shadow-md);
}

.form__input::placeholder {
    color: var(--text-muted);
}

.form__select {
    width: 350px;
    height: 40px;
    margin: 4px 0;
    padding-left: 15px;
    font-size: 13px;
    border: 1.5px solid var(--border-light);
    outline: none;
    font-family: 'Montserrat', sans-serif;
    background-color: var(--bg-card);
    transition: all 0.3s ease;
    border-radius: 10px;
    box-shadow: var(--shadow-sm);
    color: var(--text-primary);
}

.form__select:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(26, 124, 135, 0.08), var(--shadow-md);
}

.form__span {
    margin-top: 20px;
    margin-bottom: 12px;
    font-size: 12px;
    color: var(--text-muted);
}

.form__link {
    color: var(--primary);
    font-size: 12px;
    margin-top: 15px;
    text-decoration: none;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
}

.form__link:hover {
    color: var(--secondary);
    text-decoration: underline;
}

.form_title {
    font-size: 28px;
    font-weight: 700;
    line-height: 2;
    color: var(--text-primary);
}

.title {
    font-size: 32px;
    font-weight: 700;
    color: var(--text-primary);
}

.description {
    font-size: 13px;
    letter-spacing: 0.25px;
    text-align: center;
    line-height: 1.6;
    color: var(--text-secondary);
}

.button {
    width: 180px;
    height: 50px;
    border-radius: 25px;
    margin-top: 15px;
    margin-bottom: 15px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.8px;
    background: var(--gradient-primary);
    color: white;
    box-shadow: var(--shadow-md);
    border: none;
    outline: none;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.button:hover {
    box-shadow: var(--shadow-lg);
    transform: translateY(-2px);
}

.button:active {
    transform: translateY(0);
}

.button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.error-box {
    width: 350px;
    font-size: 11px;
    color: #c7254e;
    margin-bottom: 10px;
    padding: 10px;
    background: #f2dede;
    border-radius: 8px;
    border-left: 4px solid #a94442;
    text-align: left;
    max-height: 80px;
    overflow-y: auto;
}

.success-box {
    width: 350px;
    font-size: 11px;
    color: #155724;
    margin-bottom: 10px;
    padding: 10px;
    background: #d4edda;
    border-radius: 8px;
    border-left: 4px solid #28a745;
    text-align: left;
}

.loading {
    display: none;
    width: 20px;
    height: 20px;
    border: 3px solid rgba(255, 255, 255, 0.3);
    border-top: 3px solid white;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-right: 8px;
}

.button.is-loading {
    display: flex;
    align-items: center;
    justify-content: center;
}

.button.is-loading .loading {
    display: block;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.a-container {
    z-index: 100;
    left: calc(100% - 600px);
}

.b-container {
    left: calc(100% - 600px);
    z-index: 0;
}

.switch {
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: 0;
    left: 0;
    height: 100%;
    width: 400px;
    padding: 50px;
    z-index: 200;
    transition: 1.25s;
    background: var(--gradient-primary);
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.switch__circle {
    position: absolute;
    width: 500px;
    height: 500px;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.1);
    box-shadow: inset 8px 8px 12px rgba(255, 255, 255, 0.2), inset -8px -8px 12px rgba(0, 0, 0, 0.08);
    bottom: -60%;
    left: -60%;
    transition: 1.25s;
}

.switch__circle--t {
    top: -30%;
    left: 60%;
    width: 300px;
    height: 300px;
}

.switch__container {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    position: absolute;
    width: 400px;
    padding: 50px 55px;
    transition: 1.25s;
    opacity: 1;
    visibility: visible;
    color: white;
}

.switch__container.is-hidden {
    opacity: 0;
    visibility: hidden;
}

.switch__title {
    font-size: 32px;
    font-weight: 700;
    color: white;
    margin-bottom: 12px;
}

.switch__description {
    font-size: 13px;
    color: rgba(255, 255, 255, 0.9);
    text-align: center;
    line-height: 1.6;
    margin-bottom: 20px;
}

.switch__button {
    width: 180px;
    height: 50px;
    border-radius: 25px;
    font-weight: 700;
    font-size: 14px;
    letter-spacing: 0.8px;
    background-color: #1f2d3d;
    color: white;
    box-shadow: 0 6px 20px rgba(255, 255, 255, 0.25);
    border: 2px solid white;
    outline: none;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    transition: all 0.3s ease;
}

.switch__button:hover {
    background-color: white;
    color: #1f2d3d;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.2);
    transform: translateY(-2px);
}

.is-txr {
    left: calc(100% - 400px) !important;
    transition: 1.25s;
}

.is-txl {
    left: 0 !important;
    transition: 1.25s;
}

.is-z200 {
    z-index: 200;
    transition: 1.25s;
}

.is-gx {
    animation: is-gx 1.25s;
}

@keyframes is-gx {
    0%, 10%, 100% { width: 400px; }
    30%, 50% { width: 500px; }
}

@media (max-width: 768px) {
    .btn-back {
        top: 10px;
        left: 10px;
        padding: 8px 12px;
        font-size: 0.75rem;
    }
}
</style>

<div class="auth-wrapper">
    <a href="{{ url('/') }}" class="btn-back">
        <i class="fas fa-arrow-left"></i>
        Retour
    </a>
    
    <div class="auth-container">
        <div class="main" id="main">
            <!-- INSCRIPTION FORM (Gauche) -->
            <div class="container a-container" id="a-container">
                <form class="form" id="register-form">
                    <h2 class="form_title">S'inscrire</h2>
                    <span class="form__span">Créer votre compte patient MediCare</span>

                    <div id="register-messages"></div>

                    <input type="text" name="nom" class="form__input" placeholder="Nom">
                    <input type="text" name="prenom" class="form__input" placeholder="Prénom">
                    <input type="email" name="email" class="form__input" placeholder="Email">
                    <input type="date" name="date_of_birth" class="form__input" placeholder="Date de naissance">
                    
                    <select name="gender" class="form__select">
                        <option value="">Sexe</option>
                        <option value="M">Homme</option>
                        <option value="F">Femme</option>
                        <option value="Autre">Autre</option>
                    </select>

                    <input type="tel" name="telephone" class="form__input" placeholder="Téléphone">
                    <input type="password" name="password" class="form__input" placeholder="Mot de passe">
                    <input type="password" name="password_confirmation" class="form__input" placeholder="Confirmer mot de passe">

                    <button type="submit" class="button" id="register-btn">
                        <span class="loading"></span>
                        S'INSCRIRE
                    </button>
                </form>
            </div>

            <!-- CONNEXION FORM (Droite) -->
            <div class="container b-container" id="b-container">
                <form class="form" id="login-form">
                    <h2 class="form_title">Se connecter</h2>
                    <span class="form__span">Accédez à votre portail médical</span>

                    <div id="login-messages"></div>

                    <input type="email" name="email" class="form__input" placeholder="Email" autofocus>
                    <input type="password" name="password" class="form__input" placeholder="Mot de passe">

                    <a href="#" class="form__link">Mot de passe oublié?</a>

                    <button type="submit" class="button" id="login-btn">
                        <span class="loading"></span>
                        SE CONNECTER
                    </button>
                </form>
            </div>

            <!-- SWITCH PANEL (Animation) -->
            <div class="switch" id="switch-cnt">
                <div class="switch__circle"></div>
                <div class="switch__circle switch__circle--t"></div>

                <!-- Panneau Connexion (initialement visible) -->
                <div class="switch__container" id="switch-c1">
                    <h2 class="switch__title">Bienvenue!</h2>
                    <p class="switch__description">Vous n'avez pas encore de compte MediCare? Créez-en un dès maintenant pour accéder à vos dossiers médicaux.</p>
                    <button type="button" class="switch__button button switch-btn" data-auth-target="login">SE CONNECTER</button>
                </div>

                <!-- Panneau Inscription (initialement caché) -->
                <div class="switch__container is-hidden" id="switch-c2">
                    <h2 class="switch__title">Bon retour!</h2>
                    <p class="switch__description">Vous avez déjà un compte? Connectez-vous pour consulter vos rendez-vous et résultats d'analyses.</p>
                    <button type="button" class="switch__button button switch-btn" data-auth-target="register">S'INSCRIRE</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let switchCtn = document.querySelector("#switch-cnt");
let switchC1 = document.querySelector("#switch-c1");
let switchC2 = document.querySelector("#switch-c2");
let switchCircle = document.querySelectorAll(".switch__circle");
let switchBtn = document.querySelectorAll(".switch-btn");
let aContainer = document.querySelector("#a-container");
let bContainer = document.querySelector("#b-container");

let changeForm = (e) => {
    e.preventDefault();

    const targetMode = e.currentTarget?.dataset?.authTarget === "register"
        ? "register"
        : "login";

    if (typeof window.__authAnimateRedirect === "function") {
        window.__authAnimateRedirect(targetMode);
        return;
    }

    const targetUrl = targetMode === "register"
        ? "{{ url('/inscription') }}"
        : "{{ url('/connexion') }}";

    if (window.location.pathname !== new URL(targetUrl, window.location.origin).pathname) {
        window.location.href = targetUrl;
    }
}

let mainF = (e) => {
    for (var i = 0; i < switchBtn.length; i++)
        switchBtn[i].addEventListener("click", changeForm);
}

// Helper function to translate error messages
function translateErrorMessage(errorMsg) {
    const errorTranslations = {
        'NOT NULL constraint failed: patients.date_of_birth': 'Le champ date de naissance est obligatoire',
        'NOT NULL constraint failed: patients.gender': 'Veuillez sélectionner votre sexe',
        'NOT NULL constraint failed': 'Un champ obligatoire est manquant',
        'UNIQUE constraint failed: patients.email': 'Cet email est déjà utilisé',
        'SQLSTATE': 'Une erreur est survenue lors de l\'enregistrement'
    };
    
    for (const [key, value] of Object.entries(errorTranslations)) {
        if (errorMsg.includes(key)) return value;
    }
    
    return errorMsg;
}

// Registration Form Handler
document.getElementById('register-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const btn = document.getElementById('register-btn');
    const messagesDiv = document.getElementById('register-messages');
    
    // Clear previous messages
    messagesDiv.innerHTML = '';
    
    // Client-side validation
    const nom = form.nom.value.trim();
    const prenom = form.prenom.value.trim();
    const email = form.email.value.trim();
    const dateOfBirth = form.date_of_birth.value;
    const gender = form.gender.value;
    const telephone = form.telephone.value.trim();
    const password = form.password.value;
    const passwordConfirm = form.password_confirmation.value;
    
    if (!nom) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre nom</div>';
        return;
    }
    
    if (!prenom) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre prénom</div>';
        return;
    }
    
    if (!email) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre adresse e-mail</div>';
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer une adresse e-mail valide</div>';
        return;
    }
    
    if (!dateOfBirth) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre date de naissance</div>';
        return;
    }
    
    if (!gender) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez sélectionner votre sexe</div>';
        return;
    }
    
    if (!telephone) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre numéro de téléphone</div>';
        return;
    }
    
    if (!/^[0-9+\-\s()]{10,}$/.test(telephone)) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer un numéro de téléphone valide</div>';
        return;
    }
    
    if (!password) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer un mot de passe</div>';
        return;
    }
    
    if (password.length < 8) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Le mot de passe doit contenir au minimum 8 caractères</div>';
        return;
    }
    
    if (!passwordConfirm) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez confirmer votre mot de passe</div>';
        return;
    }
    
    if (password !== passwordConfirm) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Les mots de passe ne correspondent pas. Vérifiez et réessayez.</div>';
        return;
    }
    
    // Collect form data
    const formData = {
        nom: nom,
        prenom: prenom,
        email: email,
        password: password,
        password_confirmation: passwordConfirm,
        date_of_birth: dateOfBirth,
        gender: gender,
        telephone: telephone,
    };
    
    btn.classList.add('is-loading');
    btn.disabled = true;
    
    try {
        const result = await registerPatient(formData);
        messagesDiv.innerHTML = '<div class="success-box">✓ Inscription réussie! Redirection vers votre espace...</div>';
        
        setTimeout(() => {
            window.location.href = resolveDashboardPath(result.user || getAuthData().user);
        }, 1500);
    } catch (error) {
        const userFriendlyMessage = translateErrorMessage(error.message);
        messagesDiv.innerHTML = `<div class="error-box">✗ ${userFriendlyMessage}</div>`;
    } finally {
        btn.classList.remove('is-loading');
        btn.disabled = false;
    }
});

// Login Form Handler
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const form = e.target;
    const btn = document.getElementById('login-btn');
    const messagesDiv = document.getElementById('login-messages');
    
    // Clear previous messages
    messagesDiv.innerHTML = '';
    
    // Client-side validation
    const email = form.email.value.trim();
    const password = form.password.value;
    
    if (!email) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre adresse e-mail</div>';
        return;
    }
    
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer une adresse e-mail valide</div>';
        return;
    }
    
    if (!password) {
        messagesDiv.innerHTML = '<div class="error-box">⚠ Veuillez entrer votre mot de passe</div>';
        return;
    }
    
    btn.classList.add('is-loading');
    btn.disabled = true;
    
    try {
        const result = await login(email, password);
        messagesDiv.innerHTML = '<div class="success-box">la connexion est reussit</div>';
        setTimeout(() => {
            window.location.href = resolveDashboardPath(result.user || getAuthData().user);
        }, 1500);
    } catch (error) {
        const userFriendlyMessage = error.message || 'Email ou mot de passe incorrect. Veuillez réessayer.';
        messagesDiv.innerHTML = `<div class="error-box">✗ ${userFriendlyMessage}</div>`;
    } finally {
        btn.classList.remove('is-loading');
        btn.disabled = false;
    }
});

window.addEventListener("load", mainF);
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const switchContainer = document.getElementById("switch-cnt");
    const switchPanels = {
        login: document.getElementById("switch-c2"),
        register: document.getElementById("switch-c1"),
    };
    const registerContainer = document.getElementById("a-container");
    const loginContainer = document.getElementById("b-container");
    const circles = Array.from(document.querySelectorAll(".switch__circle"));
    const switchButtons = Array.from(document.querySelectorAll(".switch-btn"));
    const transitionMs = 520;
    let redirectTimer = null;
    let isSwitching = false;

    if (!switchContainer || !registerContainer || !loginContainer) {
        return;
    }

    const setAuthMode = (mode, animate = false) => {
        const showLogin = mode === "login";

        if (animate) {
            switchContainer.classList.add("is-gx");
            window.setTimeout(() => switchContainer.classList.remove("is-gx"), 1500);
        }

        switchContainer.classList.toggle("is-txr", showLogin);
        circles.forEach((circle) => circle.classList.toggle("is-txr", showLogin));
        registerContainer.classList.toggle("is-txl", showLogin);
        loginContainer.classList.toggle("is-txl", showLogin);
        loginContainer.classList.toggle("is-z200", showLogin);
        switchPanels.register?.classList.toggle("is-hidden", showLogin);
        switchPanels.login?.classList.toggle("is-hidden", !showLogin);
    };

    const initialMode = window.location.pathname.toLowerCase().includes("/connexion")
        || window.location.pathname.toLowerCase().endsWith("/login")
        ? "login"
        : "register";

    setAuthMode(initialMode, false);

    window.__authAnimateRedirect = (targetMode) => {
        const targetUrl = targetMode === "register"
            ? "{{ url('/inscription') }}"
            : "{{ url('/connexion') }}";
        const targetPath = new URL(targetUrl, window.location.origin).pathname;

        if (isSwitching) {
            return;
        }

        isSwitching = true;
        setAuthMode(targetMode, true);
        window.clearTimeout(redirectTimer);

        redirectTimer = window.setTimeout(() => {
            isSwitching = false;

            if (window.location.pathname !== targetPath) {
                window.location.href = targetUrl;
                return;
            }

            setAuthMode(targetMode, false);
        }, transitionMs);
    };

    switchButtons.forEach((button) => {
        button.addEventListener("click", (event) => {
            event.preventDefault();
            window.__authAnimateRedirect(button.dataset.authTarget || "login");
        });
    });
});
</script>

@endsection

