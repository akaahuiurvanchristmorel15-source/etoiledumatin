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
                <a href="https://wa.me/2250700000001" class="nav-dropdown-item">Assistance WhatsApp</a>
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
                    @click="searchModalOpen = true" 
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
       3. TIROIR LATÉRAL MOBILE
       ========================================================================= -->
    @php
        $drawerCategories = \App\Models\Category::where('is_active', true)
            ->whereHas('books', fn ($query) => $query->active())
            ->withCount(['books' => fn ($query) => $query->active()])
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
        $drawerLinks = [
            ['url' => route('home'), 'label' => 'Accueil', 'active' => $isHome],
            ['url' => route('catalog.index'), 'label' => 'Catalogue', 'active' => $isCatalog && ! request('category') && request('format') !== 'audio'],
            ['url' => route('catalog.index', ['format' => 'audio']), 'label' => 'Livres audio', 'active' => $isCatalog && request('format') === 'audio'],
            ['url' => route('library.index'), 'label' => 'Ma bibliothèque', 'active' => $isLibrary],
        ];
    @endphp

    <div x-show="mobileMenuOpen" x-cloak class="drawer-backdrop" @click="mobileMenuOpen = false"
         x-transition:enter="drawer-fade" x-transition:enter-start="drawer-fade-out" x-transition:enter-end="drawer-fade-in"
         x-transition:leave="drawer-fade" x-transition:leave-start="drawer-fade-in" x-transition:leave-end="drawer-fade-out"></div>

    <aside x-show="mobileMenuOpen" x-cloak class="drawer" role="dialog" aria-modal="true" aria-label="Menu"
           x-transition:enter="drawer-slide" x-transition:enter-start="drawer-slide-out" x-transition:enter-end="drawer-slide-in"
           x-transition:leave="drawer-slide" x-transition:leave-start="drawer-slide-in" x-transition:leave-end="drawer-slide-out">

        <div class="drawer-head">
            <a href="{{ route('home') }}" class="drawer-brand" aria-label="Accueil NSOROMMA">
                <img src="{{ asset('images/brand/nsoromma-mark.png') }}" alt="" width="28" height="28">
                <span>NSOROMMA</span>
            </a>
            <button type="button" class="drawer-close" @click="mobileMenuOpen = false" aria-label="Fermer le menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>

        <div class="drawer-body">
            <form action="{{ route('catalog.index') }}" method="GET" class="drawer-search" role="search">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="search" placeholder="Titre, auteur, thème…" aria-label="Rechercher un livre">
            </form>

            <nav class="drawer-nav" aria-label="Navigation principale">
                @foreach($drawerLinks as $index => $link)
                    <a href="{{ $link['url'] }}" class="drawer-link {{ $link['active'] ? 'is-active' : '' }}" style="--i: {{ $index }};" @if($link['active']) aria-current="page" @endif>
                        <span>{{ $link['label'] }}</span>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
                    </a>
                @endforeach
            </nav>

            @if($drawerCategories->isNotEmpty())
                <section class="drawer-section" style="--i: 4;">
                    <h2 class="drawer-label">Collections</h2>
                    <ul class="drawer-collections">
                        @foreach($drawerCategories as $category)
                            <li>
                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="{{ request('category') === $category->slug ? 'is-active' : '' }}">
                                    <span>{{ $category->name }}</span>
                                    <span class="drawer-count">{{ $category->books_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <section class="drawer-section drawer-services" style="--i: 5;">
                <a href="{{ route('orders.lookup') }}">Suivi de commande</a>
                <a href="https://wa.me/2250700000001">Assistance WhatsApp</a>
            </section>
        </div>

        <div class="drawer-account">
            @auth
                <div class="drawer-user">
                    <span class="drawer-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <div class="drawer-user-info">
                        <div class="drawer-user-name">{{ auth()->user()->name }}</div>
                        <div class="drawer-user-email">{{ auth()->user()->email }}</div>
                    </div>
                </div>
                <div class="drawer-actions">
                    @if(auth()->user()->isStaff())
                        <a href="{{ route('admin.dashboard') }}" class="drawer-btn drawer-btn-ghost">Administration</a>
                    @endif
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="drawer-btn drawer-btn-ghost">Se déconnecter</button>
                    </form>
                </div>
            @else
                <div class="drawer-actions">
                    <a href="{{ route('login') }}" class="drawer-btn">Se connecter</a>
                    <a href="{{ route('register') }}" class="drawer-btn drawer-btn-ghost">Créer un compte</a>
                </div>
            @endauth
        </div>
    </aside>

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
                @click="searchModalOpen = true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><line x1="20" y1="20" x2="15.4" y2="15.4"/></svg>
        </button>
    </nav>

    <!-- =========================================================================
       5. FENÊTRE DE RECHERCHE (résultats instantanés)
       ========================================================================= -->
    @php
        $searchAuthors = \App\Models\Book::active()
            ->select('author')
            ->groupBy('author')
            ->orderByRaw('COUNT(*) DESC')
            ->orderBy('author')
            ->limit(6)
            ->pluck('author');
    @endphp

    <div x-show="searchModalOpen" x-cloak class="search-backdrop" @click="searchModalOpen = false"
         x-transition:enter="drawer-fade" x-transition:enter-start="drawer-fade-out" x-transition:enter-end="drawer-fade-in"
         x-transition:leave="drawer-fade" x-transition:leave-start="drawer-fade-in" x-transition:leave-end="drawer-fade-out"></div>

    <div x-show="searchModalOpen" x-cloak class="search-panel" role="dialog" aria-modal="true" aria-label="Rechercher un livre"
         x-transition:enter="search-pop" x-transition:enter-start="search-pop-out" x-transition:enter-end="search-pop-in"
         x-transition:leave="search-pop" x-transition:leave-start="search-pop-in" x-transition:leave-end="search-pop-out"
         x-data="{
            query: '',
            results: [],
            loading: false,
            searched: false,
            catalogUrl: @js(route('catalog.index')),
            controller: null,
            async search() {
                const term = this.query.trim();
                if (term.length < 2) { this.results = []; this.searched = false; return; }
                this.controller?.abort();
                this.controller = new AbortController();
                this.loading = true;
                try {
                    const response = await fetch(@js(route('search.suggestions')) + '?q=' + encodeURIComponent(term), { headers: { 'Accept': 'application/json' }, signal: this.controller.signal });
                    if (! response.ok) { throw new Error(response.status); }
                    const data = await response.json();
                    this.results = data.books;
                    this.catalogUrl = data.catalogUrl;
                    this.searched = true;
                } catch (error) {
                    if (error.name !== 'AbortError') { this.results = []; this.searched = true; }
                } finally {
                    this.loading = false;
                }
            }
         }"
         x-effect="if (searchModalOpen) { $nextTick(() => $refs.searchInput.focus()) }"
         @keydown.escape.window="searchModalOpen = false">

        <form action="{{ route('catalog.index') }}" method="GET" class="search-form" role="search">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <input type="search" name="search" x-ref="searchInput" x-model="query" @input.debounce.250ms="search()"
                   placeholder="Un titre, un auteur…" autocomplete="off" aria-label="Rechercher un titre ou un auteur" maxlength="80">
            <span class="search-spinner" x-show="loading" aria-hidden="true"></span>
            <button type="button" class="search-close" @click="searchModalOpen = false" aria-label="Fermer la recherche">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </form>

        <div class="search-body">
            {{-- Résultats instantanés --}}
            <div x-show="query.trim().length >= 2" x-cloak>
                <template x-if="results.length > 0">
                    <div>
                        <p class="search-label">Livres</p>
                        <ul class="search-results">
                            <template x-for="(book, index) in results" :key="book.url">
                                <li :style="`--i: ${index}`">
                                    <a :href="book.url" class="search-result">
                                        <img :src="book.cover" alt="" loading="lazy">
                                        <span class="search-result-text">
                                            <span class="search-result-title" x-text="book.title"></span>
                                            <span class="search-result-meta" x-text="book.author + (book.formats ? ' — ' + book.formats : '')"></span>
                                        </span>
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="13 6 19 12 13 18"/></svg>
                                    </a>
                                </li>
                            </template>
                        </ul>
                        <a :href="catalogUrl" class="search-all">Voir tous les résultats dans le catalogue →</a>
                    </div>
                </template>
                <p class="search-empty" x-show="searched && ! loading && results.length === 0">
                    Aucun livre pour « <span x-text="query.trim()"></span> ». Essayez un autre titre ou un nom d'auteur.
                </p>
            </div>

            {{-- Suggestions avant la saisie --}}
            <div x-show="query.trim().length < 2">
                @if($drawerCategories->isNotEmpty())
                    <section class="search-section">
                        <p class="search-label">Collections</p>
                        <div class="search-chips">
                            @foreach($drawerCategories as $category)
                                <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" class="search-chip">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if($searchAuthors->isNotEmpty())
                    <section class="search-section">
                        <p class="search-label">Auteurs</p>
                        <div class="search-chips">
                            @foreach($searchAuthors as $author)
                                <a href="{{ route('catalog.index', ['author' => $author]) }}" class="search-chip">{{ $author }}</a>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="search-section">
                    <p class="search-label">Formats</p>
                    <div class="search-chips">
                        <a href="{{ route('catalog.index', ['format' => 'pdf']) }}" class="search-chip">e-Books PDF</a>
                        <a href="{{ route('catalog.index', ['format' => 'physical']) }}" class="search-chip">Livres papier</a>
                        <a href="{{ route('catalog.index', ['format' => 'audio']) }}" class="search-chip">Livres audio</a>
                    </div>
                </section>
            </div>
        </div>
    </div>

</div>
