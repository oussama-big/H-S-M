/* ============================================================
   MEDICARE — JS Principal
   ============================================================ */

// ===== NAVIGATION =====
(function () {
    const header    = document.getElementById('header');
    const menuToggle = document.getElementById('menuToggle');
    const navMenu    = document.getElementById('navMenu');
    const userMenuToggle = document.getElementById('userMenuToggle');
    const userDropdown   = document.getElementById('userDropdown');

    // Scroll header
    if (header) {
        window.addEventListener('scroll', () => {
            header.classList.toggle('scrolled', window.scrollY > 40);
        });
    }

    // Mobile menu
    if (menuToggle && navMenu) {
        menuToggle.addEventListener('click', () => {
            const open = navMenu.classList.toggle('active');
            menuToggle.classList.toggle('active', open);
        });

        document.addEventListener('click', (e) => {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                navMenu.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }

    // User dropdown
    if (userMenuToggle && userDropdown) {
        userMenuToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            userDropdown.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            userDropdown.classList.remove('open');
        });
    }
})();

// ===== PARTICLES MÉDICALES =====
function generateParticles() {
    const container = document.getElementById('particles');
    if (!container) return;
    const symbols = ['✚', '⚕', '♥', '+', '◇', '○', '△', '✦', '⬡', '◈', '✛', '⬟'];
    for (let i = 0; i < 18; i++) {
        const p = document.createElement('div');
        p.className = 'particle';
        p.textContent = symbols[Math.floor(Math.random() * symbols.length)];
        p.style.left = Math.random() * 100 + '%';
        p.style.animationDelay = (Math.random() * 15) + 's';
        p.style.animationDuration = (12 + Math.random() * 10) + 's';
        p.style.fontSize = (0.8 + Math.random() * 1.1) + 'rem';
        container.appendChild(p);
    }
}

// ===== HERO PAGE INIT =====
function initHeroPage() {
    if (typeof anime === 'undefined') return;

    // Typing name
    const heroName = document.getElementById('heroName');
    if (heroName) {
        const nameValue = heroName.querySelector('.name-value');
        if (nameValue) {
            const txt = nameValue.textContent;
            nameValue.textContent = '';
            anime({
                targets: { v: 0 }, v: txt.length,
                duration: 1500, delay: 700,
                easing: 'easeInOutQuad',
                update(anim) {
                    nameValue.textContent = txt.substring(0, Math.floor(anim.animatables[0].target.v));
                },
                complete() {
                    const cur = document.createElement('span');
                    cur.textContent = '|';
                    cur.style.cssText = 'animation: blinkCursor 1s infinite; color:var(--primary)';
                    nameValue.appendChild(cur);
                    setTimeout(() => cur.remove(), 2200);
                }
            });
        }
    }

    // Staggered hero elements
    anime({ targets: '.greeting-badge', opacity: [0, 1], translateY: [-20, 0], delay: 200, duration: 800, easing: 'easeOutExpo' });
    anime({ targets: '.hero-title, .hero-description', opacity: [0, 1], translateX: [-30, 0], delay: anime.stagger(200, { start: 900 }), duration: 900, easing: 'easeOutExpo' });
    anime({ targets: '.hero-buttons .btn', opacity: [0, 1], scale: [0.85, 1], delay: anime.stagger(100, { start: 1400 }), duration: 700, easing: 'easeOutBack' });
    anime({ targets: '.hero-social .social-icon', opacity: [0, 1], scale: [0, 1], rotate: [180, 0], delay: anime.stagger(80, { start: 1800 }), duration: 600, easing: 'easeOutBack' });

    const profileImg = document.getElementById('profileImage');
    if (profileImg) {
        anime({ targets: profileImg, opacity: [0, 1], scale: [0.8, 1], delay: 900, duration: 1400, easing: 'easeOutElastic(1, .8)' });
        profileImg.addEventListener('mouseenter', () => anime({ targets: profileImg, scale: [1, 1.05], duration: 350, easing: 'easeOutElastic(1, .8)' }));
        profileImg.addEventListener('mouseleave', () => anime({ targets: profileImg, scale: [1.05, 1], duration: 350, easing: 'easeOutElastic(1, .8)' }));
    }

    document.querySelectorAll('.floating-badge').forEach((b, i) => {
        anime({ targets: b, opacity: [0, 1], scale: [0, 1], delay: 1700 + i * 200, duration: 700, easing: 'easeOutBack' });
    });
}

