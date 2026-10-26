import './bootstrap';

const enableScrollReveal = () => {
    if (
        !('IntersectionObserver' in window)
        || window.matchMedia('(prefers-reduced-motion: reduce)').matches
    ) {
        return;
    }

    const selectors = [
        'main.page-enter > section',
        'main.page-enter > * > header',
        'main.page-enter > * > section',
        'main.page-enter > * > aside',
    ];
    const targets = [...new Set(document.querySelectorAll(selectors.join(',')))];

    if (!targets.length) {
        return;
    }

    targets.forEach((target, index) => {
        target.classList.add('reveal-on-scroll');
        target.style.setProperty('--reveal-delay', `${Math.min(index % 3, 2) * 110}ms`);
    });

    document.documentElement.classList.add('scroll-reveal-ready');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target);
        });
    }, {
        rootMargin: '0px 0px -10% 0px',
        threshold: 0.12,
    });

    targets.forEach((target) => observer.observe(target));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', enableScrollReveal, { once: true });
} else {
    enableScrollReveal();
}
