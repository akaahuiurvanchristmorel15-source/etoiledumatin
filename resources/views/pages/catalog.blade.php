@extends('layouts.app')
@section('title', ($selectedCategory ? $selectedCategory->name.' — ' : '').'Catalogue — NSOROMMA')
@section('meta_description', $selectedCategory?->description ? Str::limit(strip_tags($selectedCategory->description), 160) : 'Parcourez le catalogue NSOROMMA : spiritualité africaine, philosophie, religion et politique panafricaine en papier, PDF et audio.')
@section('canonical', route('catalog.index', array_filter(['category' => request('category'), 'page' => (int) request('page') > 1 ? (int) request('page') : null])))
@if(request()->anyFilled(['search', 'author', 'language', 'format', 'price_min', 'price_max', 'sort']))
    @section('robots', 'noindex, follow')
@endif

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/catalog-page.css') }}?v={{ filemtime(public_path('css/catalog-page.css')) }}">
@endpush

@section('content')
    @php
        $formats = ['pdf' => 'e-Book PDF', 'physical' => 'Livre papier', 'audio' => 'Livre audio'];
        $sorts = ['recent' => 'Plus récents', 'price_asc' => 'Prix croissant', 'price_desc' => 'Prix décroissant', 'author' => 'Auteur (A–Z)'];
        $allCount = $categories->sum('books_count');
        $activeFilters = collect([
            'search' => request('search') ? '« '.request('search').' »' : null,
            'category' => $selectedCategory?->name,
            'format' => $formats[request('format')] ?? null,
            'author' => request('author'),
            'language' => request('language'),
            'price_max' => request('price_max') ? 'Jusqu\'à '.number_format((int) request('price_max'), 0, ',', ' ').' FCFA' : null,
        ])->filter();
        $lowestPrice = function ($book) {
            $prices = collect([
                $book->is_pdf_available ? $book->price_pdf : null,
                $book->is_physical_available ? $book->price_physical : null,
                $book->is_audio_available ? $book->price_audio : null,
            ])->filter(fn ($price) => $price !== null);

            return $prices->isEmpty() ? null : $prices->min();
        };
    @endphp

    <div class="catalog" data-live="catalog" x-data="{ filtersOpen: false }">
        <header class="catalog-header">
            <p class="catalog-eyebrow">Catalogue</p>
            <h1 class="catalog-title">{{ $selectedCategory?->name ?? 'Tous les livres' }}</h1>
            <p class="catalog-subtitle">
                {{ $books->total() }} {{ Str::plural('livre', $books->total()) }}
                @if($selectedCategory?->description) — {{ Str::limit($selectedCategory->description, 120) }} @endif
            </p>

            <form action="{{ route('catalog.index') }}" method="GET" class="catalog-search" role="search">
                @foreach(request()->except(['search', 'page']) as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Titre, auteur, mot-clé…" aria-label="Rechercher dans le catalogue">
                <button type="submit">Rechercher</button>
            </form>
        </header>

        <div class="catalog-layout">
            {{-- Filtres --}}
            <aside class="catalog-filters" :class="{ 'is-open': filtersOpen }" aria-label="Filtres du catalogue">
                <div class="filters-mobile-head">
                    <span>Filtres</span>
                    <button type="button" class="filters-close" @click="filtersOpen = false" aria-label="Fermer les filtres">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <section class="filter-group">
                    <h2 class="filter-title">Collections</h2>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['category' => null, 'page' => null]) }}" class="{{ ! request('category') ? 'is-active' : '' }}">
                                <span>Toutes</span><span class="filter-count">{{ $allCount }}</span>
                            </a>
                        </li>
                        @foreach($categories as $category)
                            <li>
                                <a href="{{ request()->fullUrlWithQuery(['category' => $category->slug, 'page' => null]) }}" class="{{ request('category') === $category->slug ? 'is-active' : '' }}">
                                    <span>{{ $category->name }}</span><span class="filter-count">{{ $category->books_count }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <section class="filter-group">
                    <h2 class="filter-title">Format</h2>
                    <ul class="filter-list">
                        <li>
                            <a href="{{ request()->fullUrlWithQuery(['format' => null, 'page' => null]) }}" class="{{ ! request('format') ? 'is-active' : '' }}"><span>Tous les formats</span></a>
                        </li>
                        @foreach($formats as $value => $label)
                            <li>
                                <a href="{{ request()->fullUrlWithQuery(['format' => $value, 'page' => null]) }}" class="{{ request('format') === $value ? 'is-active' : '' }}"><span>{{ $label }}</span></a>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <form action="{{ route('catalog.index') }}" method="GET" class="filter-group filter-form">
                    @foreach(request()->only(['search', 'category', 'format', 'sort']) as $key => $value)
                        @if($value)<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                    @endforeach

                    <h2 class="filter-title">Affiner</h2>
                    <label class="filter-field">
                        <span>Auteur</span>
                        <select name="author">
                            <option value="">Tous les auteurs</option>
                            @foreach($authors as $author)
                                <option value="{{ $author }}" @selected(request('author') === $author)>{{ $author }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="filter-field">
                        <span>Langue</span>
                        <select name="language">
                            <option value="">Toutes les langues</option>
                            @foreach($languages as $language)
                                <option value="{{ $language }}" @selected(request('language') === $language)>{{ $language }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="filter-field">
                        <span>Prix maximum (FCFA)</span>
                        <input type="number" name="price_max" min="0" step="500" value="{{ request('price_max') }}" placeholder="Ex : 10 000">
                    </label>
                    <button type="submit" class="filter-apply">Appliquer</button>
                </form>
            </aside>
            <div class="filters-backdrop" x-show="filtersOpen" x-cloak x-transition.opacity @click="filtersOpen = false" style="display: none;"></div>

            {{-- Résultats --}}
            <section class="catalog-results" aria-label="Livres">
                <div class="catalog-toolbar">
                    <button type="button" class="filters-toggle" @click="filtersOpen = true">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="4" y1="7" x2="20" y2="7"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="17" x2="14" y2="17"/></svg>
                        <span>Filtres @if($activeFilters->isNotEmpty())({{ $activeFilters->count() }})@endif</span>
                    </button>

                    <div class="active-filters">
                        @foreach($activeFilters as $key => $label)
                            <a href="{{ request()->fullUrlWithQuery([$key => null, 'page' => null]) }}" class="active-filter" aria-label="Retirer le filtre {{ $label }}">
                                <span>{{ $label }}</span>
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                            </a>
                        @endforeach
                        @if($activeFilters->count() > 1)
                            <a href="{{ route('catalog.index') }}" class="clear-filters">Tout effacer</a>
                        @endif
                    </div>

                    <form action="{{ request()->url() }}" method="GET" class="catalog-sort">
                        @foreach(request()->except(['sort', 'page']) as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                        <label for="sort" class="sr-only">Trier par</label>
                        <select id="sort" name="sort" onchange="this.form.requestSubmit()">
                            @foreach($sorts as $value => $label)
                                <option value="{{ $value }}" @selected((request('sort') ?: 'recent') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>

                @if($books->isNotEmpty())
                    <ul class="catalog-grid">
                        @foreach($books as $index => $book)
                            @php $price = $lowestPrice($book); @endphp
                            <li class="catalog-item" style="--i: {{ $index }};">
                                <a href="{{ route('catalog.show', $book->slug) }}" class="catalog-card">
                                    <span class="catalog-cover">
                                        <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}"
                                             alt="Couverture de {{ $book->title }}" loading="lazy"
                                             onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                                    </span>
                                    <span class="catalog-formats">
                                        {{ collect(['PDF' => $book->is_pdf_available, 'Papier' => $book->is_physical_available, 'Audio' => $book->is_audio_available])->filter()->keys()->implode(' · ') }}
                                    </span>
                                    <span class="catalog-book-title">{{ $book->title }}</span>
                                    <span class="catalog-book-author">{{ $book->author }}</span>
                                    @if($price !== null)
                                        <span class="catalog-price">{{ $price > 0 ? 'Dès '.number_format($price, 0, ',', ' ').' FCFA' : 'Gratuit' }}</span>
                                    @endif
                                    @if($book->is_physical_available)
                                        <span class="catalog-stock is-{{ $book->stockLevel() }}">Papier : {{ $book->stockLevel() === 'out' ? 'épuisé' : Str::lcfirst($book->stockLabel()) }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{ $books->onEachSide(1)->links('components.pagination') }}
                @else
                    <div class="catalog-empty">
                        <p class="catalog-empty-title">Aucun livre trouvé</p>
                        <p>Essayez un autre mot-clé ou retirez quelques filtres.</p>
                        <a href="{{ route('catalog.index') }}" class="catalog-empty-action">Voir tout le catalogue</a>
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
