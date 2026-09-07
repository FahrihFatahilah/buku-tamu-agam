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
