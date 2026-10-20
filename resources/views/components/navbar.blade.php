@php
    $cartService = app(\App\Services\CartService::class);
    $cartCount = $cartService->getCart()['count'] ?? 0;
    $isHome = request()->routeIs('home') || request()->routeIs('books.index');
    $isCatalog = request()->routeIs('catalog.*');
    $isLibrary = request()->routeIs('library.*');
    $isCart = request()->routeIs('cart.*');
@endphp

<div x-data="{ mobileMenuOpen: false, searchModalOpen: false, userMenuOpen: false, moreMenuOpen: false }" 
     @keydown.window.escape="mobileMenuOpen = false; searchModalOpen = false; userMenuOpen = false; moreMenuOpen = false">

    <!-- =========================================================================
       1. NAVIGATION DESKTOP : barre latérale d'icônes + barre supérieure
       ========================================================================= -->
    @php
        $railItems = [
            ['url' => route('home'), 'active' => $isHome, 'label' => 'Accueil', 'icon' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1z"/>'],
            ['url' => route('catalog.index'), 'active' => $isCatalog, 'label' => 'Catalogue', 'icon' => '<path d="M5 3h11a3 3 0 0 1 3 3v15H8a3 3 0 0 1-3-3z"/><path d="M9 3v18"/>'],
            ['url' => route('catalog.index', ['format' => 'audio']), 'active' => false, 'label' => 'Livres audio', 'icon' => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><rect x="3" y="14" width="4" height="7" rx="1.5"/><rect x="17" y="14" width="4" height="7" rx="1.5"/>'],
            ['url' => auth()->check() ? route('library.index') : route('login'), 'active' => $isLibrary, 'label' => 'Ma bibliothèque', 'icon' => '<path d="M6 3h12v18l-6-4-6 4z"/>'],
            ['url' => route('orders.lookup'), 'active' => request()->routeIs('orders.lookup'), 'label' => 'Suivi de commande', 'icon' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>'],
        ];
    @endphp

    <aside class="site-rail" aria-label="Navigation principale">
        <a href="{{ route('home') }}" class="site-rail-logo" aria-label="Accueil NSOROMMA">
            <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" width="34" height="34">
        </a>

        <nav class="site-rail-nav">
            @foreach($railItems as $item)
                <a href="{{ $item['url'] }}" class="site-rail-link {{ $item['active'] ? 'is-active' : '' }}" aria-label="{{ $item['label'] }}" @if($item['active']) aria-current="page" @endif>
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $item['icon'] !!}</svg>
                    <span class="site-rail-tip">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="relative site-rail-bottom" @click.outside="moreMenuOpen = false">
            <button type="button" class="site-rail-menu" @click="moreMenuOpen = !moreMenuOpen" aria-label="Plus d'options" :aria-expanded="moreMenuOpen.toString()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="16" y2="12"/><line x1="4" y1="17" x2="12" y2="17"/></svg>
            </button>
            <div x-show="moreMenuOpen" x-cloak x-transition.opacity.duration.150ms class="nav-dropdown site-rail-dropdown" style="display:none;">
                <a href="{{ route('catalog.index', ['format' => 'pdf']) }}" class="nav-dropdown-item">e-Books PDF</a>
                <a href="{{ route('catalog.index', ['format' => 'physical']) }}" class="nav-dropdown-item">Livres papier</a>
                <a href="{{ route('catalog.index', ['format' => 'audio']) }}" class="nav-dropdown-item">Livres audio</a>
                <div class="nav-dropdown-divider"></div>
                <a href="{{ route('orders.lookup') }}" class="nav-dropdown-item">Suivi de commande</a>
                <a href="https://wa.me/2250700000001" target="_blank" rel="noopener" class="nav-dropdown-item">Assistance WhatsApp</a>
            </div>
        </div>
    </aside>

    <header class="site-topbar" role="banner">
        <form action="{{ route('catalog.index') }}" method="GET" class="site-search" role="search">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="search" value="{{ request()->routeIs('catalog.index') ? request('search') : '' }}" placeholder="Rechercher un livre, un auteur, un thème…" aria-label="Rechercher un livre">
        </form>

        <div class="site-topbar-actions">
            <a href="{{ route('cart.index') }}" class="site-icon-btn {{ $isCart ? 'is-active' : '' }}" aria-label="Panier{{ $cartCount > 0 ? ' ('.$cartCount.')' : '' }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
                <span class="cart-badge site-cart-badge" id="cart-counter" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
            </a>

            <div class="relative" @click.outside="userMenuOpen = false">
                @auth
                    <button type="button" class="site-user" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen.toString()">
                        <span class="site-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="site-user-name">{{ auth()->user()->name }}</span>
                    </button>
                @else
                    <button type="button" class="site-user" @click="userMenuOpen = !userMenuOpen" :aria-expanded="userMenuOpen.toString()">
                        <span class="site-user-avatar" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-6 8-6s8 2 8 6"/></svg>
                        </span>
                        <span class="site-user-name">Se connecter</span>
                    </button>
                @endauth

                <div x-show="userMenuOpen" x-cloak x-transition.opacity.duration.150ms class="nav-dropdown" style="display:none;">
                    @auth
                        <div class="site-dropdown-head">
                            <div class="site-dropdown-name">{{ auth()->user()->name }}</div>
                            <div class="site-dropdown-email">{{ auth()->user()->email }}</div>
                        </div>
                        <a href="{{ route('library.index') }}" class="nav-dropdown-item">Ma bibliothèque</a>
                        @if(auth()->user()->isStaff())
                            <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item">Administration</a>
                        @endif
                        <div class="nav-dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-dropdown-item site-dropdown-danger">Déconnexion</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="nav-dropdown-item">Se connecter</a>
                        <a href="{{ route('register') }}" class="nav-dropdown-item">Créer un compte</a>
                        <div class="nav-dropdown-divider"></div>
                        <a href="{{ route('orders.lookup') }}" class="nav-dropdown-item">Suivi de commande sans compte</a>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- =========================================================================
       2. TOPBAR MOBILE (< 768px) Épurée, Rapide & Tactile
       ========================================================================= -->
    <div class="mobile-topbar" role="navigation" aria-label="Navigation mobile">
        
        <!-- Hamburger Menu Button -->
        <button type="button" 
                class="mobile-menu-btn" 
                @click="mobileMenuOpen = true" 
                aria-label="Ouvrir le menu de navigation">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>

        <!-- Brand Logo Centré -->
        <a href="{{ route('home') }}" class="logo-text" aria-label="Accueil NSOROMMA">
            <x-brand-logo :size="28" name-size="1rem" />
        </a>

        <!-- Actions Droite Mobile : Recherche & Panier -->
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" 
                    class="action-btn" 
                    style="width: 38px; height: 38px;"
                    @click="searchModalOpen = true; $nextTick(() => $refs.searchInputModal.focus())" 
                    aria-label="Recherche">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
            </button>

            <a href="{{ route('cart.index') }}" 
               class="action-btn" 
               style="width: 38px; height: 38px;" 
               aria-label="Panier">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                    <path d="M3 6h18"></path>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span class="cart-badge" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
            </a>
        </div>

    </div>

    <!-- =========================================================================
       3. TIROIR LATÉRAL MOBILE (SLIDE-OVER DRAWER)
       ========================================================================= -->
    <div x-show="mobileMenuOpen" 
         x-cloak 
         class="mobile-drawer-backdrop" 
         @click="mobileMenuOpen = false"
         x-transition:enter="transition-opacity ease-out duration-250"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <div x-show="mobileMenuOpen" 
         x-cloak 
         class="mobile-drawer-panel"
         x-transition:enter="transition ease-out duration-250 transform"
         x-transition:enter-start="-translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-200 transform"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="-translate-x-full">
        
        <!-- Header du Tiroir -->
        <div class="mobile-drawer-header">
            <x-brand-logo :size="30" name-size="1.05rem" />
            <button type="button" 
                    @click="mobileMenuOpen = false" 
                    style="background: #FAF5ED; border: none; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #1E1D1B; cursor: pointer;"
                    aria-label="Fermer le menu">
                ✕
            </button>
        </div>

        <!-- Corps du Tiroir -->
        <div class="mobile-drawer-body">
            
            <!-- Recherche rapide dans le tiroir -->
            <form action="{{ route('catalog.index') }}" method="GET" style="position: relative;">
                <input type="text" 
                       name="search" 
                       placeholder="Rechercher titre, auteur..." 
                       style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 30px; padding: 0.65rem 2.2rem 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                <button type="submit" style="position: absolute; right: 0.85rem; top: 50%; transform: translateY(-50%); background: none; border: none; color: #8B857C; cursor: pointer;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                </button>
            </form>

            <!-- Navigation Principale -->
            <div>
                <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #D9AE5C; display: block; margin-bottom: 0.5rem; padding-left: 0.5rem;">
                    Navigation
                </span>
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <a href="{{ route('home') }}" class="mobile-drawer-navlink {{ $isHome ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                        <span>Accueil &amp; Nouveautés</span>
                    </a>
                    <a href="{{ route('catalog.index') }}" class="mobile-drawer-navlink {{ $isCatalog ? 'active' : '' }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        <span>Tout le Catalogue</span>
                    </a>
                    <a href="{{ route('home') . '#audio' }}" class="mobile-drawer-navlink">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"></path></svg>
                        <span>Livres Audio</span>
                    </a>
                </div>
            </div>

            <!-- Collections Africaines -->
            <div>
                <span style="font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #D9AE5C; display: block; margin-bottom: 0.5rem; padding-left: 0.5rem;">
                    Collections Thématiques
                </span>
                <div style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <a href="{{ route('catalog.index', ['category' => 'spiritualite-africaine']) }}" class="mobile-drawer-navlink" style="font-size: 0.85rem; font-weight: 600;">
                        <span>✨ Spiritualité africaine</span>
                    </a>
                    <a href="{{ route('catalog.index', ['category' => 'philosophie']) }}" class="mobile-drawer-navlink" style="font-size: 0.85rem; font-weight: 600;">
                        <span>📜 Philosophie &amp; Pensée</span>
                    </a>
                    <a href="{{ route('catalog.index', ['category' => 'politique-panafricanisme']) }}" class="mobile-drawer-navlink" style="font-size: 0.85rem; font-weight: 600;">
                        <span>🏛️ Politique &amp; Panafricanisme</span>
                    </a>
                    <a href="{{ route('catalog.index', ['category' => 'religion']) }}" class="mobile-drawer-navlink" style="font-size: 0.85rem; font-weight: 600;">
                        <span>⚖️ Textes sacrés &amp; Religions</span>
                    </a>
                </div>
            </div>

            <!-- Services & Assistance -->
            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid rgba(139,133,124,0.12);">
                <div style="display: flex; flex-direction: column; gap: 0.35rem;">
                    <a href="{{ route('library.index') }}" class="mobile-drawer-navlink" style="font-size: 0.85rem;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path></svg>
                        <span>Ma Bibliothèque Numérique</span>
                    </a>
                    <a href="{{ route('orders.lookup') }}" class="mobile-drawer-navlink" style="font-size: 0.85rem;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span>Suivi de Colis sans compte</span>
                    </a>
                    <a href="https://wa.me/2250700000001" target="_blank" class="mobile-drawer-navlink" style="font-size: 0.85rem; color: #2F6F4E;">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>Assistance WhatsApp</span>
                    </a>
                </div>

                <!-- Connexion / Compte -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(139,133,124,0.12);">
                    @auth
                        <div style="margin-bottom: 0.75rem;">
                            <div style="font-weight: 800; font-size: 0.9rem; color: #1E1D1B;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 0.75rem; color: #8B857C;">{{ auth()->user()->email }}</div>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" style="width: 100%; padding: 0.65rem; background: #FAF5ED; border: 1px solid rgba(225,29,72,0.2); border-radius: 12px; color: #BE123C; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                                Déconnexion
                            </button>
                        </form>
                    @else
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                            <a href="{{ route('login') }}" style="text-align: center; padding: 0.65rem; background: #191919; color: #D9AE5C; border-radius: 12px; font-weight: 800; font-size: 0.82rem; text-decoration: none;">
                                Connexion
                            </a>
                            <a href="{{ route('register') }}" style="text-align: center; padding: 0.65rem; background: #FAF5ED; border: 1px solid rgba(217,174,92,0.3); color: #1E1D1B; border-radius: 12px; font-weight: 700; font-size: 0.82rem; text-decoration: none;">
                                Inscription
                            </a>
                        </div>
                    @endauth
                </div>

            </div>

        </div>

    </div>

    <!-- =========================================================================
       4. BARRE DE NAVIGATION INFÉRIEURE FLOTTANTE (MOBILE < 768px)
       ========================================================================= -->
    <nav class="mobile-bottom-bar" aria-label="Navigation rapide mobile">
        <div class="tabbar-pill">
            <a href="{{ route('home') }}" class="tabbar-item {{ $isHome ? 'active' : '' }}" aria-label="Accueil" @if($isHome) aria-current="page" @endif>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M11.3 2.6a1.1 1.1 0 0 1 1.4 0l8 6.6c.2.2.4.5.4.8V20a1.5 1.5 0 0 1-1.5 1.5H15a.5.5 0 0 1-.5-.5v-5a1.5 1.5 0 0 0-1.5-1.5h-2A1.5 1.5 0 0 0 9.5 16v5a.5.5 0 0 1-.5.5H4.4A1.5 1.5 0 0 1 2.9 20v-10c0-.3.1-.6.4-.8z"/></svg>
            </a>

            <a href="{{ route('catalog.index') }}" class="tabbar-item {{ $isCatalog ? 'active' : '' }}" aria-label="Catalogue" @if($isCatalog) aria-current="page" @endif>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect x="3" y="3" width="8" height="8" rx="2.2"/><rect x="13" y="3" width="8" height="8" rx="2.2"/><rect x="3" y="13" width="8" height="8" rx="2.2"/><rect x="13" y="13" width="8" height="8" rx="2.2"/></svg>
            </a>

            <a href="{{ route('cart.index') }}" class="tabbar-item {{ $isCart ? 'active' : '' }}" aria-label="Panier{{ $cartCount > 0 ? ' ('.$cartCount.' article'.($cartCount > 1 ? 's' : '').')' : '' }}" @if($isCart) aria-current="page" @endif>
                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7.5 7V6.5a4.5 4.5 0 0 1 9 0V7h2.1c.8 0 1.4.6 1.5 1.4l.9 11.2a1.8 1.8 0 0 1-1.8 1.9H4.8A1.8 1.8 0 0 1 3 19.6l.9-11.2C4 7.6 4.6 7 5.4 7zm2 0h5v-.5a2.5 2.5 0 0 0-5 0z"/></svg>
                @if($cartCount > 0)
                    <span class="tabbar-badge" aria-hidden="true">{{ $cartCount }}</span>
                @endif
            </a>

            @auth
                <a href="{{ route('library.index') }}" class="tabbar-item {{ $isLibrary ? 'active' : '' }}" aria-label="Ma bibliothèque" @if($isLibrary) aria-current="page" @endif>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="7.5" r="4.5"/><path d="M3.5 20.2c0-3.6 3.8-6.2 8.5-6.2s8.5 2.6 8.5 6.2c0 .7-.6 1.3-1.3 1.3H4.8c-.7 0-1.3-.6-1.3-1.3"/></svg>
                </a>
            @else
                <a href="{{ route('login') }}" class="tabbar-item {{ request()->routeIs('login', 'register') ? 'active' : '' }}" aria-label="Connexion" @if(request()->routeIs('login', 'register')) aria-current="page" @endif>
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><circle cx="12" cy="7.5" r="4.5"/><path d="M3.5 20.2c0-3.6 3.8-6.2 8.5-6.2s8.5 2.6 8.5 6.2c0 .7-.6 1.3-1.3 1.3H4.8c-.7 0-1.3-.6-1.3-1.3"/></svg>
                </a>
            @endauth
        </div>

        <button type="button" class="tabbar-fab" aria-label="Rechercher un livre"
                @click="searchModalOpen = true; $nextTick(() => $refs.searchInputModal.focus())">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="20" y1="20" x2="15.4" y2="15.4"/></svg>
        </button>
    </nav>

    <!-- =========================================================================
       5. MODAL DE RECHERCHE UNIVERSELLE RAPIDE (Desktop & Mobile)
       ========================================================================= -->
    <div x-show="searchModalOpen" 
         x-cloak 
         class="mobile-drawer-backdrop" 
         @click="searchModalOpen = false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <div x-show="searchModalOpen" 
         x-cloak 
         style="position: fixed; top: 12%; left: 50%; transform: translateX(-50%); width: 90%; max-width: 640px; background: #FFFDF9; border-radius: 24px; border: 1.5px solid rgba(217,174,92,0.3); box-shadow: 0 25px 60px rgba(0,0,0,0.35); z-index: 1000; overflow: hidden; display: flex; flex-direction: column;"
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95">
        
        <!-- Formulaire de recherche dans la modale -->
        <form action="{{ route('catalog.index') }}" method="GET" style="display: flex; align-items: center; padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(139,133,124,0.12); gap: 0.75rem;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" style="color: #D9AE5C; flex-shrink: 0;"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
            <input type="text" 
                   name="search" 
                   x-ref="searchInputModal"
                   placeholder="Rechercher un ouvrage, un auteur (ex: Cheikh Anta Diop)..." 
                   style="flex: 1; border: none; background: transparent; font-size: 1.05rem; color: #1E1D1B; font-weight: 600; outline: none;">
            <button type="button" 
                    @click="searchModalOpen = false" 
                    style="background: #FAF5ED; border: none; width: 32px; height: 32px; border-radius: 50%; color: #8B857C; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                ✕
            </button>
        </form>

        <!-- Suggestions de Recherche Rapide -->
        <div style="padding: 1.25rem 1.5rem; background: #FAF5ED;">
            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: #767067; display: block; margin-bottom: 0.65rem;">
                Recherches suggérées
            </span>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="{{ route('catalog.index', ['search' => 'Cheikh Anta Diop']) }}" style="padding: 0.4rem 0.85rem; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.25); border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; text-decoration: none;">
                    Cheikh Anta Diop
                </a>
                <a href="{{ route('catalog.index', ['category' => 'spiritualite-africaine']) }}" style="padding: 0.4rem 0.85rem; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.25); border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; text-decoration: none;">
                    Spiritualité africaine
                </a>
                <a href="{{ route('catalog.index', ['format' => 'pdf']) }}" style="padding: 0.4rem 0.85rem; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.25); border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; text-decoration: none;">
                    e-Books PDF
                </a>
                <a href="{{ route('catalog.index', ['category' => 'politique-panafricanisme']) }}" style="padding: 0.4rem 0.85rem; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.25); border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; text-decoration: none;">
                    Panafricanisme
                </a>
            </div>
        </div>

    </div>

</div>