// ===== STRIP STATS COUNTER (home page) =====
function animateStripStats() {
    document.querySelectorAll('.strip-number[data-count]').forEach(el => {
        const target = parseInt(el.getAttribute('data-count'));
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && typeof anime !== 'undefined') {
                    anime({
                        targets: { v: 0 }, v: target,
                        duration: 2000, easing: 'easeOutExpo',
                        update(anim) {
                            const v = Math.floor(anim.animatables[0].target.v);
                            el.textContent = v >= 1000 ? v.toLocaleString('fr-FR') : v;
                        }
                    });
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        obs.observe(el);
    });
}

// ===== STATS COUNTER (section) =====
function animateStatsOnScroll() {
    document.querySelectorAll('.stat-number[data-count]').forEach(el => {
        const target = parseInt(el.getAttribute('data-count'));
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && typeof anime !== 'undefined') {
                    anime({
                        targets: { v: 0 }, v: target,
                        duration: 2000, easing: 'easeOutExpo',
                        update(anim) {
                            const v = Math.floor(anim.animatables[0].target.v);
                            el.textContent = v >= 1000 ? v.toLocaleString('fr-FR') : v;
                        }
                    });
                    obs.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        obs.observe(el);
    });
}

// ===== SKILL BARS =====
function initSkillAnimations(root = 'body') {
    document.querySelectorAll('.skill-item[data-percent]').forEach(item => {
        const bar     = item.querySelector('.skill-progress');
        const pctEl   = item.querySelector('.skill-percent');
        const percent = parseInt(item.getAttribute('data-percent') || 0);

        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && typeof anime !== 'undefined') {
                    anime({ targets: bar, width: ['0%', percent + '%'], duration: 1800, easing: 'easeOutExpo', delay: 150 });
                    anime({
                        targets: { v: 0 }, v: percent, duration: 1800, easing: 'easeOutExpo', delay: 150,
                        update(anim) { if (pctEl) pctEl.textContent = Math.floor(anim.animatables[0].target.v) + '%'; }
                    });
                    obs.unobserve(item);
                }
            });
        }, { threshold: 0.4 });
        obs.observe(item);
    });
}

// ===== TIMELINE ANIMATIONS =====
function initTimelineAnimations() {
    if (typeof anime === 'undefined') return;
    document.querySelectorAll('.timeline-item').forEach((item, i) => {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    anime({ targets: item, opacity: [0, 1], translateX: [-40, 0], delay: i * 120, duration: 900, easing: 'easeOutExpo' });
                    obs.unobserve(item);
                }
            });
        }, { threshold: 0.18 });
        obs.observe(item);
    });
}

// ===== CARDS ANIMATION =====
function animateCardsOnScroll() {
    const cards = document.querySelectorAll('.project-card, .value-card, .tarif-card, .stat-item, .skill-category');
    cards.forEach((card, i) => {
        const obs = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && typeof anime !== 'undefined') {
                    anime({ targets: card, opacity: [0, 1], translateY: [35, 0], scale: [0.96, 1], delay: i * 60, duration: 700, easing: 'easeOutExpo' });
                    obs.unobserve(card);
                }
            });
        }, { threshold: 0.15 });
        obs.observe(card);

        card.addEventListener('mouseenter', () => { if (typeof anime !== 'undefined') anime({ targets: card, scale: [1, 1.02], duration: 220, easing: 'easeOutQuad' }); });
        card.addEventListener('mouseleave', () => { if (typeof anime !== 'undefined') anime({ targets: card, scale: [1.02, 1], duration: 220, easing: 'easeOutQuad' }); });
    });
}

// ===== CONTACT FORM =====
(function () {
    const form   = document.getElementById('contactForm');
    const submit = document.getElementById('submitBtn');
    if (!form || !submit) return;

    form.addEventListener('submit', () => {
        submit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours…';
        submit.disabled = true;
    });
})();

// ===== BLINK KEYFRAME =====
const _s = document.createElement('style');
_s.textContent = '@keyframes blinkCursor { 0%,50% { opacity:1; } 51%,100% { opacity:0; } }';
document.head.appendChild(_s);

// ===== AUTO-INIT =====
document.addEventListener('DOMContentLoaded', () => {
    // Page-specific init based on what's in the DOM
    if (document.getElementById('particles'))         generateParticles();
    if (document.querySelector('.skill-item'))        initSkillAnimations();
    if (document.querySelector('.timeline-item'))     initTimelineAnimations();
    if (document.querySelector('.project-card, .value-card')) animateCardsOnScroll();
    if (document.querySelector('.stat-number'))       animateStatsOnScroll();
    if (document.querySelector('.strip-number'))      animateStripStats();
});
