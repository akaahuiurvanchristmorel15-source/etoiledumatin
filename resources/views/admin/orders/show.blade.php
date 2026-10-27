@extends('layouts.admin')
@section('title', 'Commande '.$order->order_number)

@section('content')
    @php
        $shipping = $order->shippingAddress;
    @endphp

    <div class="page-header">
        <div>
            <nav class="breadcrumb" aria-label="Fil d'Ariane">
                <a href="{{ route('admin.orders.index') }}">Commandes</a><span>/</span><span class="mono">{{ $order->order_number }}</span>
            </nav>
            <h1 class="page-title" style="display: flex; flex-wrap: wrap; align-items: center; gap: .6rem;">
                <span class="mono" style="font-size: 1.25rem;">{{ $order->order_number }}</span>
                @include('admin.partials.payment-badge', ['status' => $order->payment_status])
            </h1>
            <p class="page-subtitle">
                Passée le {{ $order->created_at->format('d/m/Y à H:i') }}
                @if($order->paid_at) · payée le {{ $order->paid_at->format('d/m/Y à H:i') }} @endif
            </p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">
                <x-admin.icon name="arrow-left" />
                <span>Retour</span>
            </a>
        </div>
    </div>

    <div class="grid-aside">
        <div class="stack">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Articles ({{ $order->items->count() }})</h2></div>
                <div class="card-body">
                    @foreach($order->items as $item)
                        <div class="list-item">
                            <div class="book-cell">
                                <img src="{{ $item->book?->cover_image ? asset($item->book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="" class="book-thumb">
                                <div style="min-width: 0;">
                                    <div class="cell-title">{{ $item->book_title }}</div>
                                    <div class="cell-sub">{{ $item->formatLabel() }} · {{ number_format($item->unit_price, 0, ',', ' ') }} F × {{ $item->quantity }}</div>
                                </div>
                            </div>
                            <div class="text-strong" style="white-space: nowrap;">{{ number_format($item->subtotal, 0, ',', ' ') }} F</div>
                        </div>
                    @endforeach

                    <div style="margin-top: 1rem;">
                        @if($order->subtotal_pdf > 0)
                            <div class="summary-row"><span>Sous-total PDF</span><span>{{ number_format($order->subtotal_pdf, 0, ',', ' ') }} F</span></div>
                        @endif
                        @if(($order->subtotal_audio ?? 0) > 0)
                            <div class="summary-row"><span>Sous-total audio</span><span>{{ number_format($order->subtotal_audio, 0, ',', ' ') }} F</span></div>
                        @endif
                        @if($order->subtotal_physical > 0)
                            <div class="summary-row"><span>Sous-total papier</span><span>{{ number_format($order->subtotal_physical, 0, ',', ' ') }} F</span></div>
                        @endif
                        <div class="summary-row"><span>Livraison</span><span>{{ number_format($order->shipping_cost, 0, ',', ' ') }} F</span></div>
                        <div class="summary-row total"><span>Total</span><span>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</span></div>
                    </div>
                </div>
            </section>

            @if($order->downloads->isNotEmpty())
                <section class="card">
                    <div class="card-header">
                        <div>
                            <h2 class="card-title">Accès numériques</h2>
                            <p class="card-subtitle">Licences de téléchargement générées après paiement.</p>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($order->downloads as $download)
                            <div class="list-item">
                                <div>
                                    <div class="cell-title">{{ $download->book?->title ?? 'Ouvrage' }}</div>
                                    <div class="cell-sub">{{ $download->isAudio() ? 'Livre audio' : 'e-Book PDF' }}@if($download->last_downloaded_at) · dernier téléchargement {{ $download->last_downloaded_at->diffForHumans() }}@endif</div>
                                </div>
                                <span class="badge {{ $download->canDownload() ? '' : 'badge-danger' }}">{{ $download->download_count }} / {{ $download->max_downloads }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($order->notes)
                <section class="card">
                    <div class="card-header"><h2 class="card-title">Note du client</h2></div>
                    <div class="card-body"><p style="white-space: pre-line;">{{ $order->notes }}</p></div>
                </section>
            @endif
        </div>

        <aside class="stack">
            <section class="card">
                <div class="card-header"><h2 class="card-title">Client</h2></div>
                <div class="card-body">
                    <dl class="dl">
                        <div><dt>Nom</dt><dd>{{ $order->customer_name }}</dd></div>
                        <div><dt>E-mail</dt><dd><a class="link" href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a></dd></div>
                        <div><dt>Téléphone</dt><dd><a href="tel:{{ preg_replace('/\s+/', '', $order->customer_phone) }}">{{ $order->customer_phone }}</a></dd></div>
                        <div><dt>Compte</dt><dd>{{ $order->user ? 'Client inscrit' : 'Commande invité' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="card">
                <div class="card-header"><h2 class="card-title">Paiement</h2></div>
                <div class="card-body">
                    <dl class="dl">
                        <div><dt>Moyen</dt><dd>{{ $order->payment_method ?? '—' }}</dd></div>
                        <div><dt>Référence</dt><dd class="mono">{{ $order->payment_reference ?? '—' }}</dd></div>
                    </dl>
                </div>
            </section>

            <section class="card">
                <div class="card-header">
                    <h2 class="card-title">Expédition</h2>
                    @include('admin.partials.shipping-badge', ['shipping' => $shipping])
                </div>
                @if($shipping)
                    <div class="card-body">
                        <dl class="dl">
                            <div><dt>Destinataire</dt><dd>{{ $shipping->recipient_name }} · {{ $shipping->phone }}</dd></div>
                            <div><dt>Adresse</dt><dd>{{ $shipping->address_line }}<br>{{ $shipping->city }}, {{ $shipping->country }}</dd></div>
                            <div><dt>Zone</dt><dd>{{ $shipping->zone_label }}</dd></div>
                            @if($shipping->instructions)
                                <div><dt>Instructions</dt><dd>{{ $shipping->instructions }}</dd></div>
                            @endif
                            @if($shipping->shipped_at)
                                <div><dt>Expédiée le</dt><dd>{{ $shipping->shipped_at->format('d/m/Y à H:i') }}</dd></div>
                            @endif
                            @if($shipping->delivered_at)
                                <div><dt>Livrée le</dt><dd>{{ $shipping->delivered_at->format('d/m/Y à H:i') }}</dd></div>
                            @endif
                        </dl>
                    </div>
                    <form action="{{ route('admin.orders.update-shipping', $order) }}" method="POST">
                        @csrf
                        <div class="card-body stack" style="gap: .85rem; border-top: 1px solid var(--border);">
                            <div class="field">
                                <label class="label" for="status">Statut</label>
                                <select id="status" name="status" class="select @error('status') is-invalid @enderror">
                                    @foreach(['pending' => 'À préparer', 'processing' => 'En préparation', 'shipped' => 'Expédiée', 'delivered' => 'Livrée', 'cancelled' => 'Annulée'] as $value => $label)
                                        <option value="{{ $value }}" @selected(old('status', $shipping->status) === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')<p class="error">{{ $message }}</p>@enderror
                            </div>
                            <div class="field">
                                <label class="label" for="carrier_name">Transporteur</label>
                                <input type="text" id="carrier_name" name="carrier_name" value="{{ old('carrier_name', $shipping->carrier_name) }}" maxlength="100" class="input" placeholder="Ex : coursier interne, DHL…">
                            </div>
                            <div class="field">
                                <label class="label" for="tracking_number">N° de suivi</label>
                                <input type="text" id="tracking_number" name="tracking_number" value="{{ old('tracking_number', $shipping->tracking_number) }}" maxlength="100" class="input">
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary btn-block">Mettre à jour l'expédition</button>
                        </div>
                    </form>
                @else
                    <div class="card-body"><p class="text-muted">Commande 100 % numérique : aucun envoi.</p></div>
                @endif
            </section>
        </aside>
    </div>
@endsection
