/**
 * NSOROMMA — Page d'accueil « étagères »
 * Apparition des étagères au défilement et navigation horizontale par flèches.
 */
(function () {
    'use strict';

    var shelves = document.querySelectorAll('[data-shelf]');

    // Animation d'entrée des étagères lorsqu'elles deviennent visibles
    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.2, rootMargin: '0px 0px -40px 0px' });

        shelves.forEach(function (shelf) { observer.observe(shelf); });
    } else {
        shelves.forEach(function (shelf) { shelf.classList.add('is-visible'); });
    }

    // Flèches précédent / suivant
    shelves.forEach(function (shelf) {
        var row = shelf.querySelector('[data-shelf-row]');
        var arrows = shelf.querySelectorAll('[data-scroll]');

        if (!row) {
            return;
        }

        function updateArrows() {
            var maxScroll = row.scrollWidth - row.clientWidth - 2;
            arrows.forEach(function (arrow) {
                var direction = Number(arrow.dataset.scroll);
                arrow.disabled = direction < 0 ? row.scrollLeft <= 2 : row.scrollLeft >= maxScroll;
            });
        }

        arrows.forEach(function (arrow) {
            arrow.addEventListener('click', function () {
                row.scrollBy({ left: Number(arrow.dataset.scroll) * row.clientWidth * 0.8, behavior: 'smooth' });
            });
        });

        row.addEventListener('scroll', updateArrows, { passive: true });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });
})();
