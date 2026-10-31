/**
 * Comportements déclarés par attributs data-*, en écoute déléguée.
 * Remplacent les attributs onclick/onchange/onerror, interdits par la politique de sécurité du contenu (CSP).
 * Chargé dans le <head>, avant js/live.js : une confirmation refusée bloque aussi l'envoi sans rechargement.
 *
 *  - img[data-fallback]      : image de remplacement si la couverture ne se charge pas
 *  - [data-autosubmit]       : envoie le formulaire dès que la valeur change (tri, quantité, rôle)
 *  - form[data-confirm]      : demande confirmation avant l'envoi
 */
(function () {
    document.addEventListener('error', function (event) {
        var image = event.target;

        if (image.tagName === 'IMG' && image.dataset.fallback && image.getAttribute('src') !== image.dataset.fallback) {
            image.src = image.dataset.fallback;
        }
    }, true);

    document.addEventListener('change', function (event) {
        var field = event.target.closest ? event.target.closest('[data-autosubmit]') : null;

        if (field && field.form) {
            field.form.requestSubmit();
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target.closest('form[data-confirm]');

        if (form && !window.confirm(form.dataset.confirm)) {
            event.preventDefault();
            event.stopImmediatePropagation();
        }
    });
})();
