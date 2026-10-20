@extends('layouts.app')
@section('title', $book->title . ' — NSOROMMA')
@section('meta_description', Str::limit(strip_tags($book->description), 160))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
@php
    $avgRating = $book->average_rating ?? 5.0;
    $approvedCount = $book->approvedReviews ? $book->approvedReviews->count() : 0;
@endphp

<div x-data="{ 
    rating: 5,
    hoverRating: 0,
    showFullExcerpt: false
}">

    <!-- 1. Section Héro Principale (3 Colonnes Strictement Identiques à book.index) -->
    <section class="hero-section" aria-label="Présentation de l'ouvrage et achat">
        
        <!-- Zone Gauche : Titre, Détails & Formats d'Achat -->
        <div class="hero-left">
            
            <!-- Fil d'Ariane Minimaliste -->
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}">Catalogue</a></li>
                    @if($book->category)
                        <li class="breadcrumb-separator">›</li>
                        <li class="breadcrumb-item"><a href="{{ route('catalog.index', ['category' => $book->category->slug]) }}">{{ $book->category->name }}</a></li>
                    @endif
                </ol>
            </nav>

            <!-- Catégorie Tag -->
            @if($book->category)
                <a href="{{ route('catalog.index', ['category' => $book->category->slug]) }}" class="category-tag-pill" style="margin-bottom: 0.5rem;">
                    ★ {{ $book->category->name }}
                </a>
            @endif

            <!-- Grand Titre Éditorial -->
            <h1 class="hero-title font-editorial" style="font-size: 2.6rem; line-height: 1.15; margin-bottom: 0.5rem;">
                {{ $book->title }}
            </h1>

            <p class="hero-subtitle" style="margin-bottom: 1rem; font-size: 1rem;">
                par <a href="{{ route('catalog.index', ['author' => $book->author]) }}" style="color: #D9AE5C; font-weight: 700; text-decoration: none;">{{ $book->author }}</a>
            </p>

            <!-- Note & Avis -->
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; font-size: 0.85rem;">
                <div class="star-rating">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating) ? '#D9AE5C' : 'none' }}" stroke="{{ $i <= round($avgRating) ? '#D9AE5C' : '#D5CFC5' }}" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <strong style="color: #1E1D1B;">{{ number_format($avgRating, 1, ',', '') }}</strong>
                <span style="color: #8B857C;">({{ $approvedCount }} avis)</span>
            </div>

            <!-- Sélecteur de Formats (Cartes PDF & Broché) -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 1rem; width: 100%;">
                
                <!-- Format Numérique (PDF) -->
                @if($book->is_pdf_available)
                <div style="background: #FFFFFF; border: 1.5px solid rgba(47, 111, 78, 0.35); border-radius: 18px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 14px rgba(47, 111, 78, 0.06);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <span class="format-badge pdf">e-Book PDF</span>
                            <span style="font-size: 0.7rem; color: #2F6F4E; font-weight: 700;">Instantané</span>
                        </div>
                        <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.45rem; font-weight: 800; color: #2F6F4E; margin-bottom: 0.35rem;">
                            {{ $book->formatted_price_pdf }}
                        </div>
                        <p style="font-size: 0.72rem; color: #8B857C; margin-bottom: 0.85rem; line-height: 1.4;">
                            Téléchargement direct + Filigrane nominatif
                        </p>
                    </div>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="format" value="pdf">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-add-pdf" style="padding: 0.65rem 1rem; font-size: 0.8rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            <span>Ajouter PDF</span>
                        </button>
                    </form>
                </div>
                @endif

                <!-- Format Physique (Broché) -->
                @if($book->is_physical_available)
                <div style="background: #FFFFFF; border: 1.5px solid rgba(217, 174, 92, 0.45); border-radius: 18px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 14px rgba(217, 174, 92, 0.08);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <span class="format-badge physical">Livre Broché</span>
                            @if($book->stock_physical > 0)
                                <span style="font-size: 0.7rem; color: #2F6F4E; font-weight: 700;">En stock</span>
                            @else
                                <span style="font-size: 0.7rem; color: #C0392B; font-weight: 700;">Rupture</span>
                            @endif
                        </div>
                        <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.45rem; font-weight: 800; color: #C59B4B; margin-bottom: 0.35rem;">
                            {{ $book->formatted_price_physical }}
                        </div>
                        <p style="font-size: 0.72rem; color: #8B857C; margin-bottom: 0.85rem; line-height: 1.4;">
                            Livre papier relié + Expédition suivie
                        </p>
                    </div>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="format" value="physical">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" @disabled($book->stock_physical <= 0) class="btn-add-physical" style="padding: 0.65rem 1rem; font-size: 0.8rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                            <span>{{ $book->stock_physical > 0 ? 'Commander papier' : 'Épuisé' }}</span>
                        </button>
                    </form>
                </div>
                @endif

                <!-- Format Livre Audio (MP3) -->
                @if($book->is_audio_available)
                <div style="background: #FFFFFF; border: 1.5px solid rgba(142, 68, 173, 0.35); border-radius: 18px; padding: 1.15rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 14px rgba(142, 68, 173, 0.08);">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                            <span class="format-badge audio">Livre Audio</span>
                            <span style="font-size: 0.7rem; color: #6C3483; font-weight: 700;">{{ $book->audio_duration ?: 'MP3 HD' }}</span>
                        </div>
                        <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.45rem; font-weight: 800; color: #6C3483; margin-bottom: 0.35rem;">
                            {{ $book->formatted_price_audio }}
                        </div>
                        <p style="font-size: 0.72rem; color: #8B857C; margin-bottom: 0.85rem; line-height: 1.4;">
                            Écoute numérique + Téléchargement MP3
                        </p>
                    </div>
                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <input type="hidden" name="format" value="audio">
                        <input type="hidden" name="quantity" value="1">
                        <button type="submit" class="btn-add-audio" style="padding: 0.65rem 1rem; font-size: 0.8rem;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                            <span>Ajouter Audio</span>
                        </button>
                    </form>
                </div>
                @endif

            </div>

        </div>

        <!-- Zone Centrale : Livre Vedette avec Perspective et Ombre 3D -->
        <div class="hero-center">
            <div class="featured-book-wrap" title="{{ $book->title }} par {{ $book->author }}">
                @if($book->is_featured)
                    <span class="featured-ribbon">Bestseller</span>
                @endif
                <img 
                    src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" 
                    alt="Couverture du livre {{ $book->title }}" 
                    class="featured-book-img"
                    style="width: 260px; max-width: 100%; height: auto; aspect-ratio: 2/3;"
                    onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                >
            </div>
        </div>

        <!-- Zone Droite : Panneau Auteur & Panneau Feuilletage (Style Exact book.index) -->
        <div class="hero-right">
            
            <!-- Carte Auteur de l'Ouvrage -->
            <article class="author-card" aria-label="Auteur de l'ouvrage">
                <div>
                    <span class="author-label">Auteur de l'œuvre</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.15rem;">{{ $book->author }}</h2>
                    <p class="author-count">
                        <a href="{{ route('catalog.index', ['author' => $book->author]) }}" style="color: inherit; text-decoration: underline;">
                            Voir ses publications
                        </a>
                    </p>
                </div>
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem; border: 3px solid #FFFDF9; box-shadow: 0 4px 12px rgba(0,0,0,0.08);">
                    {{ strtoupper(substr($book->author, 0, 2)) }}
                </div>
            </article>

            <!-- Carte Feuilletage / Aperçu PDF -->
            <article class="audio-card" aria-label="Feuilleter l'extrait">
                <div class="audio-meta">
                    <img 
                        src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" 
                        alt="Miniature {{ $book->title }}" 
                        class="audio-thumb"
                        onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';"
                    >
                    <div>
                        <span class="author-label" style="font-size:0.68rem;">Aperçu numérique</span>
                        <h3 class="audio-title" style="font-size: 0.9rem;">Extrait &amp; Notice</h3>
                        <p class="audio-author">Feuilletage certifié</p>
                    </div>
                </div>

                <div>
                    <a href="{{ route('catalog.preview-pdf', $book) }}" target="_blank" class="buy-btn" style="background: #191919; color: #D9AE5C; border-color: #191919; padding: 0.65rem 1rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span>Ouvrir l'Aperçu PDF</span>
                    </a>
                </div>
            </article>

            @if($book->is_audio_available)
            <!-- Carte Extrait Audio (Spécimen d'écoute) -->
            <article class="audio-card" aria-label="Écouter un extrait de l'audiobook">
                <div class="audio-meta">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(142, 68, 173, 0.15); color: #6C3483; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    </div>
                    <div>
                        <span class="author-label" style="font-size:0.68rem; color: #6C3483;">Extrait audio</span>
                        <h3 class="audio-title" style="font-size: 0.9rem;">Spécimen d'écoute</h3>
                        <p class="audio-author">{{ $book->audio_duration ?: 'Version audio intégrale' }}</p>
                    </div>
                </div>

                <div x-data="{
                    playing: false,
                    audio: null,
                    progress: 0,
                    init() {
                        const src = '{{ $book->audio_sample_path ? asset($book->audio_sample_path) : '' }}';
                        if (src) {
                            this.audio = new Audio(src);
                            this.audio.addEventListener('timeupdate', () => {
                                if (this.audio.duration) {
                                    this.progress = (this.audio.currentTime / this.audio.duration) * 100;
                                }
                            });
                            this.audio.addEventListener('ended', () => {
                                this.playing = false;
                                this.progress = 0;
                            });
                        }
                    },
                    toggle() {
                        if (!this.audio) return;
                        if (this.playing) {
                            this.audio.pause();
                            this.playing = false;
                        } else {
                            this.audio.play();
                            this.playing = true;
                        }
                    }
                }" class="audio-controls">
                    <button type="button" @click="toggle()" class="play-pause-btn" aria-label="Lancer l'extrait audio" style="background: #6C3483;">
                        <svg x-show="!playing" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <polygon points="5 3 19 12 5 21 5 3"></polygon>
                        </svg>
                        <svg x-show="playing" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                            <rect x="6" y="4" width="4" height="16"></rect>
                            <rect x="14" y="4" width="4" height="16"></rect>
                        </svg>
                    </button>
                    <div class="progress-wrap" style="cursor: pointer;" @click="if (audio && audio.duration) { audio.currentTime = ($event.offsetX / $el.clientWidth) * audio.duration; }">
                        <div class="progress-bar-bg">
                            <div class="progress-bar-fill" :style="'width: ' + progress + '%; background-color: #6C3483;'"></div>
                        </div>
                        <div class="time-row">
                            <span style="font-size: 0.7rem; color: #6C3483; font-weight: 700;">Extrait MP3</span>
                            <span style="font-size: 0.7rem; color: #8B857C;">{{ $book->audio_duration ?: 'Audio' }}</span>
                        </div>
                    </div>
                </div>
            </article>
            @endif

        </div>

    </section>

    <!-- 2. Section Résumé & Fiche Technique (2 colonnes) -->
    <section class="detail-content-grid" aria-label="Résumé et caractéristiques">
        
        <!-- Colonne Gauche : Résumé & Extrait Littéraire -->
        <div>
            <h2 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem;">
                Résumé de l'œuvre
            </h2>
            <div style="font-size: 1.02rem; line-height: 1.8; color: #1E1D1B; margin-bottom: 2rem;">
                {!! nl2br(e($book->description)) !!}
            </div>

            @if($book->excerpt)
                <h3 class="font-editorial" style="font-size: 1.25rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.85rem;">
                    Extrait choisi
                </h3>
                <div class="excerpt-block">
                    <template x-if="!showFullExcerpt">
                        <div>
                            {!! nl2br(e(Str::limit($book->excerpt, 400))) !!}
                            @if(strlen($book->excerpt) > 400)
                                <button @click="showFullExcerpt = true" type="button" style="display: block; margin-top: 0.75rem; color: #D9AE5C; font-weight: 700; font-size: 0.85rem; background: none; border: none; cursor: pointer; text-decoration: underline;">
                                    Lire la suite de l'extrait ›
                                </button>
                            @endif
                        </div>
                    </template>
                    <template x-if="showFullExcerpt">
                        <div>
                            {!! nl2br(e($book->excerpt)) !!}
                            <button @click="showFullExcerpt = false" type="button" style="display: block; margin-top: 0.75rem; color: #D9AE5C; font-weight: 700; font-size: 0.85rem; background: none; border: none; cursor: pointer; text-decoration: underline;">
                                Réduire l'extrait ‹
                            </button>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        <!-- Colonne Droite : Fiche Technique & Garanties -->
        <div>
            <h2 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem;">
                Fiche technique
            </h2>

            <div class="specs-container" style="margin-bottom: 1.5rem;">
                <ul class="specs-list" style="grid-template-columns: repeat(2, 1fr);">
                    @if($book->language)
                    <li class="spec-item">
                        <span class="spec-label">Langue</span>
                        <span class="spec-value">{{ $book->language }}</span>
                    </li>
                    @endif
                    @if($book->pages_count)
                    <li class="spec-item">
                        <span class="spec-label">Pages</span>
                        <span class="spec-value">{{ $book->pages_count }} pages</span>
                    </li>
                    @endif
                    @if($book->published_year)
                    <li class="spec-item">
                        <span class="spec-label">Parution</span>
                        <span class="spec-value">{{ $book->published_year }}</span>
                    </li>
                    @endif
                    @if($book->publisher)
                    <li class="spec-item">
                        <span class="spec-label">Éditeur</span>
                        <span class="spec-value">{{ $book->publisher }}</span>
                    </li>
                    @endif
                    @if($book->isbn)
                    <li class="spec-item">
                        <span class="spec-label">ISBN</span>
                        <span class="spec-value">{{ $book->isbn }}</span>
                    </li>
                    @endif
                    @if($book->weight_grams)
                    <li class="spec-item">
                        <span class="spec-label">Poids</span>
                        <span class="spec-value">{{ $book->weight_grams }} g</span>
                    </li>
                    @endif
                    @if($book->is_audio_available && $book->audio_duration)
                    <li class="spec-item">
                        <span class="spec-label">Durée audio</span>
                        <span class="spec-value">{{ $book->audio_duration }}</span>
                    </li>
                    @endif
                </ul>
            </div>

            <!-- Garanties Éditoriales -->
            <div class="trust-badges-list">
                <div class="trust-badge-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    <span>Téléchargement PDF immédiat après paiement</span>
                </div>
                <div class="trust-badge-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                    <span>Livre Audio MP3 : Écoute en ligne &amp; Téléchargement illimité</span>
                </div>
                <div class="trust-badge-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>Exemplaire numérique protégé par filigrane</span>
                </div>
                <div class="trust-badge-item">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Expédition suivie à Abidjan &amp; International</span>
                </div>
            </div>

        </div>

    </section>

    <!-- 3. Section Avis des Lecteurs -->
    <section class="detail-reviews-section" aria-label="Avis des lecteurs">
        <h2 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.5rem;">
            Avis des lecteurs ({{ $approvedCount }})
        </h2>

        <!-- Synthèse des Notes -->
        <div class="detail-reviews-summary-grid">
            
            <div style="text-align: center; border-right: 1px solid rgba(139,133,124,0.18); padding-right: 1.5rem;">
                <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 3.5rem; font-weight: 800; color: #1E1D1B; line-height: 1;">
                    {{ number_format($avgRating, 1, ',', '') }}
                </div>
                <div class="star-rating" style="margin: 0.5rem 0;">
                    @for ($i = 1; $i <= 5; $i++)
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating) ? '#D9AE5C' : 'none' }}" stroke="{{ $i <= round($avgRating) ? '#D9AE5C' : '#D5CFC5' }}" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    @endfor
                </div>
                <div style="font-size: 0.8rem; color: #8B857C;">Note moyenne sur {{ $approvedCount }} avis</div>
            </div>

            <!-- Distribution des étoiles -->
            <div>
                @php
                    $totalRev = max($approvedCount, 1);
                    $ratingCounts = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
                    if($book->approvedReviews) {
                        foreach($book->approvedReviews as $r) {
                            if(isset($ratingCounts[$r->rating])) {
                                $ratingCounts[$r->rating]++;
                            }
                        }
                    }
                @endphp
                @foreach([5, 4, 3, 2, 1] as $s)
                    @php $pct = ($ratingCounts[$s] / $totalRev) * 100; @endphp
                    <div class="rating-bar">
                        <span class="rating-bar-label">{{ $s }} étoiles</span>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill" style="width: {{ $pct }}%;"></div>
                        </div>
                        <span class="rating-bar-count">{{ $ratingCounts[$s] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Formulaire de Dépôt d'Avis -->
        <div style="background: #FFFDF9; border: 1.5px solid rgba(217,174,92,0.3); border-radius: 20px; padding: 1.75rem; margin-bottom: 2.5rem;">
            <h3 style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.25rem; font-weight: 700; color: #1E1D1B; margin-bottom: 1rem;">
                Partagez votre lecture avec la communauté
            </h3>

            <form action="{{ route('books.review', $book) }}" method="POST">
                @csrf

                <!-- Sélection de la Note -->
                <div style="margin-bottom: 1.25rem;">
                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.4rem;">
                        Votre note *
                    </label>
                    <div class="star-selector" @mouseleave="hoverRating = 0">
                        <template x-for="i in 5">
                            <button type="button" 
                                    class="star-btn"
                                    :class="{ 'active': i <= (hoverRating || rating) }"
                                    @mouseenter="hoverRating = i"
                                    @click="rating = i">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </button>
                        </template>
                        <span style="font-size: 0.85rem; font-weight: 700; color: #D9AE5C; margin-left: 0.75rem;" x-text="(hoverRating || rating) + ' / 5'"></span>
                    </div>
                    <input type="hidden" name="rating" x-model="rating">
                </div>

                <div style="margin-bottom: 1rem;">
                    <label for="customer_name" style="display: block; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">
                        Votre nom ou pseudonyme *
                    </label>
                    <input type="text" 
                           name="customer_name" 
                           id="customer_name" 
                           value="{{ old('customer_name', auth()->check() ? auth()->user()->name : '') }}" 
                           required 
                           placeholder="Ex: Koffi Emmanuel"
                           style="width: 100%; max-width: 400px; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.9rem; color: #1E1D1B; outline: none;">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="comment" style="display: block; font-size: 0.8rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.35rem;">
                        Votre appréciation *
                    </label>
                    <textarea name="comment" 
                              id="comment" 
                              rows="3" 
                              required 
                              placeholder="Qu'avez-vous pensé de cet ouvrage, de son apport et de sa portée ?"
                              style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.75rem 1rem; font-size: 0.9rem; color: #1E1D1B; outline: none;">{{ old('comment') }}</textarea>
                </div>

                <button type="submit" class="buy-btn" style="background: #191919; color: #D9AE5C; border-color: #191919; display: inline-flex; width: auto; padding: 0.75rem 2rem;">
                    <span>Publier mon avis de lecteur</span>
                </button>
            </form>
        </div>

        <!-- Liste des Avis -->
        <div class="reviews-feed">
            @if($book->approvedReviews && $book->approvedReviews->isNotEmpty())
                @foreach($book->approvedReviews as $review)
                    <div class="review-card">
                        <div class="review-header">
                            <div>
                                <div class="review-author">{{ $review->customer_name }}</div>
                                <div class="review-date">{{ \Carbon\Carbon::parse($review->created_at)->format('d F Y') }}</div>
                            </div>
                            <div class="star-rating">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="{{ $i <= $review->rating ? '#D9AE5C' : 'none' }}" stroke="{{ $i <= $review->rating ? '#D9AE5C' : '#D5CFC5' }}" stroke-width="1.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                @endfor
                            </div>
                        </div>
                        <div class="review-comment">
                            {{ $review->comment }}
                        </div>
                    </div>
                @endforeach
            @else
                <div style="text-align: center; padding: 2rem; color: #8B857C; font-style: italic;">
                    Aucun avis pour le moment. Soyez le premier à partager votre lecture !
                </div>
            @endif
        </div>
    </section>

    <!-- 4. Section Ouvrages Similaires (Structure Exacte de Bestsellers de book.index) -->
    @if(isset($relatedBooks) && $relatedBooks->isNotEmpty())
        <section class="bestsellers-section" aria-label="Ouvrages recommandés">
            
            <!-- Titre Vertical -->
            <div class="vertical-title-wrap">
                <h2 class="vertical-title font-editorial">
                    Dans la même collection
                </h2>
            </div>

            <!-- Rangée de livres (Grille de book.index) -->
            <div class="books-row">
                @foreach($relatedBooks as $relatedBook)
                    <x-book-card :book="$relatedBook" />
                @endforeach
            </div>

        </section>
    @endif

</div>
@endsection
