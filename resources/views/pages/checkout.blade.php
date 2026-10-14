@extends('layouts.app')
@section('title', 'Finaliser votre commande — L\'Étoile du Matin')
@section('meta_description', 'Renseignez vos coordonnées pour le paiement sécurisé de vos livres.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/books.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catalog.css') }}">
@endpush

@section('content')
<div x-data="{ paymentMethod: 'paystack_momo' }">

    <!-- 1. Section Héro En-tête (Style BROKS) -->
    <section class="hero-section" aria-label="En-tête de commande">
        <div class="hero-left" style="grid-column: span 2;">
            <nav class="breadcrumb" style="margin-bottom: 0.75rem;" aria-label="Fil d'Ariane">
                <ol class="breadcrumb-list">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Accueil</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Panier</a></li>
                    <li class="breadcrumb-separator">›</li>
                    <li class="breadcrumb-item active" aria-current="page">Paiement Sécurisé</li>
                </ol>
            </nav>

            <h1 class="hero-title font-editorial" style="font-size: 3rem;">
                Commander
            </h1>
            <p class="hero-subtitle">
                Renseignez vos coordonnées de facturation et de livraison pour accéder au paiement.
            </p>
        </div>

        <div class="hero-right hero-right-desktop-only">
            <article class="author-card" aria-label="Récapitulatif">
                <div>
                    <span class="author-label">Total à régler</span>
                    <h2 class="author-name font-editorial" style="font-size: 1.4rem; color: #D9AE5C;">
                        {{ number_format($cart['total'], 0, ',', ' ') }} FCFA
                    </h2>
                    <p class="author-count">{{ $cart['count'] }} article(s) à confirmer</p>
                </div>
                <div style="width: 48px; height: 48px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                    ★
                </div>
            </article>
        </div>
    </section>

    <!-- 2. Formulaire & Récapitulatif -->
    <section class="page-section-padded" aria-label="Formulaire de commande">
        <form action="{{ route('checkout.process') }}" method="POST">
            @csrf

            <div class="checkout-main-grid">
                
                <!-- Colonne Gauche : Étapes -->
                <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                    
                    <!-- Étape 1 : Informations personnelles -->
                    <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.22); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                        <h3 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 28px; height: 28px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">1</span>
                            <span>Vos Coordonnées</span>
                        </h3>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                            <div style="grid-column: 1 / -1;">
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Nom complet *</label>
                                <input type="text" name="name" value="{{ old('name', $user?->name) }}" required placeholder="Ex : Amadou Koné" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                                @error('name') <span style="color: #C0392B; font-size: 0.72rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Adresse Email *</label>
                                <input type="email" name="email" value="{{ old('email', $user?->email) }}" required placeholder="amadou@exemple.ci" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                                @error('email') <span style="color: #C0392B; font-size: 0.72rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Téléphone *</label>
                                <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="+225 07 00 00 00" style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                                @error('phone') <span style="color: #C0392B; font-size: 0.72rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Étape 2 : Adresse de livraison (si livres physiques) -->
                    @if($cart['has_physical'])
                    <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.22); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                        <h3 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 28px; height: 28px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">2</span>
                            <span>Adresse de Livraison</span>
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Zone / Commune *</label>
                                <select name="shipping_zone" required style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                                    <option value="abidjan_centre">Abidjan (Plateau, Cocody, Marcory) — 1 500 FCFA</option>
                                    <option value="abidjan_peripherie">Abidjan Périphérie (Yopougon, Abobo, Bingerville) — 2 500 FCFA</option>
                                    <option value="interieur">Intérieur de la Côte d'Ivoire (Expédition car) — 3 500 FCFA</option>
                                    <option value="international">International (DHL / Chronopost) — Sur devis</option>
                                </select>
                            </div>

                            <div>
                                <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #1E1D1B; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 0.35rem;">Adresse détaillée / Repères de livraison *</label>
                                <input type="text" name="shipping_address" value="{{ old('shipping_address') }}" required placeholder="Ex : Cocody Angré 8ème Tranche, près de la pharmacie..." style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139,133,124,0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Étape 3 : Mode de Paiement Sécurisé -->
                    <div style="background: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.22); border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
                        <h3 class="font-editorial" style="font-size: 1.25rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem; display: flex; align-items: center; gap: 0.6rem;">
                            <span style="width: 28px; height: 28px; border-radius: 50%; background: #D9AE5C; color: #191919; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 800;">{{ $cart['has_physical'] ? '3' : '2' }}</span>
                            <span>Mode de Règlement Sécurisé</span>
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <!-- Paystack Mobile Money -->
                            <label style="border: 1.5px solid rgba(217,174,92,0.4); border-radius: 16px; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: #FAF5ED;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <input type="radio" name="payment_channel" value="paystack_momo" checked style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.9rem; color: #1E1D1B;">Mobile Money (Orange, Wave, MTN, Moov)</div>
                                        <div style="font-size: 0.75rem; color: #8B857C;">Paiement instantané sécurisé par Paystack</div>
                                    </div>
                                </div>
                                <span style="font-size: 1.2rem;">📱</span>
                            </label>

                            <!-- Paystack Carte Bancaire -->
                            <label style="border: 1.5px solid rgba(139,133,124,0.2); border-radius: 16px; padding: 1rem 1.25rem; display: flex; align-items: center; justify-content: space-between; cursor: pointer; background: #FFFFFF;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <input type="radio" name="payment_channel" value="paystack_card" style="accent-color: #D9AE5C; width: 18px; height: 18px;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.9rem; color: #1E1D1B;">Carte Bancaire (Visa, Mastercard)</div>
                                        <div style="font-size: 0.75rem; color: #8B857C;">Règlement international sécurisé</div>
                                    </div>
                                </div>
                                <span style="font-size: 1.2rem;">💳</span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Colonne Droite : Récapitulatif Panier & Validation -->
                <div style="background: #F8F1E5; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 24px; padding: 1.75rem; position: sticky; top: 2rem;">
                    <h3 class="font-editorial" style="font-size: 1.3rem; font-weight: 800; color: #1E1D1B; margin-bottom: 1.25rem;">
                        Votre Commande
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 260px; overflow-y: auto; padding-right: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid rgba(139,133,124,0.14); padding-bottom: 1rem;">
                        @foreach($cart['items'] as $item)
                            @php $book = $item['book']; @endphp
                            <div style="display: flex; gap: 0.75rem; align-items: center;">
                                <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="{{ $book->title }}" style="width: 38px; height: 57px; border-radius: 6px; object-fit: cover; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.8rem; font-weight: 700; color: #1E1D1B; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $book->title }}</div>
                                    <div style="font-size: 0.7rem; color: #8B857C;">{{ $item['format'] === 'pdf' ? 'e-Book PDF' : ($item['format'] === 'audio' ? 'Livre Audio MP3' : 'Livre papier') }} × {{ $item['quantity'] }}</div>
                                </div>
                                <div style="font-weight: 700; font-size: 0.8rem; color: #1E1D1B;">{{ number_format($item['price'] * $item['quantity'], 0, ',', ' ') }} F</div>
                            </div>
                        @endforeach
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 1.5rem;">
                        <span style="font-weight: 800; font-size: 0.95rem; color: #1E1D1B;">Total final</span>
                        <span style="font-family: 'Playfair Display', Georgia, serif; font-size: 1.5rem; font-weight: 800; color: #1E1D1B;">
                            {{ number_format($cart['total'], 0, ',', ' ') }} FCFA
                        </span>
                    </div>

                    <button type="submit" class="buy-btn" style="background: #D9AE5C; color: #191919; border-color: #D9AE5C; padding: 0.85rem 1.5rem; font-size: 0.95rem; font-weight: 800; width: 100%; justify-content: center; box-shadow: 0 4px 14px rgba(217, 174, 92, 0.35);">
                        <span>Confirmer &amp; Payer ›</span>
                    </button>

                    <p style="font-size: 0.72rem; color: #8B857C; text-align: center; margin-top: 1rem; line-height: 1.4;">
                        En validant, vous acceptez les conditions de diffusion et de licence de L'Étoile du Matin.
                    </p>
                </div>

            </div>
        </form>
    </section>

</div>
@endsection
