@extends('layouts.app')
@section('title', 'Ma bibliothèque — NSOROMMA')
@section('meta_description', 'Accédez à vos e-books et livres audio, et suivez vos commandes de livres papier.')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $formatLabels = ['pdf' => 'e-Book PDF', 'physical' => 'Livre papier', 'audio' => 'Livre audio'];
    @endphp

    <div class="pg" x-data="{ tab: @js(request('onglet') === 'commandes' ? 'orders' : 'books') }">
        <header class="pg-head">
            <p class="pg-eyebrow">Espace lecteur</p>
            <h1 class="pg-title">Ma bibliothèque</h1>
            <p class="pg-subtitle">
                @auth
                    Bonjour {{ strtok(auth()->user()->name, ' ') }} — {{ $downloads->count() }} {{ Str::plural('titre', $downloads->count()) }} numérique{{ $downloads->count() > 1 ? 's' : '' }} et {{ $orders->count() }} {{ Str::plural('commande', $orders->count()) }}.
                @else
                    Connectez-vous pour retrouver vos livres numériques et vos commandes.
                @endauth
            </p>
        </header>

        @auth
            @unless(auth()->user()->hasVerifiedEmail())
                <div class="pg-notice">
                    <span>Confirmez votre adresse e-mail <strong>{{ auth()->user()->email }}</strong> pour sécuriser votre compte.</span>
                    <form action="{{ route('verification.send') }}" method="POST">
                        @csrf
                        <button type="submit" class="pg-link">Renvoyer le lien</button>
                    </form>
                </div>
            @endunless
        @endauth

        @guest
            <div class="pg-empty">
                <p class="pg-empty-title">Votre bibliothèque vous attend</p>
                <p>Connectez-vous avec le compte utilisé lors de vos achats.<br>Commande passée sans compte ? Retrouvez-la avec son numéro.</p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: .75rem;">
                    <a href="{{ route('login') }}" class="pg-btn">Se connecter</a>
                    <a href="{{ route('orders.lookup') }}" class="pg-btn pg-btn-ghost" style="margin-top: 1.75rem;">Suivre une commande</a>
                </div>
            </div>
        @else
            <nav class="pg-tabs" role="tablist" aria-label="Sections de la bibliothèque">
                <button type="button" role="tab" class="pg-tab" :class="{ 'is-active': tab === 'books' }" :aria-selected="(tab === 'books').toString()" @click="tab = 'books'">
                    Livres numériques<span class="pg-tab-count">{{ $downloads->count() }}</span>
                </button>
                <button type="button" role="tab" class="pg-tab" :class="{ 'is-active': tab === 'orders' }" :aria-selected="(tab === 'orders').toString()" @click="tab = 'orders'">
                    Commandes<span class="pg-tab-count">{{ $orders->count() }}</span>
                </button>
            </nav>

            {{-- Livres numériques --}}
            <section x-show="tab === 'books'" role="tabpanel" aria-label="Livres numériques">
                @if($downloads->isNotEmpty())
                    <ul class="pg-shelf">
                        @foreach($downloads as $index => $download)
                            @php $book = $download->book; @endphp
                            <li class="pg-owned" style="--i: {{ $index }};">
                                <a href="{{ route('catalog.show', $book->slug) }}" class="pg-owned-cover" tabindex="-1" aria-hidden="true">
                                    <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="" loading="lazy">
                                </a>
                                <span class="pg-owned-meta">{{ $download->isAudio() ? 'Livre audio'.($book->audio_duration ? ' · '.$book->audio_duration : '') : 'e-Book PDF' }}</span>
                                <h2 class="pg-owned-title"><a href="{{ route('catalog.show', $book->slug) }}">{{ $book->title }}</a></h2>
                                <p class="pg-owned-author">{{ $book->author }}</p>

                                @if($download->canDownload())
                                    <a href="{{ route('library.download', $download) }}" class="pg-btn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><polyline points="7 10 12 15 17 10"/><path d="M5 20h14"/></svg>
                                        <span>{{ $download->isAudio() ? 'Télécharger l\'audio' : 'Télécharger le PDF' }}</span>
                                    </a>
                                @else
                                    <span class="pg-btn pg-btn-ghost" aria-disabled="true" style="cursor: default;">Quota atteint</span>
                                @endif
                                <span class="pg-owned-quota">{{ $download->download_count }} / {{ $download->max_downloads }} téléchargements utilisés</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="pg-empty">
                        <p class="pg-empty-title">Aucun livre numérique</p>
                        <p>Vos e-books et livres audio apparaîtront ici dès le paiement confirmé.</p>
                        <a href="{{ route('catalog.index', ['format' => 'pdf']) }}" class="pg-btn">Découvrir les e-books</a>
                    </div>
                @endif
            </section>

            {{-- Commandes --}}
            <section x-show="tab === 'orders'" x-cloak role="tabpanel" aria-label="Commandes" style="display: none;">
                @if($orders->isNotEmpty())
                    <div class="pg-orders">
                        @foreach($orders as $order)
                            <article class="pg-order">
                                <div class="pg-order-head">
                                    <div>
                                        <div class="pg-order-number">{{ $order->order_number }}</div>
                                        <div class="pg-order-date">{{ $order->created_at->format('d/m/Y') }}</div>
                                    </div>
                                    <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center;">
                                        <span class="pg-badge {{ $order->isPaid() ? 'is-success' : 'is-warning' }}">{{ $order->isPaid() ? 'Payée' : 'En attente de paiement' }}</span>
                                        @if($order->shippingAddress)
                                            <span class="pg-badge {{ $order->shippingAddress->status === 'delivered' ? 'is-success' : '' }}">{{ $order->shippingAddress->status_label }}</span>
                                        @endif
                                        <strong>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong>
                                    </div>
                                </div>
                                @foreach($order->items as $item)
                                    <div class="pg-mini">
                                        <img src="{{ $item->book?->cover_image ? asset($item->book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="">
                                        <div style="min-width: 0;">
                                            <div class="pg-mini-title">{{ $item->book?->title ?? 'Livre' }}</div>
                                            <div class="pg-mini-sub">{{ $formatLabels[$item->format] ?? $item->format }} × {{ $item->quantity }}</div>
                                        </div>
                                        <div class="pg-mini-price">{{ number_format($item->unit_price * $item->quantity, 0, ',', ' ') }} F</div>
                                    </div>
                                @endforeach
                                @if($order->shippingAddress?->tracking_number)
                                    <p class="pg-hint" style="margin: .75rem 0 0;">Suivi : {{ $order->shippingAddress->carrier_name }} · {{ $order->shippingAddress->tracking_number }}</p>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="pg-empty">
                        <p class="pg-empty-title">Aucune commande</p>
                        <p>Vos commandes apparaîtront ici.</p>
                        <a href="{{ route('catalog.index') }}" class="pg-btn">Parcourir le catalogue</a>
                    </div>
                @endif
            </section>
        @endguest
    </div>
@endsection
