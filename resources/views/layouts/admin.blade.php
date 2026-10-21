<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Administration') — NSOROMMA</title>

    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/shell.css') }}?v={{ filemtime(public_path('css/shell.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}">

    @stack('styles')
</head>
<body>
    @php
        $navItems = [
            ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'dashboard', 'label' => 'Tableau de bord', 'can' => null],
            ['route' => 'admin.orders.index', 'match' => 'admin.orders.*', 'icon' => 'bag', 'label' => 'Commandes', 'can' => 'manage-orders'],
            ['route' => 'admin.books.index', 'match' => 'admin.books.*', 'icon' => 'book', 'label' => 'Livres', 'can' => 'manage-catalog'],
            ['route' => 'admin.stock.index', 'match' => 'admin.stock.*', 'icon' => 'box', 'label' => 'Stock', 'can' => 'manage-catalog', 'count' => $outOfStockCount ?? 0],
            ['route' => 'admin.categories.index', 'match' => 'admin.categories.*', 'icon' => 'folder', 'label' => 'Catégories', 'can' => 'manage-catalog'],
            ['route' => 'admin.reviews.index', 'match' => 'admin.reviews.*', 'icon' => 'star', 'label' => 'Avis clients', 'can' => 'manage-catalog', 'count' => $pendingReviewsCount ?? 0],
        ];
    @endphp

    <aside class="sidebar" id="sidebar" aria-label="Navigation de l'administration">
        <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
            <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" class="sidebar-logo" width="30" height="30">
            <span class="sidebar-name">NSOROMMA<small>Administration</small></span>
        </a>

        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-label">Gestion</div>
                @foreach($navItems as $item)
                    @if(! $item['can'] || Gate::allows($item['can']))
                        @php $isActive = request()->routeIs($item['match']); @endphp
                        <a href="{{ route($item['route']) }}" class="nav-link {{ $isActive ? 'is-active' : '' }}" @if($isActive) aria-current="page" @endif>
                            <x-admin.icon :name="$item['icon']" />
                            <span>{{ $item['label'] }}</span>
                            @if(($item['count'] ?? 0) > 0)
                                <span class="nav-count" aria-label="{{ $item['count'] }} en attente">{{ $item['count'] }}</span>
                            @endif
                        </a>
                    @endif
                @endforeach
            </div>

            <div class="nav-section">
                <div class="nav-label">Compte</div>
                @php $isSettings = request()->routeIs('admin.settings.*'); @endphp
                <a href="{{ route('admin.settings.profile') }}" class="nav-link {{ $isSettings ? 'is-active' : '' }}" @if($isSettings) aria-current="page" @endif>
                    <x-admin.icon name="settings" />
                    <span>Paramètres</span>
                </a>
                <a href="{{ route('home') }}" class="nav-link">
                    <x-admin.icon name="external" />
                    <span>Voir la boutique</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-user">
            <span class="avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
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

        <main class="content">
            @yield('content')
        </main>

        <footer class="footer">© {{ date('Y') }} Éditions NSOROMMA</footer>
    </div>

    <script>
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

            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!window.confirm(form.dataset.confirm)) { e.preventDefault(); }
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
