@extends('layouts.app')

@section('title', 'L\'ÉTOILE DU MATIN — Bibliothèque Numérique & AudioBooks')
@section('meta_description', 'Explorez la sélection de livres numériques et audiobooks sur la plateforme Etoile du Matin.')
@section('body_class', 'broks-page')
@section('body_attributes', '')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/books.js') }}" defer></script>
@endpush

@section('content')

@php
    $fallbackBestsellers = [
        ['id' => 1, 'title' => 'Left to Fear', 'author' => 'Adele Parks', 'image' => 'images/books/left-to-fear.jpg', 'price' => 14.99],
        ['id' => 2, 'title' => 'The Last Thing He Told Me', 'author' => 'Laura Dave', 'image' => 'images/books/the-last-thing-he-told-me.jpg', 'price' => 12.99],
        ['id' => 3, 'title' => 'Malibu Rising', 'author' => 'Taylor Jenkins Reid', 'image' => 'images/books/malibu-rising.jpg', 'price' => 13.99],
        ['id' => 4, 'title' => 'Black Ice', 'author' => 'Brad Thor', 'image' => 'images/books/black-ice.jpg', 'price' => 15.99],
        ['id' => 5, 'title' => 'Blind Tiger', 'author' => 'Sandra Brown', 'image' => 'images/books/blind-tiger.jpg', 'price' => 11.99],
    ];
    $displayBestsellers = (isset($bestsellers) && !empty($bestsellers)) ? $bestsellers : $fallbackBestsellers;
