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
                row.scrollBy({ left: Number(arrow.dataset.scroll) * row.clientWidth, behavior: 'smooth' });
            });
        });

        row.addEventListener('scroll', updateArrows, { passive: true });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });

    // Partage d'un livre : partage natif du téléphone, sinon copie du lien
    // (écoute déléguée : le bouton peut être remplacé par une mise à jour sans rechargement)
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-share]');

        if (button) {
            var url = button.dataset.shareUrl;
            var label = button.querySelector('[data-share-label]');

            if (navigator.share) {
                navigator.share({ title: button.dataset.shareTitle, url: url }).catch(function () {});
                return;
            }

            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(function () {
                    if (label) {
                        label.textContent = 'Lien copié';
                        setTimeout(function () { label.textContent = 'Partager'; }, 2000);
                    }
                });
            }
        }
    });
})();
