<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Administration') — NSOROMMA</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}?v=2" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}?v=2">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v=2">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}?v={{ filemtime(public_path('css/shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">

    <script src="{{ asset('js/behaviors.js') }}?v={{ filemtime(public_path('js/behaviors.js')) }}"></script>
    @stack('styles')
</head>
<body>
    @php
        // Menu regroupé par thème ; un groupe sans entrée autorisée n'est pas affiché
        $navGroups = [
            'Pilotage' => [
                ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'label' => 'Tableau de bord', 'can' => null],
                ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'label' => 'Commandes', 'can' => 'manage-orders'],
            ],
            'Catalogue' => [
                ['route' => 'admin.books.index', 'match' => ['admin.books.*', 'admin.parts.*', 'admin.tracks.*'], 'label' => 'Livres', 'can' => 'manage-catalog'],
                ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'label' => 'Catégories', 'can' => 'manage-catalog'],
                ['route' => 'admin.stock.index', 'match' => 'admin.stock.*', 'label' => 'Stock', 'can' => 'manage-stock', 'count' => $outOfStockCount ?? 0],
            ],
            'Contenu' => [
                ['route' => 'admin.news.index', 'match' => 'admin.news.*', 'label' => 'Actualités', 'can' => 'manage-news'],
                ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'label' => 'Avis clients', 'can' => 'manage-reviews', 'count' => $pendingReviewsCount ?? 0],
            ],
        ];
    @endphp

    <aside class="sidebar" id="sidebar" aria-label="Navigation de l'administration">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" class="sidebar-logo" width="30" height="30">
            <span class="sidebar-name">NSOROMMA<small>Administration</small></span>
        </a>

        <nav class="sidebar-nav">
            @foreach($navGroups as $groupLabel => $items)
                @php $allowedItems = array_filter($items, fn (array $item): bool => ! $item['can'] || Gate::allows($item['can'])); @endphp
                @if($allowedItems)
                    <div class="nav-section">
                        <div class="nav-label">{{ $groupLabel }}</div>
                        @foreach($allowedItems as $item)
                            @php $isActive = request()->routeIs(...(array) $item['match']); @endphp
                            <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>
                                <span>{{ $item['label'] }}</span>
                                @if(($item['count'] ?? 0) > 0)
                                    <span class="nav-count" aria-label="{{ $item['count'] }} en attente">{{ $item['count'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach

            <div class="nav-section">
                <div class="nav-label">Réglages</div>
                @php $isSettings = request()->routeIs('admin.settings.*'); @endphp
                <a href="{{ route('admin.settings.profile') }}" class="nav-link {{ $isSettings ? 'is-active' : '' }}" @if($isSettings) aria-current="page" @endif>
                    <span>Paramètres</span>
                </a>
                <a href="{{ route('home') }}" class="nav-link">
                    <span>Voir la boutique</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-user">
            <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                <div class="sidebar-user-role">{{ auth()->user()->roleLabel() }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="inline-form">
                @csrf
                <button type="submit" class="btn btn-ghost btn-icon" title="Se déconnecter" aria-label="Se déconnecter">
                    <x-admin.icon name="logout" />
                </button>
            </form>
        </div>
    </aside>

    <div class="overlay" id="overlay" aria-hidden="true"></div>

    <div class="main">
        <header class="topbar">
            <button type="button" class="btn btn-secondary btn-icon menu-toggle" id="menu-toggle" aria-label="Ouvrir le menu" aria-controls="sidebar" aria-expanded="false">
                <x-admin.icon name="menu" />
            </button>
            <div class="topbar-title">@yield('title', 'Administration')</div>
            <a href="{{ route('home') }}" class="btn btn-ghost btn-sm topbar-site">
                <x-admin.icon name="external" />
                <span>Voir la boutique</span>
            </a>
        </header>

        <x-toasts />

        @if(\App\Http\Middleware\BlockVisitorsDuringMaintenance::isActive())
            <div class="maintenance-banner" role="status">
                <x-admin.icon name="alert" />
                <span>Mode maintenance actif : les visiteurs voient la page « Le site sera bientôt disponible ».</span>
                @can('manage-store')<a href="{{ route('admin.settings.maintenance') }}">Gérer</a>@endcan
            </div>
        @endif

        <main class="content" data-live="admin">
            @yield('content')
        </main>

        <footer class="footer">© {{ date('Y') }} Éditions NSOROMMA</footer>
    </div>

    <script nonce="{{ Vite::cspNonce() }}">
        (function () {
            var body = document.body;
            var toggle = document.getElementById('menu-toggle');

            function setOpen(open) {
                body.classList.toggle('nav-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
            }

            toggle.addEventListener('click', function () { setOpen(!body.classList.contains('nav-open')); });
            document.getElementById('overlay').addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { setOpen(false); } });
            window.addEventListener('resize', function () { if (window.innerWidth > 1024) { setOpen(false); } });

        })();
    </script>

    @can('manage-orders')
        <script nonce="{{ Vite::cspNonce() }}">
            /**
             * Nouvelles commandes payées : vérification toutes les 30 secondes, notification et compteur dans l'onglet.
             */
            (function () {
                var endpoint = @js(route('admin.notifications.orders'));
                var since = @js(now()->toIso8601String());
                var baseTitle = document.title;
                var unseen = 0;

                function notify(order) {
                    var stack = document.querySelector('[data-toast-stack]');
                    var toast = document.createElement('div');
                    toast.className = 'toast toast-success';
                    toast.setAttribute('role', 'status');
                    toast.setAttribute('data-toast', '');
                    toast.innerHTML = '<span class="toast-icon" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 12.5 10 17 19 7"/></svg></span>'
                        + '<div class="toast-body"><p class="toast-title">Nouvelle commande payée</p><p class="toast-message"><a></a></p></div>'
                        + '<button type="button" class="toast-close" data-toast-close aria-label="Fermer la notification"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>'
                        + '<span class="toast-progress" aria-hidden="true"></span>';

                    var link = toast.querySelector('.toast-message a');
                    link.href = order.url;
                    link.textContent = order.number + ' · ' + order.customer + ' · ' + order.total;

                    stack.appendChild(toast);
                    window.initToasts(stack);
                }

                // Vérifie aussi onglet en arrière-plan : le compteur « (n) » du titre signale les commandes arrivées entre-temps
                function check() {
                    fetch(endpoint + '?since=' + encodeURIComponent(since), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                        .then(function (response) { return response.ok ? response.json() : null; })
                        .then(function (data) {
                            if (!data) {
                                return;
                            }

                            since = data.checked_at;
                            data.orders.forEach(notify);
                            unseen += data.orders.length;
                            unseen = document.hidden ? unseen : 0;
                            document.title = unseen ? '(' + unseen + ') ' + baseTitle : baseTitle;
                        })
                        .catch(function () {});
                }

                document.addEventListener('visibilitychange', function () {
                    if (!document.hidden) {
                        unseen = 0;
                        document.title = baseTitle;
                    }
                });

                setInterval(check, 30000);
            })();
        </script>
    @endcan
    <script src="{{ asset('js/motion.js') }}?v={{ filemtime(public_path('js/motion.js')) }}" defer></script>
    <script src="{{ asset('js/live.js') }}?v={{ filemtime(public_path('js/live.js')) }}" data-login-path="{{ route('admin.login') }}" defer></script>
    @stack('scripts')
</body>
</html>
