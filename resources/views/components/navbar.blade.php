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
       1. NAVBAR DESKTOP & TABLETTE (Aesthetic Signature BROKS Épuré)
       ========================================================================= -->
    <header class="broks-navbar" role="banner">
        
        <!-- Logo de Prestige -->
        <a href="{{ route('home') }}" class="logo-text" aria-label="Accueil NSOROMMA">
            <x-brand-logo :size="34" name-size="1.15rem" />
        </a>

        <!-- Onglets Centraux (Livre, Livre Audio, Catalogue) -->
        <nav class="nav-tabs" role="tablist" aria-label="Navigation principale">
            <a href="{{ route('home') }}" 
               class="nav-tab {{ $isHome ? 'active' : '' }}" 
               role="tab" 
               aria-selected="{{ $isHome ? 'true' : 'false' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"></path>
                    <path d="M6 6h10"></path>
                    <path d="M6 10h10"></path>
                </svg>
                <span>Livre</span>
            </a>

            <a href="{{ $isHome ? '#audio' : route('home') . '#audio' }}" 
               class="nav-tab" 
               role="tab" 
               aria-selected="false">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"></path>
                </svg>
                <span>Livre Audio</span>
            </a>

            <a href="{{ route('catalog.index') }}" 
               class="nav-tab {{ $isCatalog ? 'active' : '' }}" 
               role="tab" 
               aria-selected="{{ $isCatalog ? 'true' : 'false' }}">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"></rect>
                    <rect x="14" y="3" width="7" height="7"></rect>
                    <rect x="14" y="14" width="7" height="7"></rect>
                    <rect x="3" y="14" width="7" height="7"></rect>
                </svg>
                <span>Catalogue</span>
            </a>
        </nav>

        <!-- Actions Rapides Droite -->
        <div class="nav-actions">
            
            <!-- Bouton Recherche Rapide Modal -->
            <button type="button" 
                    class="action-btn" 
                    @click="searchModalOpen = true; $nextTick(() => $refs.searchInputModal.focus())" 
                    aria-label="Rechercher un livre" 
                    title="Recherche instantanée">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="m21 21-4.3-4.3"></path>
                </svg>
            </button>

            <!-- Bouton Panier avec Badge Interactif -->
            <a href="{{ route('cart.index') }}" 
               class="action-btn" 
               aria-label="Voir mon panier d'achat" 
               title="Panier">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"></path>
                    <path d="M3 6h18"></path>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <span class="cart-badge" id="cart-counter" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">{{ $cartCount }}</span>
            </a>

            <!-- Avatar / Compte Utilisateur avec Menu Déroulant -->
            <div class="relative" @click.outside="userMenuOpen = false">
                <button type="button" 
                        class="avatar-circle" 
                        @click="userMenuOpen = !userMenuOpen" 
                        aria-label="Espace Lecteur" 
                        title="{{ auth()->check() ? auth()->user()->name : 'Mon Compte' }}">
                    <span>{{ auth()->check() ? strtoupper(substr(auth()->user()->name, 0, 2)) : '★' }}</span>
                </button>

                <!-- Dropdown Utilisateur -->
                <div x-show="userMenuOpen" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="nav-dropdown"
                     style="display:none;">
                    
                    @auth
                        <div style="padding: 0.65rem 1rem 0.75rem; border-bottom: 1px solid rgba(139,133,124,0.12);">
                            <div style="font-weight: 800; font-size: 0.9rem; color: #1E1D1B;">{{ auth()->user()->name }}</div>
                            <div style="font-size: 0.75rem; color: #8B857C;">{{ auth()->user()->email }}</div>
                            <div style="font-size: 0.68rem; font-weight: 800; color: #D9AE5C; text-transform: uppercase; margin-top: 2px;">
                                Lecteur Privilégié
                            </div>
                        </div>

                        <a href="{{ route('library.index') }}" class="nav-dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            <span>Ma Bibliothèque (e-Books)</span>
                        </a>

                        @if(auth()->user()->isAdmin() || auth()->user()->isManager() || auth()->user()->isLogistics())
                            <a href="{{ route('admin.dashboard') }}" class="nav-dropdown-item" style="color:#B3862A; font-weight: 700;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/><path d="m14 9 3 3-3 3"/></svg>
                                <span>Panneau d'Administration</span>
                            </a>
                        @endif

                        <div class="nav-dropdown-divider"></div>

                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="nav-dropdown-item" style="color: #BE123C;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span>Déconnexion</span>
                            </button>
                        </form>
                    @else
                        <div style="padding: 0.5rem 0.75rem 0.75rem; text-align: center;">
                            <a href="{{ route('login') }}" class="nav-dropdown-item" style="justify-content: center; background: #191919; color: #D9AE5C; border-radius: 12px; margin-bottom: 0.4rem; font-weight: 800;">
                                <span>Se connecter</span>
                            </a>
                            <a href="{{ route('register') }}" class="nav-dropdown-item" style="justify-content: center; background: #FAF5ED; border: 1px solid rgba(217,174,92,0.3); border-radius: 12px; font-weight: 700;">
                                <span>Créer un compte</span>
                            </a>
                        </div>
                        <div class="nav-dropdown-divider"></div>
                        <a href="{{ route('orders.lookup') }}" class="nav-dropdown-item" style="font-size: 0.8rem; color: #767067;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <span>Suivi de commande sans compte</span>
                        </a>
                    @endauth

                </div>
            </div>

            <!-- Menu d'Options Complémentaires (⋮) -->
            <div class="relative" @click.outside="moreMenuOpen = false">
                <button type="button" 
                        class="action-btn" 
                        @click="moreMenuOpen = !moreMenuOpen" 
                        aria-label="Options complémentaires" 
                        title="Plus d'options">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </button>

                <!-- Dropdown Options -->
                <div x-show="moreMenuOpen" 
                     x-cloak 
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="nav-dropdown"
                     style="display:none; width: 260px;">
                    
                    <a href="{{ route('catalog.index', ['format' => 'pdf']) }}" class="nav-dropdown-item">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #2F6F4E;"></span>
                        <span>Catalogue e-Books (PDF)</span>
                    </a>

                    <a href="{{ route('catalog.index', ['format' => 'physical']) }}" class="nav-dropdown-item">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: #D9AE5C;"></span>
                        <span>Catalogue Livres Papier</span>
                    </a>

                    <a href="{{ route('orders.lookup') }}" class="nav-dropdown-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                        <span>Suivi de Colis &amp; Expédition</span>
                    </a>

                    <div class="nav-dropdown-divider"></div>

                    <a href="https://wa.me/2250700000001" target="_blank" class="nav-dropdown-item" style="color: #2F6F4E;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        <span>Assistance WhatsApp (7j/7)</span>
                    </a>
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
