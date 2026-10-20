@extends('layouts.app')

@section('title', 'NSOROMMA — Librairie panafricaine : livres papier, PDF & audio')
@section('meta_description', 'Parcourez les étagères de NSOROMMA : spiritualité, philosophie, religion et savoirs africains en livre papier, e-book PDF et livre audio.')
@section('body_class', 'broks-page home-page')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}" defer></script>
@endpush

@section('content')
    @php
        $shelfColors = ['amber', 'sky', 'emerald', 'rose', 'violet'];
    @endphp

    <div class="home">
        <header class="home-hero">
            <p class="home-eyebrow reveal" style="--delay: 0ms;">Notre bibliothèque</p>
            <h1 class="home-title reveal" style="--delay: 80ms;" aria-label="Livres">
                @foreach(mb_str_split('LIVRES') as $index => $letter)
                    <span class="home-title-letter" style="--i: {{ $index }};" aria-hidden="true">{{ $letter }}</span>
                @endforeach
            </h1>
            <p class="home-subtitle reveal" style="--delay: 160ms;">
                Spiritualité, philosophie et savoirs africains — en papier, en PDF ou à écouter.
            </p>

            <form action="{{ route('catalog.index') }}" method="GET" class="home-search reveal" style="--delay: 240ms;" role="search">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="search" placeholder="Titre, auteur, thème…" aria-label="Rechercher un livre">
                <button type="submit">Rechercher</button>
            </form>
        </header>

        @forelse($shelves as $index => $shelf)
            <section class="shelf shelf-{{ $shelfColors[$index % count($shelfColors)] }}" aria-labelledby="shelf-title-{{ $index }}" data-shelf>
                <div class="shelf-head">
                    <h2 class="shelf-title" id="shelf-title-{{ $index }}">
                        <a href="{{ $shelf['url'] }}">{{ $shelf['title'] }}</a>
                    </h2>
                    <div class="shelf-meta">
                        <a href="{{ $shelf['url'] }}" class="shelf-count">{{ $shelf['count'] }} {{ Str::plural('livre', $shelf['count']) }}</a>
                        <button type="button" class="shelf-arrow" data-scroll="-1" aria-label="Livres précédents : {{ $shelf['title'] }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <button type="button" class="shelf-arrow" data-scroll="1" aria-label="Livres suivants : {{ $shelf['title'] }}">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="shelf-stage">
                    <ul class="shelf-row" data-shelf-row>
                        @foreach($shelf['books'] as $bookIndex => $book)
                            <li class="shelf-book" style="--i: {{ $bookIndex }};">
                                <a href="{{ route('catalog.show', $book->slug) }}" class="shelf-book-link">
                                    <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}"
                                         alt="{{ $book->title }}, {{ $book->author }}"
                                         loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                                    <span class="shelf-book-caption">
                                        <span class="shelf-book-title">{{ $book->title }}</span>
                                        <span class="shelf-book-author">{{ $book->author }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="shelf-board" aria-hidden="true">
                        <span class="shelf-screw"></span>
                        <span class="shelf-screw"></span>
                    </div>
                </div>
            </section>
        @empty
            <div class="home-empty reveal">
                <p class="home-empty-title">Les étagères se remplissent bientôt.</p>
                <p>Aucun livre n'est encore publié dans la boutique.</p>
            </div>
        @endforelse
    </div>

    <a href="{{ route('catalog.index') }}" class="home-cta" aria-label="Parcourir le catalogue complet{{ $booksCount ? ' ('.$booksCount.' livres)' : '' }}">
        <span>Parcourir le catalogue</span>
        @if($booksCount)
            <span class="home-cta-count">{{ $booksCount }}</span>
        @endif
    </a>
@endsection
