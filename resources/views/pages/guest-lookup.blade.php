@extends('layouts.app')
@section('title', 'Suivi de commande — NSOROMMA')
@section('meta_description', 'Suivez la livraison de votre colis ou retrouvez vos e-books achetés sans compte.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    <div class="pg">
        <header class="pg-head">
            <ol class="pg-crumbs">
                <li><a href="{{ route('home') }}">Accueil</a></li>
                <li aria-current="page">Suivi de commande</li>
            </ol>
            <h1 class="pg-title">Suivre une commande</h1>
            <p class="pg-subtitle">Saisissez le numéro reçu après votre achat et l'e-mail ou le téléphone utilisé lors de la commande. Aucun compte n'est nécessaire.</p>

            <form action="{{ route('orders.lookup') }}" method="GET" class="pg-lookup" role="search">
                <div class="pg-field">
                    <label class="pg-label" for="order_number">Numéro de commande</label>
                    <input id="order_number" type="text" name="order_number" value="{{ request('order_number') }}" required maxlength="50" placeholder="EDM-20261020-A1B2C3" class="pg-input @error('order_number') is-invalid @enderror" autocomplete="off">
                    @error('order_number')<span class="pg-error">{{ $message }}</span>@enderror
                </div>
                <div class="pg-field">
                    <label class="pg-label" for="contact">E-mail ou téléphone</label>
                    <input id="contact" type="text" name="contact" value="{{ request('contact') }}" required maxlength="150" class="pg-input @error('contact') is-invalid @enderror" autocomplete="email">
                    @error('contact')<span class="pg-error">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="pg-btn">Rechercher</button>
            </form>
        </header>

        @if($order)
            <div class="pg-order-ref" style="margin: 0 0 1rem;">
                <h2 class="pg-section-title" style="margin: 0;">Commande {{ $order->order_number }}</h2>
                <span>Passée le <strong>{{ $order->created_at->format('d/m/Y') }}</strong></span>
                <span>Total <strong>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong></span>
            </div>

            @include('pages._order-details')
        @elseif(request()->filled('order_number'))
            <div class="pg-empty">
                <p class="pg-empty-title">Aucune commande trouvée</p>
                <p>Vérifiez le numéro de commande et l'e-mail ou le téléphone saisis lors de l'achat.<br>Besoin d'aide ? Notre équipe répond sur WhatsApp.</p>
                <a href="https://wa.me/2250700000001" target="_blank" rel="noopener" class="pg-btn pg-btn-ghost">Contacter l'assistance</a>
            </div>
        @endif
    </div>
@endsection
