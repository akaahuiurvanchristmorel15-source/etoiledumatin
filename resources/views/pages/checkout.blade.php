@extends('layouts.app')
@section('title', 'Paiement — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Renseignez vos coordonnées pour le paiement sécurisé de vos livres.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $subtotal = $cart['subtotal_pdf'] + $cart['subtotal_physical'] + ($cart['subtotal_audio'] ?? 0);
        $selectedZone = old('delivery_zone', $cart['shipping_zone']);
        $zonePrices = collect($zones)->map(fn (array $zone) => $zone['price']);
        $step = 1;
    @endphp

    <div class="pg"
         x-data="{
            zone: @js($selectedZone),
            prices: @js($zonePrices),
            subtotal: {{ $subtotal }},
            hasPhysical: {{ $cart['has_physical'] ? 'true' : 'false' }},
            get shipping() { return this.hasPhysical ? (this.prices[this.zone] ?? 0) : 0 },
            get total() { return this.subtotal + this.shipping },
            money(value) { return new Intl.NumberFormat('fr-FR').format(value).replace(/ /g, ' ') + ' FCFA' }
         }">
        <header class="pg-head">
            <ol class="pg-crumbs">
                <li><a href="{{ route('home') }}">Accueil</a></li>
                <li><a href="{{ route('cart.index') }}">Panier</a></li>
                <li aria-current="page">Paiement</li>
            </ol>
            <h1 class="pg-title">Paiement</h1>
            <p class="pg-subtitle">Quelques informations, puis vous serez redirigé vers Paystack pour régler en toute sécurité.</p>
        </header>

        @if($errors->any())
            <div class="pg-alert" role="alert">
                Merci de vérifier les informations suivantes :
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('checkout.process') }}" method="POST" class="pg-split">
            @csrf

            <div>
                <section class="pg-section">
                    <h2 class="pg-section-title"><span class="pg-step">0{{ $step++ }}</span> Vos coordonnées</h2>
                    <div class="pg-grid-2">
                        <div class="pg-field pg-full">
                            <label class="pg-label" for="name">Nom complet</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}" required maxlength="150" autocomplete="name" class="pg-input @error('name') is-invalid @enderror">
                            @error('name')<span class="pg-error">{{ $message }}</span>@enderror
                        </div>
                        <div class="pg-field">
                            <label class="pg-label" for="email">Adresse e-mail</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" required maxlength="150" autocomplete="email" class="pg-input @error('email') is-invalid @enderror">
                            @error('email')<span class="pg-error">{{ $message }}</span>@enderror
                            <span class="pg-hint">Vos liens de téléchargement y seront rattachés.</span>
                        </div>
                        <div class="pg-field">
                            <label class="pg-label" for="phone">Téléphone</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', $user?->phone) }}" required maxlength="30" autocomplete="tel" placeholder="+225 07 00 00 00 00" class="pg-input @error('phone') is-invalid @enderror">
                            @error('phone')<span class="pg-error">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </section>

                @if($cart['has_physical'])
                    <section class="pg-section">
                        <h2 class="pg-section-title"><span class="pg-step">0{{ $step++ }}</span> Livraison</h2>

                        <div class="pg-choices" role="radiogroup" aria-label="Zone de livraison">
                            @foreach($zones as $key => $zone)
                                <label class="pg-choice">
                                    <input type="radio" name="delivery_zone" value="{{ $key }}" x-model="zone" @checked($selectedZone === $key)>
                                    <span class="pg-choice-main">
                                        <span class="pg-choice-dot" aria-hidden="true"></span>
                                        <span>
                                            <span class="pg-choice-title">{{ $zone['name'] }}</span>
                                            <span class="pg-choice-sub">{{ $zone['delay'] }} · {{ $zone['description'] }}</span>
                                        </span>
                                    </span>
                                    <span class="pg-choice-price">{{ number_format($zone['price'], 0, ',', ' ') }} FCFA</span>
                                </label>
                            @endforeach
                        </div>
                        @error('delivery_zone')<span class="pg-error">{{ $message }}</span>@enderror

                        <div class="pg-grid-2" style="margin-top: 1.75rem;">
                            <div class="pg-field pg-full">
                                <label class="pg-label" for="address_line">Adresse</label>
                                <input id="address_line" type="text" name="address_line" value="{{ old('address_line', $user?->address) }}" required maxlength="255" autocomplete="street-address" placeholder="Quartier, rue, repère…" class="pg-input @error('address_line') is-invalid @enderror">
                                @error('address_line')<span class="pg-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="pg-field">
                                <label class="pg-label" for="city">Ville</label>
                                <input id="city" type="text" name="city" value="{{ old('city', $user?->city ?? 'Abidjan') }}" required maxlength="100" autocomplete="address-level2" class="pg-input @error('city') is-invalid @enderror">
                                @error('city')<span class="pg-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="pg-field">
                                <label class="pg-label" for="country">Pays</label>
                                <input id="country" type="text" name="country" value="{{ old('country', $user?->country ?? 'Côte d\'Ivoire') }}" required maxlength="100" autocomplete="country-name" class="pg-input @error('country') is-invalid @enderror">
                                @error('country')<span class="pg-error">{{ $message }}</span>@enderror
                            </div>
                            <div class="pg-field pg-full">
                                <label class="pg-label" for="delivery_instructions">Instructions pour le livreur <span class="pg-hint">(facultatif)</span></label>
                                <input id="delivery_instructions" type="text" name="delivery_instructions" value="{{ old('delivery_instructions') }}" maxlength="500" placeholder="Horaires, code d'accès, personne à contacter…" class="pg-input">
                            </div>
                        </div>
                    </section>
                @endif

                <section class="pg-section">
                    <h2 class="pg-section-title"><span class="pg-step">0{{ $step++ }}</span> Mode de paiement</h2>
                    <div class="pg-choices" role="radiogroup" aria-label="Mode de paiement">
                        <label class="pg-choice">
                            <input type="radio" name="payment_channel" value="paystack_momo" @checked(old('payment_channel', 'paystack_momo') === 'paystack_momo')>
                            <span class="pg-choice-main">
                                <span class="pg-choice-dot" aria-hidden="true"></span>
                                <span>
                                    <span class="pg-choice-title">Mobile Money</span>
                                    <span class="pg-choice-sub">Orange Money, MTN, Moov, Wave</span>
                                </span>
                            </span>
                        </label>
                        <label class="pg-choice">
                            <input type="radio" name="payment_channel" value="paystack_card" @checked(old('payment_channel') === 'paystack_card')>
                            <span class="pg-choice-main">
                                <span class="pg-choice-dot" aria-hidden="true"></span>
                                <span>
                                    <span class="pg-choice-title">Carte bancaire</span>
                                    <span class="pg-choice-sub">Visa, Mastercard</span>
                                </span>
                            </span>
                        </label>
                    </div>
                    @error('payment_channel')<span class="pg-error">{{ $message }}</span>@enderror

                    <div class="pg-field" style="margin-top: 1.75rem;">
                        <label class="pg-label" for="notes">Note sur la commande <span class="pg-hint">(facultatif)</span></label>
                        <textarea id="notes" name="notes" maxlength="500" rows="3" class="pg-textarea">{{ old('notes') }}</textarea>
                    </div>
                </section>
            </div>

            <aside class="pg-aside">
                <div class="pg-panel">
                    <h2 class="pg-summary-title">Votre commande</h2>

                    @foreach($cart['items'] as $item)
                        <div class="pg-mini">
                            <img src="{{ $item['book']->cover_image ? asset($item['book']->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="">
                            <div style="min-width: 0;">
                                <div class="pg-mini-title">{{ $item['book']->title }}</div>
                                <div class="pg-mini-sub">{{ $item['format_label'] }} × {{ $item['quantity'] }}</div>
                            </div>
                            <div class="pg-mini-price">{{ number_format($item['total_price'], 0, ',', ' ') }} F</div>
                        </div>
                    @endforeach

                    <div style="margin-top: 1rem; padding-top: .75rem; border-top: 1px solid var(--line);">
                        <div class="pg-row"><span>Sous-total</span><strong>{{ number_format($subtotal, 0, ',', ' ') }} FCFA</strong></div>
                        @if($cart['has_physical'])
                            <div class="pg-row"><span>Livraison</span><strong x-text="money(shipping)">{{ number_format($cart['shipping_cost'], 0, ',', ' ') }} FCFA</strong></div>
                        @endif
                        <div class="pg-row pg-row-total"><span>Total</span><strong x-text="money(total)">{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</strong></div>
                    </div>

                    <div class="pg-summary-actions">
                        <button type="submit" class="pg-btn pg-btn-block">
                            <span>Payer <span x-text="money(total)">{{ number_format($cart['total'], 0, ',', ' ') }} FCFA</span></span>
                        </button>
                        <a href="{{ route('cart.index') }}" class="pg-link" style="text-align: center;">Modifier le panier</a>
                    </div>
                    <p class="pg-note">En validant, vous acceptez les <a href="{{ route('legal.terms') }}">conditions générales de vente</a> de NSOROMMA. Vos données sont traitées selon notre <a href="{{ route('legal.privacy') }}">politique de confidentialité</a>. Paiement chiffré par Paystack.</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
