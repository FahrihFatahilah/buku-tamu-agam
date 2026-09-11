import Alpine from 'alpinejs';
import intersect from '@alpinejs/intersect';

window.Alpine = Alpine;
Alpine.plugin(intersect);
Alpine.start();

document.addEventListener('DOMContentLoaded', () => {

    // ── Intersection Observer — all reveal variants ────────────────────────
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -48px 0px' }
    );

    document.querySelectorAll(
        '.reveal, .reveal-left, .reveal-right, .reveal-scale, .reveal-blur, .stagger-children, .ornament-line'
    ).forEach(el => observer.observe(el));

    // ── Parallax ───────────────────────────────────────────────────────────
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reducedMotion) return;

    // Collect all parallax elements once
    const parallaxItems = [];

    document.querySelectorAll('.parallax-bg, .parallax-up, .parallax-down').forEach(el => {
        const section = el.closest('section') ?? el.parentElement;
        let speed = parseFloat(el.dataset.parallaxSpeed ?? '0.25');

        // Direction: parallax-down moves opposite to parallax-up
        if (el.classList.contains('parallax-down')) speed = -Math.abs(speed);
        else speed = Math.abs(speed); // parallax-bg and parallax-up move upward (negative Y)

        parallaxItems.push({ el, section, speed });
    });

    if (!parallaxItems.length) return;

    let ticking = false;

    const updateParallax = () => {
        const scrollY = window.scrollY;
        const vh = window.innerHeight;

        parallaxItems.forEach(({ el, section, speed }) => {
            const rect = section.getBoundingClientRect();
            // Only update when section is near viewport
            if (rect.bottom < -vh || rect.top > vh * 2) return;

            // offset = how far the section center is from viewport center
            const sectionCenter = rect.top + rect.height / 2;
            const viewportCenter = vh / 2;
            const offset = (sectionCenter - viewportCenter) * speed;

            el.style.transform = `translateY(${offset}px)`;
        });

        ticking = false;
    };

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }, { passive: true });

    // Initial call
    updateParallax();
});

/* ─────────────────────────────────────────────────────────────────────────
 * Document animation runtime
 *
 * Powers the builder's animation system: entrance (IntersectionObserver),
 * continuous (CSS keyframes with a duration variable) and scroll (a single
 * rAF loop). Kept separate from the block above so the existing coded
 * templates are unaffected.
 * ───────────────────────────────────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    // ── Resolve authored timings into CSS variables ────────────────────────
    document.querySelectorAll('[data-anim-entrance], [data-anim-continuous]').forEach((el) => {
        const entranceDur = el.dataset.animEntranceDuration;
        const continuousDur = el.dataset.animContinuousDuration;
        const dur = entranceDur || continuousDur;

        if (dur) el.style.setProperty('--n-anim-duration', `${dur}ms`);
        if (el.dataset.animDelay) el.style.setProperty('--n-anim-delay', `${el.dataset.animDelay}ms`);

        // Intensity scales the travel distance of the entrance animations.
        const speed = parseFloat(el.dataset.animSpeed || '0');
        if (!Number.isNaN(speed) && speed > 0) {
            el.style.setProperty('--n-anim-dist', `${Math.round(32 * speed)}px`);
        }
    });

    // ── Entrance ───────────────────────────────────────────────────────────
    const entrants = document.querySelectorAll('[data-anim-watch]');

    if (reduceMotion || !('IntersectionObserver' in window)) {
        entrants.forEach((el) => el.classList.add('is-in'));
    } else {
        const entranceObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-in');

                // `repeat: loop` keeps replaying on re-entry.
                if (entry.target.dataset.animRepeat !== 'loop') {
                    entranceObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        entrants.forEach((el) => entranceObserver.observe(el));
    }

    if (reduceMotion) return;

    // ── Scroll animations ──────────────────────────────────────────────────
    const scrollItems = [];

    document.querySelectorAll('[data-anim-scroll]').forEach((el) => {
        const speed = parseFloat(el.dataset.animSpeed || '0.25');

        scrollItems.push({
            el,
            type: el.dataset.animScroll,
            speed: Number.isNaN(speed) ? 0.25 : Math.abs(speed),
        });
    });

    if (scrollItems.length) {
        let ticking = false;

        const updateScrollAnimations = () => {
            const vh = window.innerHeight;

            scrollItems.forEach(({ el, type, speed }) => {
                const rect = el.getBoundingClientRect();

                // Skip work for elements far outside the viewport.
                if (rect.bottom < -vh || rect.top > vh * 2) return;

                // -1 (below viewport) .. 1 (above viewport)
                const progress = 1 - 2 * ((rect.top + rect.height / 2) / vh);

                switch (type) {
                    case 'parallax':
                        el.style.transform = `translateY(${progress * speed * 60}px)`;
                        break;
                    case 'move-scroll':
                        el.style.transform = `translateX(${progress * speed * 80}px)`;
                        break;
                    case 'scale-scroll':
                        el.style.transform = `scale(${1 + progress * speed * 0.15})`;
                        break;
                    case 'rotate-scroll':
                        el.style.transform = `rotate(${progress * speed * 8}deg)`;
                        break;
                    case 'fade-scroll': {
                        const visible = Math.max(0, Math.min(1, 1 - Math.abs(progress)));
                        el.style.opacity = String(visible);
                        break;
                    }
                    default:
                        break;
                }
            });

            ticking = false;
        };

        window.addEventListener('scroll', () => {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(updateScrollAnimations);
        }, { passive: true });

        updateScrollAnimations();
    }

    // ── Effects ────────────────────────────────────────────────────────────
    document.querySelectorAll('[data-effects]').forEach((el) => {
        const effects = (el.dataset.effects || '').split(',').map((s) => s.trim());

        if (effects.includes('mouse-parallax')) {
            const strength = 0.04;

            window.addEventListener('mousemove', (event) => {
                const x = (event.clientX / window.innerWidth - 0.5) * 2;
                const y = (event.clientY / window.innerHeight - 0.5) * 2;
                el.style.transform = `translate(${x * strength * 60}px, ${y * strength * 60}px)`;
            }, { passive: true });
        }

        if (effects.includes('cursor-glow')) {
            const glow = document.createElement('div');
            glow.className = 'n-cursor-glow';
            glow.style.width = '220px';
            glow.style.height = '220px';
            glow.style.background = 'radial-gradient(circle, rgba(201,168,76,.35), transparent 70%)';
            document.body.appendChild(glow);

            window.addEventListener('mousemove', (event) => {
                glow.style.opacity = '1';
                glow.style.left = `${event.clientX}px`;
                glow.style.top = `${event.clientY}px`;
            }, { passive: true });
        }
    });
});
