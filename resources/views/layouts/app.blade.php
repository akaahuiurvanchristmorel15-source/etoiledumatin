<!DOCTYPE html>
<html lang="fr" class="h-full bg-stone-50 text-stone-900 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Librairie NSOROMMA — Spiritualité, Philosophie & Savoirs Africains')</title>
    <meta name="description" content="@yield('meta_description', 'Librairie en ligne panafricaine spécialisée en spiritualité africaine, philosophie, religion et politique. Achat de livres en téléchargement PDF immédiat ou livraison de livres physiques.')">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Aperçus de partage (WhatsApp, Facebook, X…) --}}
    <meta property="og:site_name" content="NSOROMMA">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="@yield('title', 'Librairie NSOROMMA — Spiritualité, Philosophie & Savoirs Africains')">
    <meta property="og:description" content="@yield('meta_description', 'Librairie en ligne panafricaine : spiritualité, philosophie, religion et savoirs africains en papier, PDF et audio.')">
    <meta property="og:url" content="@yield('canonical', url()->current())">
    <meta property="og:image" content="@yield('og_image', asset('images/brand/nsoromma-logo.png'))">
    <meta name="twitter:card" content="@yield('twitter_card', 'summary')">

    @stack('structured-data')

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">

    {{-- Mode jour / nuit : choix mémorisé, sinon réglage de l'appareil ; appliqué avant l'affichage pour éviter tout flash --}}
    <meta name="color-scheme" content="light dark">
    <script>
        (function () {
            var root = document.documentElement;
            var media = window.matchMedia('(prefers-color-scheme: dark)');

            function stored() {
                try { return localStorage.getItem('theme'); } catch (e) { return null; }
            }

            function apply(theme) {
                root.dataset.theme = theme;
                document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
                    button.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
                    button.setAttribute('aria-label', theme === 'dark' ? 'Passer en mode jour' : 'Passer en mode nuit');
                });
            }

            apply(stored() || (media.matches ? 'dark' : 'light'));

            /**
             * Bascule animée : l'icône pivote, puis le nouveau mode s'étend en cercle depuis le bouton
             * (navigateurs récents ; ailleurs, simple fondu des couleurs).
             */
            window.toggleTheme = function (origin) {
                var theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
                var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                try { localStorage.setItem('theme', theme); } catch (e) {}

                if (origin) {
                    origin.classList.remove('is-spinning');
                    void origin.offsetWidth;
                    origin.classList.add('is-spinning');
                    setTimeout(function () { origin.classList.remove('is-spinning'); }, 700);
                }

                if (reduceMotion || !document.startViewTransition || !origin) {
                    root.classList.add('theme-switching');
                    apply(theme);
                    setTimeout(function () { root.classList.remove('theme-switching'); }, 350);

                    return;
                }

                var rect = origin.getBoundingClientRect();
                var x = rect.left + rect.width / 2;
                var y = rect.top + rect.height / 2;
                var radius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

                root.classList.add('theme-reveal');
                var transition = document.startViewTransition(function () { apply(theme); });

                transition.ready.then(function () {
                    root.animate(
                        { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + radius + 'px at ' + x + 'px ' + y + 'px)'] },
                        { duration: 650, easing: 'cubic-bezier(.4, 0, .2, 1)', pseudoElement: '::view-transition-new(root)' }
                    );
                });

                transition.finished.finally(function () { root.classList.remove('theme-reveal'); });
            };

            // Tant que le visiteur n'a rien choisi, le site suit le réglage de son appareil
            media.addEventListener('change', function (event) {
                if (!stored()) { apply(event.matches ? 'dark' : 'light'); }
            });

            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-theme-toggle]');
                if (button) { window.toggleTheme(button); }
            });

            document.addEventListener('DOMContentLoaded', function () { apply(root.dataset.theme); });

            // Transition entre pages : sens inverse lors d'un retour arrière ; sinon, repli pour les navigateurs sans transition
            if ('onpagereveal' in window) {
                window.addEventListener('pagereveal', function (event) {
                    var type = window.navigation && navigation.activation && navigation.activation.navigationType;
                    var from = window.navigation && navigation.activation && navigation.activation.from;
                    var entry = window.navigation && navigation.activation && navigation.activation.entry;

                    if (event.viewTransition && type === 'traverse' && from && entry && entry.index < from.index) {
                        event.viewTransition.types.add('back');
                    }
                });
            } else {
                root.classList.add('no-page-transition');
            }
        })();
    </script>

    {{-- Masque les éléments Alpine (menus, recherche, tiroirs) tant qu'Alpine n'est pas initialisé --}}
    <style>[x-cloak] { display: none !important; }</style>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}?v={{ filemtime(public_path('css/shell.css')) }}">

    @stack('styles')
    <link rel="stylesheet" href="{{ asset('css/theme-dark.css') }}?v={{ filemtime(public_path('css/theme-dark.css')) }}">

    <style>
        .font-serif-title { font-family: 'Cinzel', Georgia, serif; }
        .font-editorial { font-family: 'Playfair Display', Georgia, serif; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="@yield('body_class', 'broks-page')" @yield('body_attributes', '')>
    <div class="page-progress" data-page-progress aria-hidden="true"></div>
    @php
        $announcement = \App\Models\Setting::content('announcement_text');
        $announcementLink = \App\Models\Setting::content('announcement_link');
        $socialLinks = collect(['facebook' => 'Facebook', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'youtube' => 'YouTube'])
            ->mapWithKeys(fn (string $label, string $network) => [$label => \App\Models\Setting::content($network.'_url')])
            ->filter();
    @endphp

    @if($announcement !== '')
        {{-- Bandeau d'annonce (Paramètres › Contenus du site) ; fermé, il reste masqué tant que son texte ne change pas --}}
        <div class="site-announcement" x-show="shown" x-transition.opacity
             x-data="{
                key: 'announcement:' + @js(md5($announcement)),
                shown: true,
                init() { try { this.shown = localStorage.getItem(this.key) !== 'closed' } catch (e) {} },
                close() { this.shown = false; try { localStorage.setItem(this.key, 'closed') } catch (e) {} },
             }">
            @if($announcementLink !== '')
                <a href="{{ $announcementLink }}" class="site-announcement-text">{{ $announcement }}</a>
            @else
                <span class="site-announcement-text">{{ $announcement }}</span>
            @endif
            <button type="button" class="site-announcement-close" aria-label="Fermer l'annonce" @click="close()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
    @endif

    <!-- Grande Carte Principale BROKS -->
    <main class="library-container" role="main">
        <!-- 1. En-tête / Barre de navigation Principale BROKS Responsive -->
        <x-navbar />

        <!-- 2. Notifications -->
        <x-toasts />

        <!-- 3. Contenu de la Page -->
        <div class="page-body min-h-[50vh]">
            @yield('content')
        </div>

        <!-- 4. Pied de page -->
        <footer class="site-footer" role="contentinfo">
            <div class="site-footer-brand">
                <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" width="26" height="26">
                <span>© {{ date('Y') }} NSOROMMA — Librairie panafricaine</span>
            </div>
            <nav class="site-footer-links" aria-label="Liens du pied de page">
                <a href="{{ route('catalog.index') }}">Catalogue</a>
                <a href="{{ route('news.index') }}">Actualités</a>
                <a href="{{ route('orders.lookup') }}">Suivi de commande</a>
                @if($whatsapp = \App\Models\Setting::content('support_whatsapp'))
                    <a href="https://wa.me/{{ $whatsapp }}">Assistance</a>
                @endif
                @if($email = \App\Models\Setting::content('contact_email'))
                    <a href="mailto:{{ $email }}">{{ $email }}</a>
                @endif
                @if($phone = \App\Models\Setting::content('contact_phone'))
                    <a href="tel:{{ preg_replace('/[^\d+]/', '', $phone) }}">{{ $phone }}</a>
                @endif
                @foreach($socialLinks as $label => $url)
                    <a href="{{ $url }}" rel="noopener">{{ $label }}</a>
                @endforeach
                <a href="{{ route('legal.notice') }}">Mentions légales</a>
                <a href="{{ route('legal.privacy') }}">Confidentialité</a>
                <a href="{{ route('legal.terms') }}">CGV</a>
            </nav>
        </footer>
    </main>

    <script src="{{ asset('js/motion.js') }}?v={{ filemtime(public_path('js/motion.js')) }}" defer></script>
    <script src="{{ asset('js/live.js') }}?v={{ filemtime(public_path('js/live.js')) }}" data-login-path="{{ route('login') }}" defer></script>
    @stack('scripts')
</body>
</html>
