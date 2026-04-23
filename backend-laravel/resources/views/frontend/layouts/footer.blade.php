<footer class="main-footer">
    <div class="footer-container">
        <div class="footer-grid">
            <div class="footer-brand-block">
                <div class="brand-logo footer-brand-logo">
                    <span class="logo-cross"><i class="fas fa-plus"></i></span>
                    <span class="logo-text">Medicare</span>
                </div>
                <p class="footer-brand-text">
                    Plateforme de gestion medicale pour le suivi des patients, la coordination des rendez-vous
                    et la continute des soins au sein du cabinet.
                </p>
            </div>

            <div class="footer-column">
                <h4>Navigation</h4>
                <a href="{{ route('home') }}">Accueil</a>
                <a href="{{ route('about') }}">A Propos</a>
                <a href="{{ route('services') }}">Services</a>
                <a href="{{ route('equipe') }}">Equipe</a>
            </div>

            <div class="footer-column">
                <h4>Parcours patient</h4>
                <a href="{{ route('contact') }}">Informations rendez-vous</a>
                <a href="{{ route('login') }}">Connexion</a>
                <a href="{{ route('register') }}">Inscription</a>
                <a href="{{ route('dashboard') }}" data-dashboard-link>Mon espace</a>
            </div>

            <div class="footer-column">
                <h4>Contact</h4>
                <a href="tel:+212522001122">+212 522 00 11 22</a>
                <a href="mailto:contact@medicare.ma">contact@medicare.ma</a>
                <span>12 Rue Ibn Sina, Casablanca</span>
                <span>Lun-Ven: 08h00 - 16h00</span>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="footer-bottom-text">
                <span class="footer-copyright">© {{ date('Y') }} Medicare</span>
                <span class="footer-divider">|</span>
                <span>Cabinet medical - Parcours de soins coordonne</span>
            </div>

            <div class="footer-social">
                <a href="#" class="footer-social-link" title="Facebook" aria-label="Facebook">
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#" class="footer-social-link" title="Instagram" aria-label="Instagram">
                    <i class="fab fa-instagram"></i>
                </a>
                <a href="#" class="footer-social-link" title="WhatsApp" aria-label="WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <a href="{{ route('contact') }}" class="footer-social-link" title="Rendez-vous" aria-label="Rendez-vous" data-appointment-access>
                    <i class="fas fa-calendar-check"></i>
                </a>
            </div>
        </div>
    </div>
</footer>