@endphp

    <!-- 2. Section Héro Principale (3 colonnes) -->
    <section class="hero-section" aria-label="Livre à la une et sélection éditoriale">
        <span class="sr-only">BROKS</span>
        <span class="sr-only">The Last Thing He Told Me</span>
        
        <!-- Zone Gauche : Titre éditorial & Recherche -->
        <div class="hero-left">
            <h1 class="hero-title font-editorial">
                New &amp;<br>Trending
            </h1>
            <p class="hero-subtitle">
                Explore new worlds from authors
            </p>

            <!-- Barre de recherche interactive -->
            <x-search />
        </div>

        <!-- Zone Centrale : Livre Vedette avec perspective et ombre réaliste -->
        <div class="hero-center">
            @if(isset($featuredBooks) && $featuredBooks->isNotEmpty())
                @php $featured = $featuredBooks->first(); @endphp
                <a href="{{ route('catalog.show', $featured->slug) }}" class="featured-book-wrap" title="{{ $featured->title }} par {{ $featured->author }}">
                    <span class="featured-ribbon">Bestseller</span>
                    <img 
                        src="{{ $featured->cover_image ? asset($featured->cover_image) : asset('images/books/fallback-book.jpg') }}" 
                        alt="Couverture du livre {{ $featured->title }} de {{ $featured->author }}" 
                        class="featured-book-img"
                        onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                    >
                </a>
            @else
                <div class="featured-book-wrap" title="The Last Thing He Told Me par Laura Dave">
                    <span class="featured-ribbon">Bestseller</span>
                    <img 
                        src="{{ asset('images/books/the-last-thing-he-told-me.jpg') }}" 
                        alt="Couverture du livre The Last Thing He Told Me de Laura Dave" 
                        class="featured-book-img"
                        onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                    >
                </div>
            @endif
        </div>

        <!-- Zone Droite : Panneau Auteur de la semaine & Panneau Lecteur Audio -->
        <div class="hero-right">
            
            <!-- Carte Auteur de la semaine -->
            <article class="author-card" aria-label="Auteur de la semaine : Stephen King">
                <div>
                    <span class="author-label">Author of the week</span>
                    <h2 class="author-name font-editorial">Stephen King</h2>
                    <p class="author-count">78 books</p>
                </div>
                <img 
                    src="{{ asset('images/books/stephen-king.jpg') }}" 
                    alt="Photo portrait de Stephen King" 
                    class="author-avatar"
                >
            </article>

            <!-- Carte Lecteur Audio (Last listened) -->
            <article class="audio-card" aria-label="Dernière écoute : False Witness">
                <div class="audio-meta">
                    <img 
                        src="{{ asset('images/books/false-witness.jpg') }}" 
                        alt="Miniature audiobook False Witness" 
                        class="audio-thumb"
                        onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                    >
                    <div>
                        <span class="author-label" style="font-size:0.68rem;">Last listened</span>
                        <h3 class="audio-title">False Witness</h3>
                        <p class="audio-author">Karin Slaughter</p>
                    </div>
                </div>

                <!-- Contrôles audio interactifs -->
                <div class="audio-controls">
                    <button type="button" class="play-pause-btn" id="audio-play-btn" aria-label="Lancer la lecture audio">
                        <!-- Play Icon SVG -->
                        <svg id="play-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <!-- Pause Icon SVG -->
                        <svg id="pause-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                    </button>

                    <!-- Barre de progression scrubbable -->
                    <div class="progress-wrap" id="audio-progress-wrap" role="progressbar" aria-valuemin="0" aria-valuemax="270" aria-valuenow="135" aria-label="Progression de l'audio">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" id="audio-progress-fill" style="width: 50%;"></div>
                        </div>
                        <div class="time-row">
                            <span id="audio-current-time">02:15</span>
                            <span id="audio-total-time">04:30</span>
                        </div>
                    </div>
                </div>
            </article>

        </div>

    </section>

    <!-- 3. Section Bestsellers Récents (Dynamique) -->
    <section class="bestsellers-section" aria-label="Sélection des meilleures ventes récentes">
        
        <!-- Titre vertical éditorial -->
        <div class="vertical-title-wrap">
            <h2 class="vertical-title font-editorial">
                Recent Bestsellers
            </h2>
        </div>

        <!-- Rangée de livres avec filtrage dynamique -->
        <div class="books-row" id="books-grid">
            @if(isset($latestBooks) && $latestBooks->isNotEmpty())
                @foreach($latestBooks as $book)
                    <x-book-card :book="$book" />
                @endforeach
            @else
                @foreach($displayBestsellers as $book)
                    <x-book-card 
                        :id="$book['id']"
                        :title="$book['title']"
                        :author="$book['author']"
                        :image="$book['image']"
                        :price="$book['price']"
                    />
                @endforeach
            @endif

            <!-- Message aucun résultat lors de la recherche -->
            <div id="no-search-results" style="display:none;grid-column:1/-1;text-align:center;padding:3rem 1rem;color:#8B857C;">
                <p style="font-size:1.1rem;font-weight:700;margin-bottom:0.5rem;color:#1E1D1B;">Aucun livre trouvé</p>
                <p style="font-size:0.875rem;">Essayez avec un autre mot-clé (titre ou auteur).</p>
            </div>
        </div>

    </section>

    <!-- 4. Section Catalogue par Catégories -->
    @if(isset($categories) && $categories->isNotEmpty())
        <section style="padding:2rem 3rem 3rem;border-top:1px solid rgba(139,133,124,0.08);" aria-label="Explorer par catégorie">
            <h2 class="font-editorial" style="font-size:1.4rem;font-weight:800;color:#1E1D1B;margin-bottom:1.5rem;">Explorer par catégorie</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:1rem;">
                @foreach($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug]) }}" 
                       style="display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;background:#F8F1E5;border-radius:14px;border:1px solid rgba(217,174,92,0.15);text-decoration:none;color:#1E1D1B;font-weight:600;font-size:0.88rem;transition:all 0.25s ease;"
                       onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 6px 16px rgba(60,45,25,0.08)'"
                       onmouseout="this.style.transform='';this.style.boxShadow=''">
                        <span>{{ $category->name }}</span>
                        <span style="background:#D9AE5C;color:#191919;font-size:0.7rem;font-weight:800;padding:2px 8px;border-radius:10px;">{{ $category->books_count }}</span>
                    </a>
                @endforeach
            </div>

            <div style="text-align:center;margin-top:2rem;">
                <a href="{{ route('catalog.index') }}" style="display:inline-flex;align-items:center;gap:0.5rem;background:#191919;color:#D9AE5C;padding:0.75rem 2rem;border-radius:30px;text-decoration:none;font-size:0.85rem;font-weight:700;transition:all 0.25s ease;" onmouseover="this.style.background='#D9AE5C';this.style.color='#191919'" onmouseout="this.style.background='#191919';this.style.color='#D9AE5C'">
                    Voir tout le catalogue
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
            </div>
        </section>
    @endif

@endsection
