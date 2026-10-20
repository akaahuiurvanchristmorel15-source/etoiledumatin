<!DOCTYPE html>
<html lang="fr" class="h-full bg-stone-50 text-stone-900 antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Librairie NSOROMMA — Spiritualité, Philosophie & Savoirs Africains')</title>
    <meta name="description" content="@yield('meta_description', 'Librairie en ligne panafricaine spécialisée en spiritualité africaine, philosophie, religion et politique. Achat de livres en téléchargement PDF immédiat ou livraison de livres physiques.')">

    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">

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

        <!-- 2. Messages Flash & Alertes Interactifs -->
        @if(session('success'))
            <div class="px-4 sm:px-8 pt-4" x-data="{ show: true }" x-show="show" x-transition.duration.300ms>
                <div class="rounded-2xl bg-emerald-50/90 border border-emerald-200/80 p-4 text-emerald-900 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0 text-emerald-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-emerald-700">Confirmation</p>
                            <p class="text-sm font-medium">{{ session('success') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-emerald-500 hover:text-emerald-800 p-1.5 rounded-lg transition-colors" aria-label="Fermer la notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="px-4 sm:px-8 pt-4" x-data="{ show: true }" x-show="show" x-transition.duration.300ms>
                <div class="rounded-2xl bg-rose-50/90 border border-rose-200/80 p-4 text-rose-900 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0 text-rose-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-rose-700">Attention</p>
                            <p class="text-sm font-medium">{{ session('error') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-rose-500 hover:text-rose-800 p-1.5 rounded-lg transition-colors" aria-label="Fermer la notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('info'))
            <div class="px-4 sm:px-8 pt-4" x-data="{ show: true }" x-show="show" x-transition.duration.300ms>
                <div class="rounded-2xl bg-amber-50/90 border border-amber-200/80 p-4 text-amber-900 flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0 text-amber-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Information</p>
                            <p class="text-sm font-medium">{{ session('info') }}</p>
                        </div>
                    </div>
                    <button type="button" @click="show = false" class="text-amber-500 hover:text-amber-800 p-1.5 rounded-lg transition-colors" aria-label="Fermer la notification">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

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
                <a href="{{ route('orders.lookup') }}">Suivi de commande</a>
                <a href="https://wa.me/2250700000001" target="_blank" rel="noopener">Assistance</a>
            </nav>
        </footer>
    </main>

    @stack('scripts')
</body>
</html>
