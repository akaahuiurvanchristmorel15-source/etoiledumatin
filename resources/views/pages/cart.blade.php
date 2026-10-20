@extends('layouts.app')
@section('title', 'Votre Panier — NSOROMMA')
@section('meta_description', 'Consultez et validez les livres numériques ou physiques de votre panier.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div>
    <!-- 1. Section Héro Panier (Style BROKS) -->
    <section class="hero-section" aria-label="En-tête de votre panier">
        <div class="hero-left" style="grid-column: span 2;">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item"><a href="{{ route('catalog.index') }}">Catalogue</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Votre Panier</li>
                </ol>
            </nav>

            <div style="display: flex; align-items: baseline; gap: 0.75rem; flex-wrap: wrap;">
                <h1 class="hero-title font-editorial" style="font-size: 2.8rem; margin-bottom: 0.25rem;">
                    Votre Panier
                </h1>
                @if($cart['count'] > 0)
                    <span style="font-size: 0.85rem; font-weight: 800; color: #D9AE5C; background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.35); padding: 0.2rem 0.75rem; border-radius: 20px;">
                        {{ $cart['count'] }} article{{ $cart['count'] > 1 ? 's' : '' }}
                    </span>
                @endif
            </div>

            <p class="hero-subtitle" style="margin-bottom: 0.5rem;">
                Vérifiez vos articles avant de finaliser votre commande en toute sérénité.
            </p>
        </div>

        <!-- Récapitulatif Express visible UNIQUEMENT sur Desktop/Tablette (masqué sur mobile pour libérer l'écran) -->
        <div class="hero-right hero-right-desktop-only">
            <article class="author-card" aria-label="Récapitulatif express">
                <div>
                    <span class="author-label">Total du panier</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.4rem; color: #D9AE5C;">
                        {{ number_format($cart['total'], 0, ',', ' ') }} FCFA
                    </h2>
                    <p class="author-count">{{ $cart['count'] }} article(s) sélectionné(s)</p>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                    ★
                </div>
            </article>
        </div>
    </section>

    <!-- 2. Section Contenu du Panier -->
    <section class="page-section-padded" aria-label="Articles du panier">
        
        @if($cart['count'] === 0)
            <div class="empty-state">
                <div class="empty-state-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <h2 class="empty-state-title">Votre panier est vide</h2>
                <p class="empty-state-desc">
                    Explorez notre catalogue pour découvrir des œuvres fondamentales de spiritualité, philosophie et politique africaine.
                </p>
                <a href="{{ route('catalog.index') }}" class="empty-state-action">
                    <span>Parcourir le catalogue</span>
                </a>
            </div>
        @else
            <div class="cart-main-grid">
                
                <!-- Liste des Articles -->
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach($cart['items'] as $item)
                        @php $book = $item['book']; @endphp
                        <div class="cart-item-card">
                            
                            <!-- Ligne principale : Couverture + Détails -->
                            <div class="cart-item-main">
                                <!-- Couverture -->
                                <a href="{{ route('catalog.show', $book->slug) }}" class="cart-item-cover" aria-label="Voir la fiche de {{ $book->title }}">
                                    <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="Couverture de {{ $book->title }}" onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                                </a>

                                <!-- Détails de l'ouvrage -->
                                <div class="cart-item-details">
                                    <div class="cart-item-badges">
                                        @if($item['format'] === 'pdf')
                                            <span class="format-badge pdf">e-Book Numérique (PDF)</span>
                                        @elseif($item['format'] === 'audio')
                                            <span class="format-badge audio">Livre Audio (MP3)</span>
                                        @else
                                            <span class="format-badge physical">Livre Papier Broché</span>
                                        @endif
                                        <span style="font-size: 0.75rem; color: #8B857C; font-weight: 600;">{{ $book->author }}</span>
                                    </div>

                                    <h3 class="cart-item-title">
                                         <a href="{{ route('catalog.show', $book->slug) }}">{{ $book->title }}</a>
                                    </h3>

                                    <div class="cart-item-price" style="color: {{ $item['format'] === 'pdf' ? '#2F6F4E' : ($item['format'] === 'audio' ? '#6C3483' : '#C59B4B') }};">
                                        {{ number_format($item['unit_price'] ?? $item['price'] ?? 0, 0, ',', ' ') }} <span style="font-size: 0.82rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; color: #8B857C;">FCFA</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Ligne d'actions dédiée : Quantité & Suppression (Parfaitement accessible sur mobile) -->
                            <div class="cart-item-actions-bar">
                                @if($item['format'] === 'physical')
                                    <form action="{{ route('cart.update') }}" method="POST" class="cart-qty-pill">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <input type="hidden" name="format" value="physical">
                                        <span style="font-size: 0.74rem; color: #767067; font-weight: 700;">Quantité :</span>
                                        <select name="quantity" onchange="this.form.submit()" aria-label="Quantité pour {{ $book->title }}">
                                            @for($i = 1; $i <= max(1, min(10, $book->stock_physical ?? 10)); $i++)
                                                <option value="{{ $i }}" {{ $item['quantity'] == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </form>
                                @elseif($item['format'] === 'audio')
                                    <div class="cart-qty-pill" style="background: rgba(108, 52, 131, 0.08); border-color: rgba(108, 52, 131, 0.22); color: #6C3483;">
                                        <span style="font-size: 0.74rem; font-weight: 700;">Format Audio (1 licence)</span>
                                    </div>
                                @else
                                    <div class="cart-qty-pill" style="background: rgba(47, 111, 78, 0.08); border-color: rgba(47, 111, 78, 0.22); color: #2F6F4E;">
                                        <span style="font-size: 0.74rem; font-weight: 700;">Format e-Book (1 exemplaire)</span>
                                    </div>
                                @endif

                                <form action="{{ route('cart.remove') }}" method="POST" style="margin: 0;">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                                    <input type="hidden" name="format" value="{{ $item['format'] }}">
                                    <button type="submit" class="cart-remove-btn" title="Retirer {{ $book->title }} du panier">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6"></polyline>
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                            <line x1="10" y1="11" x2="10" y2="17"></line>
                                            <line x1="14" y1="11" x2="14" y2="17"></line>
                                        </svg>
                                        <span>Retirer</span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    @endforeach

                    <!-- Liens de navigation du panier -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.85rem; margin-top: 0.75rem; padding: 0.25rem 0.5rem;">
                        <a href="{{ route('catalog.index') }}" style="color: #D9AE5C; font-weight: 700; font-size: 0.85rem; text-decoration: underline; display: inline-flex; align-items: center; gap: 0.35rem;">
                            <span>← Continuer mes achats</span>
                        </a>

                        <form action="{{ route('cart.clear') }}" method="POST" style="margin: 0;" onsubmit="return confirm('Confirmez-vous le vidage complet du panier ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #8B857C; font-size: 0.8rem; cursor: pointer; text-decoration: underline; padding: 0.4rem;">
                                Vider le panier
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Panneau Récapitulatif & Passage en Caisse -->
                <div class="cart-summary-box">
                    <h3 class="font-editorial" style="font-size: 1.35rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem;">
                        Récapitulatif
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem; font-size: 0.88rem; padding-bottom: 1.15rem; border-bottom: 1px solid rgba(139, 133, 124, 0.15); margin-bottom: 1.15rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #767067;">
                            <span>Sous-total articles</span>
                            <span style="font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; color: #767067;">
                            <span>Frais de livraison</span>
                            <span style="font-weight: 700; color: #2F6F4E; font-size: 0.8rem; background: rgba(47, 111, 78, 0.08); padding: 0.2rem 0.55rem; border-radius: 8px;">
                                {{ $cart['has_physical'] ? 'Calculé à l\'étape suivante' : 'Gratuit (Numérique)' }}
                            </span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.5rem;">
                        <span style="font-weight: 800; font-size: 1.05rem; color: #1E1D1B;">Total à régler</span>
                        <span class="font-editorial" style="font-size: 1.65rem; font-weight: 800; color: #1E1D1B;">
                            {{ number_format($cart['total'], 0, ',', ' ') }} <span style="font-size: 0.85rem; font-family: 'Plus Jakarta Sans', sans-serif; font-weight: 700; color: #8B857C;">FCFA</span>
                        </span>
                    </div>

                    <a href="{{ route('checkout.index') }}" class="buy-btn" style="background: #191919; color: #D9AE5C; border: 1.5px solid #191919; padding: 0.95rem 1.5rem; font-size: 0.95rem; font-weight: 800; text-align: center; justify-content: center; border-radius: 16px; box-shadow: 0 4px 14px rgba(25, 25, 25, 0.2); transition: all 0.2s ease;">
                        <span>Procéder au paiement ›</span>
                    </a>

                    <div style="margin-top: 1.5rem; display: flex; flex-direction: column; gap: 0.55rem; font-size: 0.75rem; color: #767067;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #2F6F4E; font-weight: 800;">✓</span>
                            <span>Paiement sécurisé Mobile Money (Wave, Orange, MTN, Moov) &amp; Cartes</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="color: #2F6F4E; font-weight: 800;">✓</span>
                            <span>Téléchargement PDF &amp; Audio immédiats avec licence certifiée</span>
                        </div>
                    </div>
                </div>

            </div>
        @endif

    </section>

    <!-- 3. Section Réassurance (Compacte & Élégante sur Desktop comme sur Mobile) -->
    <section class="page-section-padded" aria-label="Nos engagements">
        <div class="cart-reassurance-grid">
            <div class="author-card cart-reassurance-card" style="padding: 1rem 1.25rem;">
                <div>
                    <span class="author-label" style="font-size: 0.68rem;">Sécurité garantie</span>
                    <h4 class="author-name font-editorial" style="font-size: 0.95rem;">Paiement Sécurisé</h4>
                    <p class="author-count" style="font-size: 0.75rem;">Wave, Orange, MTN, Moov &amp; Cartes Paystack</p>
                </div>
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                    🔒
                </div>
            </div>

            <div class="author-card cart-reassurance-card" style="padding: 1rem 1.25rem;">
                <div>
                    <span class="author-label" style="font-size: 0.68rem;">Format Numérique</span>
                    <h4 class="author-name font-editorial" style="font-size: 0.95rem;">e-Book Haute Fidélité</h4>
                    <p class="author-count" style="font-size: 0.75rem;">Téléchargement instantané et filigrané</p>
                </div>
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #2F6F4E; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                    ⚡
                </div>
            </div>

            <div class="author-card cart-reassurance-card" style="padding: 1rem 1.25rem;">
                <div>
                    <span class="author-label" style="font-size: 0.68rem;">Format Physique</span>
                    <h4 class="author-name font-editorial" style="font-size: 0.95rem;">Expédition Soignée</h4>
                    <p class="author-count" style="font-size: 0.75rem;">Livraison suivie Abidjan &amp; Intérieur</p>
                </div>
                <div style="width: 38px; height: 38px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0;">
                    📦
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
