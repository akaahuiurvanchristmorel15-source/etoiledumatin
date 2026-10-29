/**
 * NSOROMMA — Navigation « vivante » sans rechargement de page.
 *
 * Une zone marquée data-live="nom" se met à jour sur place : ses liens et formulaires
 * sont envoyés en arrière-plan, puis la même zone est extraite de la page renvoyée par le serveur.
 * Le serveur ne change pas : il répond comme d'habitude (HTML, redirections, messages flash).
 *
 * - data-live-stay sur un formulaire : la page reste telle quelle (ex. « Ajouter au panier » depuis une fiche livre),
 *   y compris pour un formulaire placé hors de toute zone.
 * - data-live-sync="clé" : élément recopié depuis chaque réponse (badges du panier…).
 * - data-live-poll="secondes" : la zone se rafraîchit seule à intervalle régulier.
 * - data-live-skip : lien ou formulaire laissé en navigation classique.
 *
 * En cas de souci (erreur serveur, session expirée, page inattendue), on retombe sur la navigation classique.
 */
(function () {
    'use strict';

    if (!window.fetch || !window.DOMParser) {
        return;
    }

    var script = document.currentScript;
    var loginPath = script && script.dataset.loginPath ? new URL(script.dataset.loginPath, location.href).pathname : null;
    var parser = new DOMParser();

    function sameOrigin(url) {
        return new URL(url, location.href).origin === location.origin;
    }

    function regionOf(element) {
        return element.closest('[data-live]');
    }

    function syncFrom(doc) {
        // Badges et compteurs partagés
        document.querySelectorAll('[data-live-sync]').forEach(function (element) {
            var fresh = doc.querySelector('[data-live-sync="' + element.dataset.liveSync + '"]');

            if (fresh) {
                element.replaceWith(document.importNode(fresh, true));
            }
        });

        // Messages flash de la réponse, affichés dans la pile de notifications
        var stack = document.querySelector('[data-toast-stack]');

        doc.querySelectorAll('[data-toast-stack] [data-toast]').forEach(function (toast) {
            if (stack) {
                stack.appendChild(document.importNode(toast, true));
            }
        });

        if (stack && window.initToasts) {
            window.initToasts(stack);
        }
    }

    function swap(region, doc) {
        var fresh = doc.querySelector('[data-live="' + region.dataset.live + '"]');

        if (!fresh) {
            return false;
        }

        if (doc.title) {
            document.title = doc.title;
        }

        var imported = document.importNode(fresh, true);
        imported.classList.add('is-live-entering');
        region.replaceWith(imported);
        requestAnimationFrame(function () { imported.classList.remove('is-live-entering'); });

        if (window.initIcons) {
            window.initIcons();
        }

        document.dispatchEvent(new CustomEvent('live:updated', { detail: { region: imported } }));

        return true;
    }

    /**
     * Envoie la requête et applique la réponse à la zone ; renvoie false pour laisser le navigateur faire.
     */
    function load(region, url, options, behaviour) {
        region.classList.add('is-live-loading');
        region.setAttribute('aria-busy', 'true');

        return fetch(url, Object.assign({
            credentials: 'same-origin',
            headers: { 'X-Live': region.dataset.live, 'Accept': 'text/html' },
        }, options)).then(function (response) {
            var finalUrl = new URL(response.url);

            // Session expirée, erreur serveur ou demande de connexion : navigation classique
            if (!response.ok || (loginPath && finalUrl.pathname === loginPath && location.pathname !== loginPath)) {
                throw new Error('fallback');
            }

            return response.text().then(function (html) {
                var doc = parser.parseFromString(html, 'text/html');

                if (behaviour.stay) {
                    syncFrom(doc);

                    return true;
                }

                if (!swap(region, doc)) {
                    throw new Error('fallback');
                }

                syncFrom(doc);

                if (behaviour.push && finalUrl.href !== location.href) {
                    history.pushState({ live: true }, '', finalUrl.href);
                }

                if (behaviour.scroll) {
                    var target = document.querySelector('[data-live="' + region.dataset.live + '"]');
                    var top = target.getBoundingClientRect().top + window.scrollY - 90;

                    if (top < window.scrollY) {
                        window.scrollTo({ top: top, behavior: 'smooth' });
                    }
                }

                return true;
            });
        }).finally(function () {
            region.classList.remove('is-live-loading');
            region.removeAttribute('aria-busy');
        });
    }

    // Liens (filtres, pagination, onglets…)
    document.addEventListener('click', function (event) {
        var link = event.target.closest('a[href]');
        var region = link && regionOf(link);

        if (!region || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey
            || link.target || link.hasAttribute('download') || link.closest('[data-live-skip]') || !sameOrigin(link.href)
            || link.getAttribute('href').charAt(0) === '#' || new URL(link.href).pathname !== location.pathname) {
            // Seuls les liens qui restent sur la même page (filtres, tri, pagination) sont pris en charge
            return;
        }

        event.preventDefault();
        load(region, link.href, {}, { push: true, scroll: true }).catch(function () { location.href = link.href; });
    });

    // Formulaires (recherche, filtres, panier, avis…)
    document.addEventListener('submit', function (event) {
        var form = event.target;
        // Un formulaire « data-live-stay » fonctionne aussi hors d'une zone (ex. « Ajouter au panier » de l'accueil)
        var region = regionOf(form) || (form.hasAttribute('data-live-stay') ? form : null);

        if (!region || event.defaultPrevented || form.closest('[data-live-skip]') || form.target || !sameOrigin(form.action)) {
            return;
        }

        var submitter = event.submitter;
        var method = (form.getAttribute('method') || 'GET').toUpperCase();
        var data = new FormData(form, submitter);
        var url = form.action;
        var options = {};

        if (method === 'GET') {
            var query = new URLSearchParams(data);

            // Champs vides retirés pour garder des adresses lisibles
            Array.from(query.keys()).forEach(function (key) {
                if (query.get(key) === '') {
                    query.delete(key);
                }
            });

            url = url.split('?')[0] + (query.toString() ? '?' + query.toString() : '');
        } else {
            options = { method: 'POST', body: data };
        }

        event.preventDefault();

        if (submitter) {
            submitter.disabled = true;
        }

        // Après un envoi, l'adresse suit la redirection du serveur (ex. création d'un livre -> liste)
        load(region, url, options, { push: true, stay: form.hasAttribute('data-live-stay'), scroll: true })
            .then(function () {
                if (form.hasAttribute('data-live-reset') && form.isConnected) {
                    form.reset();
                }
            })
            .catch(function () {
                form.setAttribute('data-live-skip', '');
                form.requestSubmit ? form.requestSubmit(submitter) : form.submit();
            })
            .finally(function () {
                if (submitter) {
                    submitter.disabled = false;
                }
            });
    });

    // Formulaires GET qui se soumettent seuls (listes déroulantes de filtres)
    document.addEventListener('change', function (event) {
        var field = event.target;
        var form = field.form;

        if (form && regionOf(form) && field.hasAttribute('data-live-autosubmit')) {
            form.requestSubmit();
        }
    });

    // Retour / avance du navigateur sur une page mise à jour sans rechargement
    window.addEventListener('popstate', function () {
        var region = document.querySelector('[data-live]');

        if (!region) {
            location.reload();

            return;
        }

        load(region, location.href, {}, {}).catch(function () { location.reload(); });
    });

    // Zones qui se rafraîchissent seules (tableau de bord…)
    function startPolling() {
        document.querySelectorAll('[data-live-poll]').forEach(function (region) {
            if (region.dataset.livePolling) {
                return;
            }

            region.dataset.livePolling = '1';
            var delay = Math.max(10, Number(region.dataset.livePoll) || 30) * 1000;

            setInterval(function () {
                var current = document.querySelector('[data-live="' + region.dataset.live + '"]');

                if (document.hidden || !current) {
                    return;
                }

                load(current, location.href, {}, {}).catch(function () {});
            }, delay);
        });
    }

    startPolling();
})();
