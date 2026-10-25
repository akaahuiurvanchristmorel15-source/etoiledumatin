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

    <style>
        .font-serif-title { font-family: 'Cinzel', Georgia, serif; }
        .font-editorial { font-family: 'Playfair Display', Georgia, serif; }
        body { font-family: 'Plus Jakarta Sans', system-ui, sans-serif; }
    </style>
</head>
<body class="@yield('body_class', 'broks-page')" @yield('body_attributes', '')>

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
                <a href="https://wa.me/2250700000001">Assistance</a>
            </nav>
        </footer>
    </main>

    @stack('scripts')
</body>
</html>
