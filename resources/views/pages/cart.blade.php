@extends('layouts.app')
@section('title', 'Panier — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Consultez et validez les livres numériques ou papier de votre panier.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $subtotal = $cart['subtotal_pdf'] + $cart['subtotal_physical'] + ($cart['subtotal_audio'] ?? 0);
    @endphp

    <div class="pg" data-live="cart">
        <header class="pg-head">
            <ol class="pg-crumbs">
                <li><a href="{{ route('home') }}">Accueil</a></li>
                <li><a href="{{ route('catalog.index') }}">Catalogue</a></li>
                <li aria-current="page">Panier</li>
            </ol>
            <h1 class="pg-title">Panier</h1>
            <p class="pg-subtitle">
                @if($cart['count'] > 0)
                    {{ $cart['count'] }} {{ Str::plural('article', $cart['count']) }} · {{ number_format($subtotal, 0, ',', ' ') }} FCFA
                @else
                    Votre panier est vide pour le moment.
                @endif
            </p>
        </header>

        @if($cart['count'] === 0)
            <div class="pg-empty">
                <p class="pg-empty-title">Rien ici pour l'instant</p>
                <p>Parcourez le catalogue et ajoutez les livres qui vous inspirent.</p>
                <a href="{{ route('catalog.index') }}" class="pg-btn">Découvrir le catalogue</a>
            </div>
        @else
            <div class="pg-split">
                <section aria-label="Articles du panier">
                    @foreach($cart['items'] as $item)
                        @php $book = $item['book']; @endphp
                        <article class="pg-line">
                            <a href="{{ route('catalog.show', $book->slug) }}" class="pg-line-cover" tabindex="-1" aria-hidden="true">
                                <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="">
                            </a>

                            <div>
                                <span class="pg-line-format">{{ $item['format_label'] }}</span>
                                <a href="{{ route('catalog.show', $book->slug) }}" class="pg-line-title">{{ $book->title }}</a>
                                <p class="pg-line-author">{{ $book->author }}</p>

                                <div class="pg-line-tools">
                                    @if($item['format'] === 'physical')
                                        <form action="{{ route('cart.update') }}" method="POST" class="pg-qty">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                                            <input type="hidden" name="format" value="physical">
                                            <label for="qty-{{ $book->id }}">Quantité</label>
                                            <select id="qty-{{ $book->id }}" name="quantity" data-autosubmit>
                                                @for($quantity = 1; $quantity <= max(1, min(20, $book->stock_physical)); $quantity++)
                                                    <option value="{{ $quantity }}" @selected($item['quantity'] == $quantity)>{{ $quantity }}</option>
                                                @endfor
                                            </select>
                                        </form>
                                        <span class="pg-stock {{ $book->stockLevel() !== 'ok' ? 'is-low' : '' }}">{{ $book->stockLabel() }}</span>
                                    @else
                                        <span class="pg-hint">{{ $item['format'] === 'audio' ? 'Écoute en streaming depuis votre bibliothèque' : 'Téléchargement immédiat après paiement' }}</span>
                                    @endif

                                    <form action="{{ route('cart.remove') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <input type="hidden" name="format" value="{{ $item['cart_key'] }}">
                                        <button type="submit" class="pg-link pg-link-danger" aria-label="Retirer {{ $book->title }} du panier">Retirer</button>
                                    </form>
                                </div>
                            </div>

                            <div class="pg-line-price">
                                {{ number_format($item['total_price'], 0, ',', ' ') }} FCFA
                                @if($item['quantity'] > 1)
                                    <small>{{ number_format($item['unit_price'], 0, ',', ' ') }} FCFA l'unité</small>
                                @endif
                            </div>
                        </article>
                    @endforeach

                    <div class="pg-line-tools" style="justify-content: space-between; margin-top: 1.5rem;">
                        <a href="{{ route('catalog.index') }}" class="pg-link">← Continuer mes achats</a>
                        <form action="{{ route('cart.clear') }}" method="POST" data-confirm="Vider entièrement le panier ?">
                            @csrf
                            <button type="submit" class="pg-link">Vider le panier</button>
                        </form>
                    </div>
                </section>

                <aside class="pg-aside">
                    <div class="pg-panel">
                        <h2 class="pg-summary-title">Récapitulatif</h2>
                        @if($cart['subtotal_pdf'] > 0)
                            <div class="pg-row"><span>e-Books PDF</span><strong>{{ number_format($cart['subtotal_pdf'], 0, ',', ' ') }} FCFA</strong></div>
                        @endif
                        @if(($cart['subtotal_audio'] ?? 0) > 0)
                            <div class="pg-row"><span>Écoute audio</span><strong>{{ number_format($cart['subtotal_audio'], 0, ',', ' ') }} FCFA</strong></div>
                        @endif
                        @if($cart['subtotal_physical'] > 0)
                            <div class="pg-row"><span>Livres papier</span><strong>{{ number_format($cart['subtotal_physical'], 0, ',', ' ') }} FCFA</strong></div>
                        @endif
                        <div class="pg-row">
                            <span>Livraison</span>
                            <strong>{{ $cart['has_physical'] ? 'À l\'étape suivante' : 'Aucune (numérique)' }}</strong>
                        </div>
                        <div class="pg-row pg-row-total"><span>Sous-total</span><strong>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</strong></div>

                        <div class="pg-summary-actions">
                            <a href="{{ route('checkout.index') }}" class="pg-btn pg-btn-block">
                                <span>Passer au paiement</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                        <p class="pg-note">Paiement sécurisé par Paystack — Mobile Money ou carte bancaire.</p>
                    </div>
                </aside>
            </div>
        @endif
    </div>
@endsection
