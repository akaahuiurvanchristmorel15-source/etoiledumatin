@extends('layouts.app')
@section('title', 'Commande '.$order->order_number.' — NSOROMMA')
@section('robots', 'noindex, nofollow')
@section('meta_description', 'Le récapitulatif de votre commande NSOROMMA.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <header class="pg-head">
            @if($order->isPaid())
                <span class="pg-success-mark" aria-hidden="true">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 12.5 10 17 19 7"/></svg>
                </span>
                <p class="pg-eyebrow">Commande confirmée</p>
                <h1 class="pg-title">Merci, {{ strtok($order->customer_name, ' ') }}</h1>
                <p class="pg-subtitle">
                    Votre paiement est validé.
                    @if($order->downloads->isNotEmpty()) Vos livres numériques sont prêts à être téléchargés ci-dessous. @endif
                    @if($order->shippingAddress) Nous préparons l'envoi de vos livres papier. @endif
                    Un récapitulatif est associé à l'adresse {{ $order->customer_email }}.
                </p>
            @else
                <span class="pg-success-mark is-pending" aria-hidden="true">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <p class="pg-eyebrow">Paiement en attente</p>
                <h1 class="pg-title">Commande enregistrée</h1>
                <p class="pg-subtitle">Nous n'avons pas encore reçu la confirmation de votre paiement. Cette page se mettra à jour dès sa validation par Paystack.</p>
            @endif

            <div class="pg-order-ref">
                <span>Commande <strong>{{ $order->order_number }}</strong></span>
                <span>Passée le <strong>{{ $order->created_at->format('d/m/Y') }}</strong></span>
                <span>Total <strong>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong></span>
            </div>

            <div class="pg-actions">
                @auth
                    <a href="{{ route('library.index') }}" class="pg-btn">Ma bibliothèque</a>
                @else
                    <a href="{{ route('orders.lookup', ['order_number' => $order->order_number, 'contact' => $order->customer_email]) }}" class="pg-btn">Suivre cette commande</a>
                @endauth
                <a href="{{ route('catalog.index') }}" class="pg-btn pg-btn-ghost">Continuer mes achats</a>
            </div>
        </header>

        @include('pages._order-details')

        @guest
            <p class="pg-note" style="text-align: left; margin-top: 3rem;">Conservez votre numéro de commande : il vous permet de retrouver vos téléchargements et le suivi de livraison depuis « Suivi de commande ».</p>
        @endguest
    </div>
@endsection
