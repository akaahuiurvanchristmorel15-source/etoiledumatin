@extends('layouts.app')
@section('title', 'Suivi de commande sans compte — L\'Étoile du Matin')
@section('meta_description', 'Consultez l\'état de livraison de votre colis ou téléchargez vos e-books achetés sans compte.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div>

    <!-- 1. Section Héro Suivi (Style BROKS) -->
    <section class="hero-section" aria-label="Suivi de colis et commande">
        <div class="hero-left" style="grid-column: span 2;">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Suivi de Commande</li>
                </ol>
            </nav>

            <h1 class="hero-title font-editorial" style="font-size: 3rem;">
                Suivre une Commande
            </h1>
            <p class="hero-subtitle">
                Consultez l'état d'expédition de votre colis ou récupérez vos e-books numériques achetés en mode invité.
            </p>

            <!-- Formulaire de recherche rapide -->
            <form action="{{ route('orders.lookup') }}" method="GET" style="background: #FFFDF9; border: 1.5px solid rgba(217, 174, 92, 0.3); border-radius: 20px; padding: 1.5rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04); max-width: 600px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                    <div>
                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.35rem;">
                            Numéro de Commande *
                        </label>
                        <input type="text" name="order_number" value="{{ old('order_number', request('order_number')) }}" required placeholder="Ex : EDM-2026-AB12" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: #1E1D1B; margin-bottom: 0.35rem;">
                            Email ou Téléphone *
                        </label>
                        <input type="text" name="contact" value="{{ old('contact', request('contact')) }}" required placeholder="Ex : amadou@exemple.ci" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                    </div>
                </div>

                <button type="submit" class="buy-btn" style="background: #191919; color: #D9AE5C; border-color: #191919; width: 100%; justify-content: center; padding: 0.75rem 1.5rem; font-weight: 800;">
                    <span>Rechercher ma commande ›</span>
                </button>
            </form>
        </div>

        <div class="hero-right">
            <article class="author-card" aria-label="Assistance directe">
                <div>
                    <span class="author-label">Service Logistique</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.15rem;">Besoin d'aide ?</h2>
                    <p class="author-count">WhatsApp : +225 07 00 00 00 01</p>
                </div>
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #2F6F4E; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem;">
                    💬
                </div>
            </article>

            <article class="audio-card" aria-label="Garantie de livraison">
                <div class="audio-meta">
                    <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(217,174,92,0.15); color: #D9AE5C; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: 1.2rem;">
                        📍
                    </div>
                    <div>
                        <span class="author-label" style="font-size:0.68rem;">Expédition suivie</span>
                        <h3 class="audio-title" style="font-size: 0.9rem;">Abidjan &amp; Régions</h3>
                        <p class="audio-author">Livraison express sécurisée</p>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- 2. Résultat de la Recherche de Commande -->
    @if(isset($order) && $order)
        <section class="page-section-padded" aria-label="Détails de la commande trouvée">
            <h2 class="font-editorial" style="font-size: 1.6rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.5rem;">
                Commande #{{ $order->order_number }}
            </h2>

            <div style="background: #FFFDF9; border: 1.5px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04); margin-bottom: 2rem;">
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; padding-bottom: 1rem; border-bottom: 1px solid rgba(139, 133, 124, 0.12); margin-bottom: 1.25rem;">
                    <div>
                        <span style="font-size: 0.72rem; color: #8B857C;">Statut :</span>
                        <div><span class="stock-indicator in-stock" style="font-size: 0.85rem;">{{ ucfirst($order->status ?? 'Confirmée') }}</span></div>
                    </div>
                    <div>
                        <span style="font-size: 0.72rem; color: #8B857C;">Date :</span>
                        <div style="font-weight: 700; color: #1E1D1B;">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div>
                        <span style="font-size: 0.72rem; color: #8B857C;">Montant réglé :</span>
                        <div style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.25rem; font-weight: 800; color: #D9AE5C;">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</div>
                    </div>
                </div>

                <!-- Articles -->
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    @foreach($order->items as $item)
                        <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #1E1D1B;">
                            <span>{{ $item->book?->title ?? 'Livre' }} ({{ $item->format === 'pdf' ? 'e-Book PDF' : 'Livre Papier' }}) × {{ $item->quantity }}</span>
                            <span style="font-weight: 700;">{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Téléchargements PDF immédiats -->
            @if($order->downloads && $order->downloads->isNotEmpty())
                <h3 class="font-editorial" style="font-size: 1.3rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1rem;">
                    Vos e-Books PDF à télécharger
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.5rem;">
                    @foreach($order->downloads as $download)
                        <div style="background: #F8F1E5; border: 1.5px solid rgba(47, 111, 78, 0.35); border-radius: 18px; padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 700; color: #1E1D1B; margin-bottom: 0.25rem;">{{ $download->book?->title }}</h4>
                                <p style="font-size: 0.75rem; color: #8B857C; margin-bottom: 1rem;">Téléchargements restants : {{ max(0, $download->max_downloads - $download->download_count) }}</p>
                            </div>
                            <a href="{{ route('library.download', $download) }}" class="buy-btn" style="background: #2F6F4E; color: #fff; border-color: #2F6F4E; font-weight: 700; padding: 0.65rem 1rem;">
                                <span>Télécharger le PDF</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    @elseif(request('order_number'))
        <section style="padding: 2.5rem 3rem; border-top: 1px solid rgba(139, 133, 124, 0.08);">
            <div class="empty-state">
                <div class="empty-state-icon">
                    🔍
                </div>
                <h3 class="empty-state-title">Commande introuvable</h3>
                <p class="empty-state-desc">
                    Aucune commande ne correspond au numéro "{{ request('order_number') }}" et aux coordonnées indiquées. Vérifiez les informations saisies ou contactez notre support.
                </p>
            </div>
        </section>
    @endif

</div>
@endsection
