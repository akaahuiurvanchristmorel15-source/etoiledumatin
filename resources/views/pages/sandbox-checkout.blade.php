@extends('layouts.app')

@section('title', 'Simulation de paiement — NSOROMMA')
@section('meta_description', 'Environnement de simulation des paiements Paystack.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $formatLabels = ['pdf' => 'e-Book PDF', 'physical' => 'Livre papier', 'audio' => 'Livre audio'];
        $channels = [
            'Wave' => 'Paiement instantané par QR code ou numéro',
            'Orange Money' => 'Validation par code OTP (#144#)',
            'MTN Mobile Money' => 'Validation directe sur le téléphone',
            'Moov Money' => 'Paiement rapide via Flooz',
            'Carte Bancaire Visa/Mastercard' => 'Carte bancaire locale ou internationale',
        ];
        $order->loadMissing('items.book');
    @endphp

    <div class="pg">
        <header class="pg-head">
            <ol class="pg-crumbs">
                <li><a href="{{ route('home') }}">Accueil</a></li>
                <li><a href="{{ route('cart.index') }}">Panier</a></li>
                <li aria-current="page">Simulation de paiement</li>
            </ol>
            <p class="pg-eyebrow">Mode test · aucun débit réel</p>
            <h1 class="pg-title">Simulation de paiement</h1>
            <p class="pg-subtitle">Cette page remplace Paystack tant que les clés de paiement ne sont pas configurées. Choisissez un moyen de paiement pour valider la commande comme si elle avait été réglée.</p>
        </header>

        <form action="{{ route('checkout.sandbox-confirm', $order) }}" method="POST" class="pg-split">
            @csrf
            <input type="hidden" name="reference" value="{{ $ref }}">

            <section>
                <h2 class="pg-section-title">Moyen de paiement à simuler</h2>
                <div class="pg-choices" role="radiogroup" aria-label="Moyen de paiement à simuler">
                    @foreach($channels as $channel => $description)
                        <label class="pg-choice">
                            <input type="radio" name="payment_channel" value="{{ $channel }}" @checked(old('payment_channel', 'Wave') === $channel)>
                            <span class="pg-choice-main">
                                <span class="pg-choice-dot" aria-hidden="true"></span>
                                <span>
                                    <span class="pg-choice-title">{{ str_starts_with($channel, 'Carte') ? 'Carte Visa / Mastercard' : $channel }}</span>
                                    <span class="pg-choice-sub">{{ $description }}</span>
                                </span>
                            </span>
                            <span class="pg-hint">{{ str_starts_with($channel, 'Carte') ? 'Carte' : 'Mobile Money' }}</span>
                        </label>
                    @endforeach
                </div>
                @error('payment_channel')<span class="pg-error">{{ $message }}</span>@enderror
                @error('reference')<span class="pg-error">{{ $message }}</span>@enderror
            </section>

            <aside class="pg-aside">
                <div class="pg-panel">
                    <h2 class="pg-summary-title">Commande</h2>
                    <div class="pg-row"><span>Numéro</span><strong>{{ $order->order_number }}</strong></div>
                    <div class="pg-row"><span>Client</span><strong>{{ $order->customer_name }}</strong></div>
                    <div class="pg-row"><span>E-mail</span><strong style="overflow-wrap: anywhere;">{{ $order->customer_email }}</strong></div>

                    <div style="margin-top: 1rem; padding-top: .5rem; border-top: 1px solid var(--line);">
                        @foreach($order->items as $item)
                            <div class="pg-mini">
                                <img src="{{ $item->book?->cover_image ? asset($item->book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="">
                                <div style="min-width: 0;">
                                    <div class="pg-mini-title">{{ $item->book?->title ?? 'Livre' }}</div>
                                    <div class="pg-mini-sub">{{ $formatLabels[$item->format] ?? $item->format }} × {{ $item->quantity }}</div>
                                </div>
                                <div class="pg-mini-price">{{ number_format($item->total_price, 0, ',', ' ') }} F</div>
                            </div>
                        @endforeach
                    </div>

                    @if($order->shipping_cost > 0)
                        <div class="pg-row"><span>Livraison</span><strong>{{ number_format($order->shipping_cost, 0, ',', ' ') }} FCFA</strong></div>
                    @endif
                    <div class="pg-row pg-row-total"><span>Total</span><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong></div>

                    <div class="pg-summary-actions">
                        <button type="submit" class="pg-btn pg-btn-block">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="5 12.5 10 17 19 7"/></svg>
                            <span>Valider le paiement simulé</span>
                        </button>
                        <a href="{{ route('cart.index') }}" class="pg-link" style="text-align: center;">Annuler et revenir au panier</a>
                    </div>
                    <p class="pg-note">Environnement de développement uniquement : cette page est indisponible en production.</p>
                </div>
            </aside>
        </form>
    </div>
@endsection
