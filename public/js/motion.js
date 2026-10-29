/**
 * NSOROMMA — Animations : rond qui file vers le panier à l'ajout d'un livre,
 * et apparition en douceur des blocs au défilement (seuls ceux encore sous la ligne de flottaison).
 */
(function () {
    'use strict';

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /**
     * Ajout au panier : un rond part du bouton et file en arc jusqu'à l'icône du panier visible, qui rebondit à l'arrivée.
     */
    function visibleCart() {
        return ['cart-desktop', 'cart-mobile', 'cart-tabbar']
            .map(function (key) { return document.querySelector('[data-live-sync="' + key + '"]'); })
            .find(function (element) {
                if (!element) { return false; }
                var rect = element.getBoundingClientRect();

                return rect.width > 0 && rect.height > 0 && rect.bottom > 0 && rect.top < window.innerHeight;
            });
    }

    function flyToCart(from) {
        var cart = visibleCart();

        if (!cart || !from || !document.body.animate) {
            return;
        }

        var start = from.getBoundingClientRect();
        var end = cart.getBoundingClientRect();
        var size = 18;
        var dx = end.left + end.width / 2 - (start.left + start.width / 2);
        var dy = end.top + end.height / 2 - (start.top + start.height / 2);
        var duration = Math.min(900, Math.max(550, Math.hypot(dx, dy) * 0.9));

        // Deux éléments imbriqués : l'un glisse à l'horizontale, l'autre monte en accélérant, d'où la trajectoire en arc
        var track = document.createElement('div');
        var dot = document.createElement('div');
        track.className = 'cart-fly';
        dot.className = 'cart-fly-dot';
        track.style.left = start.left + start.width / 2 - size / 2 + 'px';
        track.style.top = start.top + start.height / 2 - size / 2 + 'px';
        track.appendChild(dot);
        document.body.appendChild(track);

        track.animate([{ transform: 'translateX(0)' }, { transform: 'translateX(' + dx + 'px)' }], { duration: duration, easing: 'cubic-bezier(.3, 0, .7, 1)', fill: 'forwards' });
        dot.animate([
            { transform: 'translateY(0) scale(1)', opacity: 1 },
            { transform: 'translateY(' + Math.min(-40, dy * 0.15) + 'px) scale(1.25)', opacity: 1, offset: 0.25 },
            { transform: 'translateY(' + dy + 'px) scale(.45)', opacity: .9 },
        ], { duration: duration, easing: 'cubic-bezier(.55, 0, .9, .4)', fill: 'forwards' }).onfinish = function () {
            track.remove();

            // L'icône a pu être remplacée entre-temps par la mise à jour du compteur (js/live.js)
            cart = document.querySelector('[data-live-sync="' + cart.dataset.liveSync + '"]') || cart;
            cart.classList.remove('cart-bump');
            void cart.offsetWidth;
            cart.classList.add('cart-bump');
            setTimeout(function () { cart.classList.remove('cart-bump'); }, 600);
        };
    }

    if (!reduceMotion) {
        document.addEventListener('submit', function (event) {
            var form = event.target.closest('form[data-fly-to-cart]');

            if (form && !event.defaultPrevented && !form.hasAttribute('data-live-skip')) {
                flyToCart(event.submitter || form.querySelector('[type="submit"]'));
            }
        });
    }

    /**
     * Fine barre dorée en haut de l'écran dès qu'on quitte la page (lien ou formulaire classique),
     * pour signaler que la page suivante arrive. Les mises à jour sans rechargement (js/live.js) ne la déclenchent pas.
     */
    var progress = document.querySelector('[data-page-progress]');

    function startProgress(event) {
        // Laisse d'abord js/live.js décider s'il prend la navigation en charge
        setTimeout(function () {
            if (!event.defaultPrevented && progress) {
                progress.classList.add('is-loading');
            }
        }, 0);
    }

    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[href]');

        if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target
            || link.hasAttribute('download') || link.origin !== location.origin || link.getAttribute('href').charAt(0) === '#'
            || (link.pathname === location.pathname && link.search === location.search && link.hash)) {
            return;
        }

        startProgress(event);
    });

    document.addEventListener('submit', startProgress);

    // Retour par le cache du navigateur : la barre ne doit pas rester affichée
    window.addEventListener('pageshow', function () {
        if (progress) {
            progress.classList.remove('is-loading');
        }
    });

    var SELECTOR = '.pg-section, .spotlight-panel, .news-card, .collections, .home-news, .pg-audio-part, .card, .stat-card';

    if (!('IntersectionObserver' in window) || reduceMotion) {
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
