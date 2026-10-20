@extends('layouts.app')
@section('title', 'Le Catalogue des Livres — NSOROMMA')
@section('meta_description', 'Parcourez l\'ensemble des ouvrages de spiritualité africaine, philosophie, religion et politique panafricaine.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $featuredBook = $books->first();
@endphp

<div x-data="{ filtersOpen: {{ request()->anyFilled(['author', 'language', 'format', 'price_min', 'price_max']) ? 'true' : 'false' }} }">

    <!-- 1. Section Héro Principale du Catalogue (3 Colonnes Style BROKS) -->
    <section class="hero-section" aria-label="Présentation du catalogue et recherche">
        
        <!-- Zone Gauche : Titre Éditorial & Recherche -->
        <div class="hero-left">
            <h1 class="hero-title font-editorial">
                Catalogue des Livres
            </h1>
            <p class="hero-subtitle">
                Explorez le patrimoine spirituel, philosophique et politique du monde noir
            </p>

            <!-- Recherche Rapide -->
            <form action="{{ route('catalog.index') }}" method="GET" class="search-container" role="search">
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('format')) <input type="hidden" name="format" value="{{ request('format') }}"> @endif
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                
                <input 
                    type="search" 
                    name="search" 
                    value="{{ request('search') }}"
                    placeholder="Rechercher un livre, un auteur..." 
                    class="search-input"
                    aria-label="Rechercher dans le catalogue"
                >
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </form>
        </div>

        <!-- Zone Centrale : Livre Vedette à l'Affiche -->
        <div class="hero-center">
            @if($featuredBook)
                <a href="{{ route('catalog.show', $featuredBook->slug) }}" class="featured-book-wrap" title="{{ $featuredBook->title }} par {{ $featuredBook->author }}">
                    <span class="featured-ribbon">À Découvrir</span>
                    <img 
                        src="{{ $featuredBook->cover_image ? asset($featuredBook->cover_image) : asset('images/books/fallback-book.jpg') }}" 
                        alt="Couverture du livre {{ $featuredBook->title }}" 
                        class="featured-book-img"
                        onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                    >
                </a>
            @else
                <div class="featured-book-wrap">
                    <img src="{{ asset('images/books/fallback-book.jpg') }}" alt="Catalogue" class="featured-book-img">
                </div>
            @endif
        </div>

        <!-- Zone Droite : Panneaux d'Informations Style BROKS -->
        <div class="hero-right">
            
            <!-- Carte 1 : Thématique Phare -->
            <article class="author-card" aria-label="Collections en vedette">
                <div>
                    <span class="author-label">Fonds documentaire</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.05rem;">Penser l'Afrique</h2>
                    <p class="author-count">{{ $books->total() }} titres disponibles</p>
                </div>
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    ★
                </div>
            </article>

            <!-- Carte 2 : Formats disponibles -->
            <article class="audio-card" aria-label="Formats disponibles">
                <div class="audio-meta">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(108, 52, 131, 0.12); color: #6C3483; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    </div>
                    <div>
                        <span class="author-label" style="font-size:0.68rem;">Triple format certifié</span>
                        <h3 class="audio-title" style="font-size: 0.9rem;">PDF, Papier &amp; Audio</h3>
                        <p class="audio-author">Lecture et écoute panafricaine</p>
                    </div>
                </div>
                <div style="display: flex; gap: 0.4rem; font-size: 0.72rem; font-weight: 700;">
                    <a href="{{ request()->fullUrlWithQuery(['format' => 'pdf', 'page' => null]) }}" style="flex: 1; text-align: center; padding: 0.4rem; border-radius: 20px; background: {{ request('format') === 'pdf' ? '#2F6F4E; color: #fff;' : '#F6F0E6; color: #1E1D1B;' }} text-decoration: none;">PDF</a>
                    <a href="{{ request()->fullUrlWithQuery(['format' => 'physical', 'page' => null]) }}" style="flex: 1; text-align: center; padding: 0.4rem; border-radius: 20px; background: {{ request('format') === 'physical' ? '#D9AE5C; color: #171717;' : '#F6F0E6; color: #1E1D1B;' }} text-decoration: none;">Papier</a>
                    <a href="{{ request()->fullUrlWithQuery(['format' => 'audio', 'page' => null]) }}" style="flex: 1; text-align: center; padding: 0.4rem; border-radius: 20px; background: {{ request('format') === 'audio' ? '#6C3483; color: #fff;' : '#F6F0E6; color: #1E1D1B;' }} text-decoration: none;">Audio</a>
                </div>
            </article>

        </div>

    </section>

    <!-- 2. Barre d'Onglets des Catégories (Style Exact des Onglets de book.index) -->
    <section class="catalog-subnav-section">
        <div class="catalog-subnav-container">
            
            <!-- Onglets de collections horizontaux avec défilement tactile -->
            <div class="catalog-tabs-scroll">
                <div class="nav-tabs" role="tablist" style="background-color: #EFE4D3; padding: 0.3rem; white-space: nowrap;">
                    <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" 
                       class="nav-tab {{ !request('category') ? 'active' : '' }}"
                       role="tab">
                        <span>Toutes les collections</span>
                    </a>

                    @foreach($categories as $cat)
                        <a href="{{ request()->fullUrlWithQuery(['category' => $cat->slug, 'page' => null]) }}" 
                           class="nav-tab {{ request('category') === $cat->slug ? 'active' : '' }}"
                           role="tab">
                            <span>{{ $cat->name }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Contrôles à Droite : Tri & Bouton Filtres -->
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <!-- Tri -->
                <form action="{{ request()->url() }}" method="GET" style="margin: 0;">
                    @foreach(request()->except(['sort', 'page']) as $k => $v)
                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                    @endforeach
                    <select name="sort" onchange="this.form.submit()" class="sort-select" style="background: #FFFDF9; border: 1px solid rgba(217,174,92,0.3); border-radius: 20px; padding: 0.45rem 2rem 0.45rem 1rem; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; cursor: pointer;">
                        <option value="recent" {{ request('sort') == 'recent' || !request('sort') ? 'selected' : '' }}>Plus récents</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Prix croissant</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Prix décroissant</option>
                        <option value="author" {{ request('sort') == 'author' ? 'selected' : '' }}>Auteur A-Z</option>
                    </select>
                </form>

                <!-- Bouton Filtres avancés toggle -->
                <button type="button" 
                        @click="filtersOpen = !filtersOpen" 
                        class="action-btn" 
                        style="width: auto; height: auto; border-radius: 20px; padding: 0.45rem 1rem; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.3); font-size: 0.8rem; font-weight: 700; display: inline-flex; gap: 0.4rem;"
                        :style="filtersOpen ? 'background: #D9AE5C; color: #171717;' : ''">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><line x1="4" y1="21" x2="4" y2="14"/><line x1="4" y1="10" x2="4" y2="3"/><line x1="12" y1="21" x2="12" y2="12"/><line x1="12" y1="8" x2="12" y2="3"/><line x1="20" y1="21" x2="20" y2="16"/><line x1="20" y1="12" x2="20" y2="3"/><line x1="1" y1="14" x2="7" y2="14"/><line x1="9" y1="8" x2="15" y2="8"/><line x1="17" y1="16" x2="23" y2="16"/></svg>
                    <span>Filtres</span>
                </button>
            </div>

        </div>

        <!-- Panneau de Filtres Avancés Dépliable -->
        <div x-show="filtersOpen" x-cloak x-transition style="margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid rgba(139,133,124,0.12);">
            <form action="{{ route('catalog.index') }}" method="GET" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; align-items: end;">
                @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif
                @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif

                <!-- Auteur -->
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #8B857C; margin-bottom: 0.4rem;">Auteur</label>
                    <select name="author" class="catalog-select" style="background: #FFFDF9; border-radius: 14px; border: 1px solid rgba(217,174,92,0.3); font-size: 0.8rem; padding: 0.5rem 1rem;">
                        <option value="">Tous les auteurs</option>
                        @foreach($authors as $auth)
                            <option value="{{ $auth }}" {{ request('author') === $auth ? 'selected' : '' }}>{{ $auth }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Langue -->
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #8B857C; margin-bottom: 0.4rem;">Langue</label>
                    <select name="language" class="catalog-select" style="background: #FFFDF9; border-radius: 14px; border: 1px solid rgba(217,174,92,0.3); font-size: 0.8rem; padding: 0.5rem 1rem;">
                        <option value="">Toutes les langues</option>
                        @foreach($languages as $lang)
                            <option value="{{ $lang }}" {{ request('language') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Format -->
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #8B857C; margin-bottom: 0.4rem;">Format</label>
                    <select name="format" class="catalog-select" style="background: #FFFDF9; border-radius: 14px; border: 1px solid rgba(217,174,92,0.3); font-size: 0.8rem; padding: 0.5rem 1rem;">
                        <option value="">Tous les formats</option>
                        <option value="pdf" {{ request('format') === 'pdf' ? 'selected' : '' }}>e-Book PDF immédiat</option>
                        <option value="physical" {{ request('format') === 'physical' ? 'selected' : '' }}>Livre Papier (Broché)</option>
                        <option value="audio" {{ request('format') === 'audio' ? 'selected' : '' }}>Livre Audio (MP3)</option>
                    </select>
                </div>

                <!-- Prix (Min / Max) -->
                <div>
                    <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #8B857C; margin-bottom: 0.4rem;">Prix Max (FCFA)</label>
                    <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Ex : 10000" style="width: 100%; background: #FFFDF9; border: 1px solid rgba(217,174,92,0.3); border-radius: 14px; padding: 0.5rem 1rem; font-size: 0.8rem; color: #1E1D1B; outline: none;">
                </div>

                <!-- Boutons d'action -->
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="filter-apply-btn" style="padding: 0.55rem 1.25rem; font-size: 0.8rem;">
                        Filtrer
                    </button>
                    <a href="{{ route('catalog.index') }}" class="action-btn" style="padding: 0.55rem 1rem; font-size: 0.8rem; border-radius: 20px; background: #FFFDF9; border: 1px solid rgba(139,133,124,0.2);">
                        Effacer
                    </a>
                </div>
            </form>
        </div>
    </section>

    <!-- Pilules des filtres actifs (si filtres appliqués) -->
    @if(request()->anyFilled(['search', 'category', 'author', 'language', 'format', 'price_min', 'price_max']))
        <div class="catalog-active-filters-bar">
            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: #8B857C; margin-right: 0.5rem;">Filtres actifs :</span>
            
            @if(request('search'))
                <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => null]) }}" class="filter-pill">
                    <span>Recherche : "{{ request('search') }}"</span>
                    <span class="filter-pill-remove">×</span>
                </a>
            @endif

            @if(request('category'))
                <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="filter-pill">
                    <span>Catégorie : {{ $selectedCategory ? $selectedCategory->name : request('category') }}</span>
                    <span class="filter-pill-remove">×</span>
                </a>
            @endif

            @if(request('author'))
                <a href="{{ request()->fullUrlWithQuery(['author' => null, 'page' => null]) }}" class="filter-pill">
                    <span>Auteur : {{ request('author') }}</span>
                    <span class="filter-pill-remove">×</span>
                </a>
            @endif

            @if(request('format'))
                <a href="{{ request()->fullUrlWithQuery(['format' => null, 'page' => null]) }}" class="filter-pill">
                    <span>Format : {{ request('format') === 'pdf' ? 'e-Book PDF' : (request('format') === 'audio' ? 'Livre Audio MP3' : 'Livre Papier') }}</span>
                    <span class="filter-pill-remove">×</span>
                </a>
            @endif

            @if(request('price_max'))
                <a href="{{ request()->fullUrlWithQuery(['price_max' => null, 'page' => null]) }}" class="filter-pill">
                    <span>Prix Max : {{ request('price_max') }} FCFA</span>
                    <span class="filter-pill-remove">×</span>
                </a>
            @endif

            <a href="{{ route('catalog.index') }}" style="font-size: 0.75rem; color: #D9AE5C; font-weight: 700; text-decoration: underline; margin-left: auto;">
                Tout réinitialiser
            </a>
        </div>
    @endif

    <!-- 3. Rayonnage des Livres (Structure Bestsellers de book.index) -->
    <section class="bestsellers-section" aria-label="Rayonnage des livres du catalogue">
        
        <!-- Titre Vertical Élégant (Exactement comme book.index) -->
        <div class="vertical-title-wrap">
            <h2 class="vertical-title font-editorial">
                {{ $selectedCategory ? $selectedCategory->name : 'Tous les Ouvrages' }}
            </h2>
        </div>

        <!-- Rangée de livres (Grille 5 colonnes de book.index) -->
        <div style="flex: 1; display: flex; flex-direction: column;">
            
            <div style="margin-bottom: 1.25rem; font-size: 0.8rem; color: #8B857C;">
                Affichage de <strong style="color: #1E1D1B;">{{ $books->firstItem() ?? 0 }}</strong> à <strong style="color: #1E1D1B;">{{ $books->lastItem() ?? 0 }}</strong> sur <strong style="color: #1E1D1B;">{{ $books->total() }}</strong> livres répertoriés
            </div>

            @if($books->count() > 0)
                <div class="catalog-books-grid">
                    @foreach($books as $book)
                        <x-book-card :book="$book" />
                    @endforeach
                </div>

                <!-- Pagination Dorée -->
                <div style="margin-top: 3rem; display: flex; justify-content: center;">
                    {{ $books->links() }}
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        ★
                    </div>
                    <h3 class="empty-state-title">Aucun ouvrage trouvé</h3>
                    <p class="empty-state-desc">
                        Aucun livre ne correspond exactement à vos critères de recherche. Essayez de réinitialiser vos filtres pour découvrir nos autres titres.
                    </p>
                    <a href="{{ route('catalog.index') }}" class="empty-state-action">
                        <span>Réinitialiser la sélection</span>
                    </a>
                </div>
            @endif

        </div>

    </section>

    <!-- 4. Section Explorer par Catégorie (Identique au bas de page de book.index) -->
    @if(isset($categories) && $categories->isNotEmpty())
        <section class="catalog-collections-section" aria-label="Explorer par collection">
            <h2 class="font-editorial" style="font-size: 1.4rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.5rem;">
                Explorer par collection
            </h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" 
                       style="display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; background: #F8F1E5; border-radius: 14px; border: 1px solid rgba(217,174,92,0.15); text-decoration: none; color: #1E1D1B; font-weight: 600; font-size: 0.88rem; transition: all 0.25s ease;"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(60,45,25,0.08)'"
                       onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <span>{{ $category->name }}</span>
                        <span style="background: #D9AE5C; color: #191919; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 10px;">{{ $category->books_count }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

</div>
@endsection
