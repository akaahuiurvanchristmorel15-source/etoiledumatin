/**
 * NSOROMMA — Apparition en douceur des blocs au défilement.
 * Seuls les blocs encore sous la ligne de flottaison sont animés : rien ne « clignote » au chargement.
 */
(function () {
    'use strict';

    var SELECTOR = '.pg-section, .spotlight-panel, .news-card, .collections, .home-news, .pg-audio-part, .card, .stat-card';

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -30px 0px' });

    function watch(root) {
        root.querySelectorAll(SELECTOR).forEach(function (element, index) {
            if (element.classList.contains('reveal') || element.getBoundingClientRect().top < window.innerHeight) {
                return;
            }

            element.classList.add('reveal');
            element.style.setProperty('--reveal-delay', (index % 4) * 70 + 'ms');
            observer.observe(element);
        });
    }

    watch(document);
    document.addEventListener('live:updated', function (event) { watch(event.detail.region); });
})();
