@extends('layouts.app')
@section('title', 'Confirmation de commande — NSOROMMA')
@section('meta_description', 'Votre commande a bien été enregistrée et confirmée.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div>
    <!-- 1. Section Héro Confirmation (Style BROKS) -->
    <section class="hero-section" aria-label="Confirmation de votre commande">
        <div class="hero-left" style="grid-column: span 2;">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item"><a href="{{ route('library.index') }}">Ma Bibliothèque</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Confirmation #{{ $order->order_number }}</li>
                </ol>
            </nav>

            <div style="display: inline-flex; align-items: center; gap: 0.5rem; background: rgba(47, 111, 78, 0.12); color: #2F6F4E; padding: 0.35rem 0.85rem; border-radius: 20px; font-size: 0.78rem; font-weight: 700; margin-bottom: 0.85rem;">
                <span>✓</span> Paiement Confirmé avec Succès
            </div>

            <h1 class="hero-title font-editorial" style="font-size: 3rem;">
                Merci pour votre commande !
            </h1>
            <p class="hero-subtitle">
                Votre transaction a été validée. Un e-mail récapitulatif vous a été adressé avec tous les détails.
            </p>
        </div>

        <div class="hero-right">
            <article class="author-card" aria-label="Numéro de commande">
                <div>
                    <span class="author-label">Commande officielle</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.25rem; color: #D9AE5C;">
                        #{{ $order->order_number }}
                    </h2>
                    <p class="author-count">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA réglés</p>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #2F6F4E; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                    ✓
                </div>
            </article>
        </div>
    </section>

    <!-- 2. Section Titres Numériques & Audio Téléchargeables -->
    @if($order->downloads && $order->downloads->isNotEmpty())
        <section class="bestsellers-section" aria-label="Vos titres numériques prêts au téléchargement">
            
            <div class="vertical-title-wrap">
                <h2 class="vertical-title font-editorial">
                    Vos Livres Prêts
                </h2>
            </div>

            <div style="flex: 1;">
                <p style="font-size: 0.85rem; color: #8B857C; margin-bottom: 1.5rem;">
                    Vos exemplaires numériques certifiés et livres audio ont été générés avec vos droits d'accès. Vous pouvez les télécharger ou les écouter dès maintenant :
                </p>

                <div class="catalog-books-grid">
                    @foreach($order->downloads as $download)
                        @php $book = $download->book; @endphp
                        <div style="background: #FFFDF9; border: 1.5px solid {{ $download->isAudio() ? 'rgba(108, 52, 131, 0.35)' : 'rgba(47, 111, 78, 0.35)' }}; border-radius: 20px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.05);">
                            <div>
                                <div class="book-cover-wrap" style="aspect-ratio: 2/3; margin-bottom: 0.85rem; position: relative;">
                                    <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="{{ $book->title }}" class="book-cover-img">
                                    <div style="position: absolute; top: 8px; left: 8px;">
                                        @if($download->isAudio())
                                            <span class="format-badge audio">Livre Audio MP3</span>
                                        @else
                                            <span class="format-badge pdf">e-Book PDF</span>
                                        @endif
                                    </div>
                                </div>
                                <h3 class="book-title" style="font-size: 0.95rem; font-weight: 700; margin-bottom: 0.2rem;">{{ $book->title }}</h3>
                                <p class="book-author" style="margin-bottom: 0.75rem;">{{ $book->author }}</p>
                            </div>

                            @if($download->isAudio())
                                <a href="{{ route('library.download', $download) }}" class="buy-btn" style="background: #6C3483; color: #FFFFFF; border-color: #6C3483; font-weight: 700; padding: 0.7rem 1rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"/><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/></svg>
                                    <span>Télécharger l'Audio MP3</span>
                                </a>
                            @else
                                <a href="{{ route('library.download', $download) }}" class="buy-btn" style="background: #2F6F4E; color: #FFFFFF; border-color: #2F6F4E; font-weight: 700; padding: 0.7rem 1rem;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    <span>Télécharger mon PDF</span>
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>

        </section>
    @endif

    <!-- 3. Détails de Facturation & Expédition -->
    <section class="page-section-padded" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;" aria-label="Informations récapitulatives">
        
        <div style="background: #F8F1E5; border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(217, 174, 92, 0.2);">
            <span class="author-label" style="display: block; margin-bottom: 0.5rem;">Coordonnées Client</span>
            <h4 class="author-name font-editorial" style="font-size: 1.1rem; margin-bottom: 0.5rem;">{{ $order->customer_name }}</h4>
            <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.5;">
                Email : <strong style="color: #1E1D1B;">{{ $order->customer_email }}</strong><br>
                Téléphone : <strong style="color: #1E1D1B;">{{ $order->customer_phone ?? 'Non renseigné' }}</strong>
            </p>
        </div>

        <div style="background: #F8F1E5; border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(217, 174, 92, 0.2);">
            <span class="author-label" style="display: block; margin-bottom: 0.5rem;">Règlement Paystack</span>
            <h4 class="author-name font-editorial" style="font-size: 1.1rem; margin-bottom: 0.5rem;">Paiement Validé</h4>
            <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.5;">
                Montant total : <strong style="color: #1E1D1B;">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong><br>
                Mode : <strong style="color: #1E1D1B;">{{ $order->payment_method ?? 'Mobile Money / Carte' }}</strong>
            </p>
        </div>

        @if($order->shippingAddress)
        <div style="background: #F8F1E5; border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(217, 174, 92, 0.2);">
            <span class="author-label" style="display: block; margin-bottom: 0.5rem;">Expédition Colis</span>
            <h4 class="author-name font-editorial" style="font-size: 1.1rem; margin-bottom: 0.5rem;">Livraison en préparation</h4>
            <p style="font-size: 0.8rem; color: #8B857C; line-height: 1.5;">
                Adresse : <strong style="color: #1E1D1B;">{{ $order->shippingAddress->address_line ?? $order->shippingAddress->address }}</strong><br>
                Zone : <strong style="color: #1E1D1B;">{{ $order->shippingAddress->city }}</strong>
            </p>
        </div>
        @endif

    </section>

    <!-- 4. Actions -->
    <section style="padding: 2rem 3rem; border-top: 1px solid rgba(139, 133, 124, 0.08); text-align: center;">
        <div style="display: inline-flex; flex-wrap: wrap; gap: 1rem; justify-content: center;">
            <a href="{{ route('library.index') }}" class="buy-btn" style="background: #D9AE5C; color: #191919; border-color: #D9AE5C; width: auto; padding: 0.75rem 2rem; font-weight: 800;">
                <span>Accéder à Ma Bibliothèque</span>
            </a>

            <a href="{{ route('catalog.index') }}" class="buy-btn" style="background: transparent; color: #1E1D1B; border-color: rgba(217, 174, 92, 0.4); width: auto; padding: 0.75rem 2rem;">
                <span>Continuer à explorer le catalogue</span>
            </a>
        </div>
    </section>
</div>
@endsection
