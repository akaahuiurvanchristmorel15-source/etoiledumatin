<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Administration') — L'Étoile du Matin</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')

    <style>
        /* ============ Tokens ============ */
        :root {
            --bg: #F7F6F3;
            --surface: #FFFFFF;
            --surface-alt: #FAF9F6;
            --border: #E8E5DE;
            --text: #1F1E1B;
            --muted: #77736B;
            --accent: #C9A04E;
            --accent-soft: rgba(201, 160, 78, .14);
            --accent-ink: #8A6A22;
            --sidebar-bg: #17171A;
            --sidebar-text: #A9A8A3;
            --sidebar-hover: rgba(255, 255, 255, .06);
            --sidebar-w: 260px;
            --topbar-h: 64px;
            --radius: 14px;
            --shadow: 0 1px 2px rgba(20, 18, 12, .04), 0 4px 16px rgba(20, 18, 12, .04);
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { -webkit-text-size-adjust: 100%; }
        body {
            margin: 0;
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; }

        /* ============ Shell ============ */
        .adm-shell { min-height: 100vh; }

        /* Sidebar */
        .adm-sidebar {
            position: fixed; inset: 0 auto 0 0;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            display: flex; flex-direction: column;
            z-index: 50;
            transition: transform .28s cubic-bezier(.4, 0, .2, 1);
        }
        .adm-brand {
            display: flex; align-items: center; gap: .75rem;
            height: var(--topbar-h);
            padding: 0 1.25rem;
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, .07);
            color: #fff;
        }
        .adm-brand-mark {
            width: 34px; height: 34px; border-radius: 10px;
            background: var(--accent); color: #17171A;
            display: grid; place-items: center; font-size: 1rem; flex-shrink: 0;
        }
        .adm-brand-name { font-family: 'Cinzel', Georgia, serif; font-weight: 700; font-size: .95rem; letter-spacing: .06em; line-height: 1.15; }
        .adm-brand-sub { display: block; font-family: 'Plus Jakarta Sans', sans-serif; font-size: .66rem; letter-spacing: .14em; text-transform: uppercase; color: var(--sidebar-text); font-weight: 500; margin-top: 2px; }

        .adm-nav { flex: 1; overflow-y: auto; padding: 1.25rem .85rem; display: flex; flex-direction: column; gap: .2rem; }
        .adm-nav-label { font-size: .66rem; letter-spacing: .14em; text-transform: uppercase; font-weight: 700; color: #6B6A66; padding: 0 .75rem .5rem; }
        .adm-link {
            display: flex; align-items: center; gap: .8rem;
            padding: .65rem .75rem; border-radius: 10px;
            text-decoration: none; color: var(--sidebar-text);
            font-weight: 500; font-size: .875rem;
            transition: background .15s, color .15s;
        }
        .adm-link svg { flex-shrink: 0; opacity: .85; }
        .adm-link:hover { background: var(--sidebar-hover); color: #fff; }
        .adm-link.active { background: var(--accent-soft); color: var(--accent); font-weight: 600; }
        .adm-link.active svg { opacity: 1; }
        .adm-link:focus-visible, .adm-icon-btn:focus-visible, .adm-logout:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        .adm-user {
            padding: 1rem; border-top: 1px solid rgba(255, 255, 255, .07);
            display: flex; align-items: center; gap: .75rem;
        }
        .adm-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: var(--accent); color: #17171A;
            display: grid; place-items: center; font-weight: 700; font-size: .8rem; flex-shrink: 0;
        }
        .adm-user-info { min-width: 0; flex: 1; }
        .adm-user-name { color: #fff; font-weight: 600; font-size: .85rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .adm-user-role { font-size: .72rem; color: var(--sidebar-text); }
        .adm-logout {
            background: none; border: 0; cursor: pointer; color: var(--sidebar-text);
            padding: .5rem; border-radius: 8px; display: grid; place-items: center;
            transition: background .15s, color .15s;
        }
        .adm-logout:hover { background: rgba(225, 29, 72, .15); color: #FB7185; }

        /* Overlay (mobile) */
        .adm-overlay {
            position: fixed; inset: 0; background: rgba(15, 14, 10, .5);
            backdrop-filter: blur(2px);
            opacity: 0; visibility: hidden;
            transition: opacity .25s, visibility .25s;
            z-index: 40;
        }

        /* Main */
        .adm-main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        .adm-topbar {
            position: sticky; top: 0; z-index: 30;
            height: var(--topbar-h);
            background: rgba(247, 246, 243, .9);
            backdrop-filter: saturate(1.4) blur(10px);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; gap: 1rem;
            padding: 0 2rem;
        }
        .adm-icon-btn {
            display: none;
            background: var(--surface); border: 1px solid var(--border);
            width: 40px; height: 40px; border-radius: 10px;
            place-items: center; cursor: pointer; color: var(--text);
        }
        .adm-topbar-title { font-size: 1rem; font-weight: 700; letter-spacing: -.01em; margin: 0; flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .adm-site-link {
            display: inline-flex; align-items: center; gap: .4rem;
            text-decoration: none; font-size: .8rem; font-weight: 600; color: var(--muted);
            padding: .5rem .85rem; border: 1px solid var(--border); border-radius: 10px; background: var(--surface);
            transition: color .15s, border-color .15s;
        }
        .adm-site-link:hover { color: var(--text); border-color: var(--accent); }

        .adm-content { flex: 1; padding: 2rem; width: 100%; max-width: 1400px; margin: 0 auto; }

        .adm-footer {
            padding: 1.25rem 2rem; border-top: 1px solid var(--border);
            display: flex; flex-wrap: wrap; gap: .5rem 1rem; justify-content: space-between;
            font-size: .75rem; color: var(--muted);
        }

        /* Flash */
        .adm-flash { display: flex; align-items: flex-start; gap: .65rem; padding: .85rem 1rem; border-radius: 12px; font-size: .85rem; font-weight: 500; margin-bottom: 1.25rem; border: 1px solid; }
        .adm-flash svg { flex-shrink: 0; margin-top: 1px; }
        .adm-flash.success { background: #F0FAF4; border-color: #BFE5CE; color: #1D6B43; }
        .adm-flash.error { background: #FEF2F3; border-color: #F6CBD1; color: #A3182F; }

        /* ============ Composants partagés (compatibles avec vos vues) ============ */
        .admin-stat-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
            padding: 1.25rem 1.5rem; box-shadow: var(--shadow);
            display: flex; flex-direction: column; justify-content: space-between;
            transition: box-shadow .2s, transform .2s;
        }
        .admin-stat-card:hover { transform: translateY(-1px); box-shadow: 0 6px 22px rgba(20, 18, 12, .08); }

        .admin-table-container {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius);
            overflow-x: auto; -webkit-overflow-scrolling: touch; box-shadow: var(--shadow);
        }
        .admin-table { width: 100%; border-collapse: collapse; text-align: left; font-size: .85rem; min-width: 640px; }
        .admin-table thead { background: var(--surface-alt); color: var(--muted); font-size: .7rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; border-bottom: 1px solid var(--border); }
        .admin-table th, .admin-table td { padding: .9rem 1.25rem; vertical-align: middle; }
        .admin-table tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
        .admin-table tbody tr:last-child { border-bottom: 0; }
        .admin-table tbody tr:hover { background: var(--surface-alt); }

        .admin-btn-primary, .admin-btn-secondary {
            display: inline-flex; align-items: center; justify-content: center; gap: .45rem;
            border-radius: 10px; font-family: inherit; font-weight: 600; text-decoration: none; cursor: pointer;
            transition: background .15s, border-color .15s, color .15s, transform .15s;
            min-height: 40px;
        }
        .admin-btn-primary { background: #1F1E1B; color: #fff; border: 1px solid #1F1E1B; padding: .6rem 1.15rem; font-size: .83rem; }
        .admin-btn-primary:hover { background: #34322D; transform: translateY(-1px); }
        .admin-btn-secondary { background: var(--surface); color: var(--text); border: 1px solid var(--border); padding: .55rem 1rem; font-size: .83rem; }
        .admin-btn-secondary:hover { border-color: var(--accent); background: var(--accent-soft); }

        .admin-badge { display: inline-flex; align-items: center; padding: .2rem .65rem; border-radius: 999px; font-size: .72rem; font-weight: 600; }
        .admin-badge-gold { background: var(--accent-soft); color: var(--accent-ink); }
        .admin-badge-green { background: rgba(47, 111, 78, .12); color: #2F6F4E; }
        .admin-badge-red { background: rgba(225, 29, 72, .1); color: #BE123C; }
        .admin-badge-blue { background: rgba(30, 58, 138, .1); color: #1E3A8A; }
        .admin-badge-purple, .badge-purple { background: rgba(142, 68, 173, .12); color: #6C3483; }
        .admin-badge-dark, .badge-dark { background: rgba(31, 30, 27, .08); color: #1F1E1B; }

        .font-serif-title { font-family: 'Cinzel', Georgia, serif; }

        /* ============ Responsive ============ */
        @media (max-width: 1024px) {
            .adm-sidebar { transform: translateX(-100%); box-shadow: none; }
            body.nav-open .adm-sidebar { transform: translateX(0); box-shadow: 0 0 40px rgba(0, 0, 0, .35); }
            body.nav-open .adm-overlay { opacity: 1; visibility: visible; }
            body.nav-open { overflow: hidden; }
            .adm-main { margin-left: 0; }
            .adm-icon-btn { display: grid; }
            .adm-topbar { padding: 0 1.25rem; }
            .adm-content { padding: 1.5rem 1.25rem; }
            .adm-footer { padding: 1.25rem; }
        }

        /* Transformation des Tableaux Administrateur en Cartes sur Mobile */
        @media (max-width: 768px) {
            .admin-table-container {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
                padding: 0 !important;
                border-radius: 0 !important;
            }
            .admin-table-container > div {
                overflow-x: visible !important;
            }
            .stack-table,
            .admin-table {
                min-width: 0 !important;
                width: 100% !important;
                border-collapse: separate !important;
                border-spacing: 0 !important;
                background: transparent !important;
                display: block !important;
            }
            .stack-table thead,
            .admin-table thead {
                display: none !important;
            }
            .stack-table tbody,
            .admin-table tbody {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                gap: 1.15rem !important;
            }
            .stack-table tbody tr,
            .admin-table tbody tr {
                display: flex !important;
                flex-direction: column !important;
                width: 100% !important;
                background: var(--surface) !important;
                border: 1px solid var(--border) !important;
                border-radius: 18px !important;
                box-shadow: 0 4px 16px rgba(20, 18, 12, 0.05) !important;
                padding: 1.25rem 1.25rem !important;
                gap: 0.65rem !important;
                box-sizing: border-box !important;
                transition: transform .2s ease, box-shadow .2s ease !important;
            }
            .stack-table tbody tr:hover,
            .admin-table tbody tr:hover {
                background: var(--surface) !important;
                border-color: rgba(201, 160, 78, 0.45) !important;
                box-shadow: 0 6px 20px rgba(20, 18, 12, 0.08) !important;
            }
            .stack-table td,
            .admin-table td {
                display: flex !important;
                justify-content: space-between !important;
                align-items: center !important;
                gap: 0.85rem !important;
                padding: 0.45rem 0 !important;
                border: none !important;
                text-align: right !important;
                font-size: 0.86rem !important;
                width: 100% !important;
                box-sizing: border-box !important;
                word-break: break-word !important;
            }
            .stack-table td::before,
            .admin-table td::before {
                content: attr(data-label);
                font-size: 0.72rem !important;
                font-weight: 700 !important;
                letter-spacing: 0.06em !important;
                text-transform: uppercase !important;
                color: var(--muted) !important;
                text-align: left !important;
                flex-shrink: 0 !important;
                max-width: 45% !important;
            }
            .stack-table td.cell-main,
            .admin-table td.cell-main {
                display: block !important;
                padding-bottom: 0.85rem !important;
                margin-bottom: 0.25rem !important;
                border-bottom: 1px solid var(--border) !important;
                text-align: left !important;
                width: 100% !important;
            }
            .stack-table td.cell-main::before,
            .admin-table td.cell-main::before {
                content: none !important;
            }
            .stack-table td.cell-actions,
            .admin-table td.cell-actions {
                display: flex !important;
                padding-top: 0.85rem !important;
                margin-top: 0.25rem !important;
                border-top: 1px solid var(--border) !important;
                justify-content: flex-end !important;
                align-items: center !important;
                width: 100% !important;
                gap: 0.5rem !important;
                flex-wrap: wrap !important;
            }
            .stack-table td.cell-actions::before,
            .admin-table td.cell-actions::before {
                content: none !important;
            }
            .stack-table td[colspan],
            .admin-table td[colspan] {
                display: block !important;
                text-align: center !important;
                padding: 2.5rem 1rem !important;
                color: var(--muted) !important;
                border: none !important;
            }
            .stack-table td[colspan]::before,
            .admin-table td[colspan]::before {
                content: none !important;
            }
            .admin-pagination-box {
                background: var(--surface) !important;
                border: 1px solid var(--border) !important;
                border-radius: 16px !important;
                box-shadow: 0 2px 8px rgba(20, 18, 12, 0.04) !important;
            }
        }

        @media (max-width: 640px) {
            :root { --topbar-h: 58px; }
            .adm-sidebar { width: min(86vw, 300px); }
            .adm-content { padding: 1.15rem .9rem 1.5rem; }
            .adm-site-link span { display: none; }
            .adm-site-link { padding: .5rem; }
            .admin-stat-card { padding: 1.1rem; }
            .adm-footer { flex-direction: column; text-align: center; align-items: center; }
        }
        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>
<div class="adm-shell">

    <!-- Sidebar -->
    <aside class="adm-sidebar" id="adm-sidebar" aria-label="Navigation Administration">
        <a href="{{ route('admin.dashboard') }}" class="adm-brand" title="Administration L'Étoile du Matin">
            <span class="adm-brand-mark">★</span>
            <span class="adm-brand-name">L'ÉTOILE DU MATIN<span class="adm-brand-sub">Administration</span></span>
        </a>

        <nav class="adm-nav">
            <div class="adm-nav-label">Menu</div>

            <a href="{{ route('admin.dashboard') }}" class="adm-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>
                <span>Tableau de bord</span>
            </a>
            <a href="{{ route('admin.books.index') }}" class="adm-link {{ request()->routeIs('admin.books.*') ? 'active' : '' }}" @if(request()->routeIs('admin.books.*')) aria-current="page" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                <span>Catalogue livres</span>
            </a>
            <a href="{{ route('admin.orders.index') }}" class="adm-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" @if(request()->routeIs('admin.orders.*')) aria-current="page" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                <span>Commandes</span>
            </a>
            <a href="{{ route('admin.reviews.index') }}" class="adm-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}" @if(request()->routeIs('admin.reviews.*')) aria-current="page" @endif>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                <span>Avis clients</span>
            </a>
        </nav>

        <div class="adm-user">
            <div class="adm-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="adm-user-info">
                <div class="adm-user-name">{{ auth()->user()->name }}</div>
                <div class="adm-user-role">Administrateur</div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="adm-logout" title="Se déconnecter" aria-label="Se déconnecter">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <div class="adm-overlay" id="adm-overlay" aria-hidden="true"></div>

    <!-- Zone principale -->
    <div class="adm-main">
        <header class="adm-topbar" role="banner">
            <button type="button" class="adm-icon-btn" id="adm-toggle" aria-label="Ouvrir le menu" aria-controls="adm-sidebar" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <h1 class="adm-topbar-title">@yield('title', 'Administration')</h1>

            <a href="{{ url('/') }}" class="adm-site-link" target="_blank" rel="noopener">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                <span>Voir le site</span>
            </a>
        </header>

        <main class="adm-content" role="main">
            @if(session('success'))
                <div class="adm-flash success" role="status">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="adm-flash error" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="adm-footer">
            <span>© {{ date('Y') }} Éditions L'Étoile du Matin</span>
            <span>Panneau d'administration &bull; Accès sécurisé</span>
        </footer>
    </div>
</div>

<script>
    (function () {
        var body = document.body;
        var toggle = document.getElementById('adm-toggle');
        var overlay = document.getElementById('adm-overlay');

        function setOpen(open) {
            body.classList.toggle('nav-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
        }

        toggle.addEventListener('click', function () { setOpen(!body.classList.contains('nav-open')); });
        overlay.addEventListener('click', function () { setOpen(false); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });
        window.addEventListener('resize', function () { if (window.innerWidth > 1024) setOpen(false); });
    })();
</script>

@stack('scripts')
</body>
</html>