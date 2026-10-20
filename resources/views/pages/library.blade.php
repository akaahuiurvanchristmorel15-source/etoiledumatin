@extends('layouts.app')
@section('title', 'Ma Bibliothèque & Commandes — NSOROMMA')
@section('meta_description', 'Accédez à vos e-books PDF certifiés et suivez l\'état de vos commandes physiques.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div x-data="{ activeTab: 'downloads' }">

    <!-- 1. Section Héro Bibliothèque (Style BROKS) -->
    <section class="hero-section" aria-label="Espace lecteur et bibliothèque">
        <div class="hero-left" style="grid-column: span 2;">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Ma Bibliothèque</li>
                </ol>
            </nav>

            <h1 class="hero-title font-editorial" style="font-size: 3rem;">
                Ma Bibliothèque
            </h1>
            <p class="hero-subtitle">
                Retrouvez vos éditions numériques certifiées et consultez vos commandes d'ouvrages physiques.
            </p>
        </div>

        <div class="hero-right">
            @auth
                <article class="author-card" aria-label="Profil lecteur">
                    <div>
                        <span class="author-label">Lecteur privilégié</span>
                        <h2 class="author-name font-editorial" style="font-size: 1.15rem;">{{ auth()->user()->name }}</h2>
                        <p class="author-count">{{ $downloads->count() }} ouvrage(s) numérique(s)</p>
                    </div>
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem; border: 3px solid #FFFDF9;">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                </article>
            @else
                <article class="author-card" aria-label="Espace Invité">
                    <div>
                        <span class="author-label">Espace Invité</span>
                        <h2 class="author-name font-editorial" style="font-size: 1.15rem;">Mes Téléchargements</h2>
                        <p class="author-count">Session active</p>
                    </div>
                    <div style="width: 52px; height: 52px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                        ★
                    </div>
                </article>
            @endauth
        </div>
    </section>

    <!-- 2. Barre d'Onglets Horizontaux (Style Exact de book.index) -->
    <section class="catalog-subnav-section">
        <div class="catalog-tabs-scroll">
            <div class="nav-tabs" style="background-color: #EFE4D3; display: inline-flex; white-space: nowrap;">
                <button type="button" 
                        @click="activeTab = 'downloads'" 
                        class="nav-tab" 
                        :class="activeTab === 'downloads' ? 'active' : ''">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1-2.5-2.5Z"/></svg>
                    <span>Mes Titres Numériques &amp; Audio ({{ $downloads->count() }})</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'orders'" 
                        class="nav-tab" 
                        :class="activeTab === 'orders' ? 'active' : ''">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    <span>Historique des Commandes ({{ $orders->count() }})</span>
                </button>
            </div>
        </div>
    </section>

    <!-- 3. Rayonnage des Titres Numériques & Audio (Structure Bestsellers de book.index) -->
    <div x-show="activeTab === 'downloads'" x-transition>
        <section class="bestsellers-section" aria-label="Mes titres numériques et audio">
            
            <div class="vertical-title-wrap">
                <h2 class="vertical-title font-editorial">
                    Mes Titres
                </h2>
            </div>

            <div style="flex: 1;">
                @if($downloads->isNotEmpty())
                    <div class="catalog-books-grid">
                        @foreach($downloads as $download)
                            @php $book = $download->book; @endphp
                            <div style="background: #FFFDF9; border: 1px solid {{ $download->isAudio() ? 'rgba(108, 52, 131, 0.28)' : 'rgba(217, 174, 92, 0.22)' }}; border-radius: 20px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 6px 18px rgba(60, 45, 25, 0.05); transition: transform 0.25s ease;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                                <div>
                                    <div class="book-cover-wrap" style="aspect-ratio: 2/3; margin-bottom: 0.85rem; position: relative;">
                                        <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="{{ $book->title }}" class="book-cover-img">
                                        <div style="position: absolute; top: 8px; left: 8px;">
                                            @if($download->isAudio())
                                                <span class="format-badge audio">Audio MP3</span>
                                            @else
                                                <span class="format-badge pdf">e-Book PDF</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h3 class="book-title" style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.2rem;">
                                        <a href="{{ route('catalog.show', $book->slug) }}" style="color: inherit; text-decoration: none;">{{ $book->title }}</a>
                                    </h3>
                                    <p class="book-author" style="margin-bottom: 0.5rem;">{{ $book->author }}</p>

                                    <div style="font-size: 0.72rem; color: #8B857C; margin-bottom: 1rem;">
                                        Téléchargements : <strong style="color: #1E1D1B;">{{ $download->download_count }}</strong> / {{ $download->max_downloads }}
                                        @if($download->isAudio() && $book->audio_duration)
                                            <span style="display: block; margin-top: 2px; color: #6C3483; font-weight: 600;">Durée : {{ $book->audio_duration }}</span>
                                        @endif
                                    </div>
                                </div>

                                @if($download->isAudio())
                                    <a href="{{ route('library.download', $download) }}" class="buy-btn" style="background: #6C3483; color: #FFFFFF; border-color: #6C3483; font-weight: 700; padding: 0.65rem 1rem;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                                        <span>Télécharger l'Audio MP3</span>
                                    </a>
                                @else
                                    <a href="{{ route('library.download', $download) }}" class="buy-btn" style="background: #2F6F4E; color: #FFFFFF; border-color: #2F6F4E; font-weight: 700; padding: 0.65rem 1rem;">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                        <span>Télécharger le PDF</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            📖
                        </div>
                        <h3 class="empty-state-title">Aucun titre numérique pour le moment</h3>
                        <p class="empty-state-desc">
                            Vos livres numériques et audiobooks achetés apparaîtront automatiquement ici avec leurs liens de téléchargement sécurisés et permanents.
                        </p>
                        <a href="{{ route('catalog.index') }}" class="empty-state-action">
                            <span>Découvrir le Catalogue</span>
                        </a>
                    </div>
                @endif
            </div>

        </section>
    </div>

    <!-- 4. Historique des Commandes Physiques & Envois -->
    <div x-show="activeTab === 'orders'" x-transition style="display: none;">
        <section class="page-section-padded" aria-label="Historique des commandes">
            <h2 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.5rem;">
                Vos Commandes &amp; Livraisons
            </h2>

            @if($orders->isNotEmpty())
                <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                    @foreach($orders as $order)
                        <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.22); border-radius: 20px; padding: 1.5rem 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                            <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(139, 133, 124, 0.12); margin-bottom: 1rem;">
                                <div>
                                    <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #8B857C;">Commande</span>
                                    <h4 style="font-size: 1.1rem; font-weight: 800; color: #1E1D1B;">#{{ $order->order_number }}</h4>
                                </div>
                                <div>
                                    <span style="font-size: 0.72rem; color: #8B857C;">Date :</span>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: #1E1D1B;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                                <div>
                                    <span style="font-size: 0.72rem; color: #8B857C;">Total :</span>
                                    <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.2rem; font-weight: 800; color: #D9AE5C;">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</div>
                                </div>
                                <div>
                                    <span class="stock-indicator in-stock" style="font-size: 0.78rem;">{{ ucfirst($order->status ?? 'Confirmée') }}</span>
                                </div>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                @foreach($order->items as $item)
                                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #1E1D1B;">
                                        <span>{{ $item->book?->title ?? 'Livre' }} ({{ $item->format === 'pdf' ? 'e-Book PDF' : ($item->format === 'audio' ? 'Livre Audio MP3' : 'Livre Papier') }}) × {{ $item->quantity }}</span>
                                        <span style="font-weight: 700;">{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state-icon">
                        📦
                    </div>
                    <h3 class="empty-state-title">Aucune commande enregistrée</h3>
                    <p class="empty-state-desc">
                        Vous n'avez pas encore passé de commande d'ouvrages imprimés ou numériques.
                    </p>
                    <a href="{{ route('catalog.index') }}" class="empty-state-action">
                        <span>Explorer le catalogue</span>
                    </a>
                </div>
            @endif
        </section>
    </div>

</div>
@endsection
