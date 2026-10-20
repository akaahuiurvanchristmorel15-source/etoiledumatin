@php
    $formatLabels = ['pdf' => 'e-Book PDF', 'physical' => 'Livre papier', 'audio' => 'Livre audio'];
    $shipping = $order->shippingAddress;
    $steps = ['pending' => 'Commande reçue', 'processing' => 'En préparation', 'shipped' => 'Expédiée', 'delivered' => 'Livrée'];
    $currentStep = $shipping ? array_search($shipping->status, array_keys($steps), true) : false;
@endphp

@if($order->isPaid() && $order->downloads->isNotEmpty())
    <section class="pg-section">
        <h2 class="pg-section-title">Vos livres numériques</h2>
        <ul class="pg-shelf">
            @foreach($order->downloads as $index => $download)
                @php $book = $download->book; @endphp
                <li class="pg-owned" style="--i: {{ $index }};">
                    <span class="pg-owned-cover">
                        <img src="{{ $book?->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="" loading="lazy">
                    </span>
                    <span class="pg-owned-meta">{{ $download->isAudio() ? 'Livre audio' : 'e-Book PDF' }}</span>
                    <h3 class="pg-owned-title">{{ $book?->title ?? 'Livre' }}</h3>
                    <p class="pg-owned-author">{{ $book?->author }}</p>
                    @if($download->canDownload())
                        <a href="{{ route('library.download', $download) }}" class="pg-btn">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><polyline points="7 10 12 15 17 10"/><path d="M5 20h14"/></svg>
                            <span>Télécharger</span>
                        </a>
                    @else
                        <span class="pg-btn pg-btn-ghost" aria-disabled="true" style="cursor: default;">Quota atteint</span>
                    @endif
                    <span class="pg-owned-quota">{{ max(0, $download->max_downloads - $download->download_count) }} téléchargement(s) restant(s)</span>
                </li>
            @endforeach
        </ul>
    </section>
@endif

@if($shipping)
    <section class="pg-section">
        <h2 class="pg-section-title">Suivi de livraison</h2>
        @if($shipping->status === 'cancelled')
            <p class="pg-alert" style="margin: 0;">Cette livraison a été annulée. Contactez-nous pour plus d'informations.</p>
        @else
            <ol class="pg-timeline">
                @foreach($steps as $key => $label)
                    @php $position = array_search($key, array_keys($steps), true); @endphp
                    <li class="pg-timeline-step {{ $currentStep !== false && $position <= $currentStep ? 'is-done' : '' }} {{ $position === $currentStep ? 'is-current' : '' }}" style="--i: {{ $position }};">
                        <span class="pg-timeline-dot" aria-hidden="true"></span>
                        <span class="pg-timeline-label">{{ $label }}</span>
                        @if($key === 'shipped' && $shipping->shipped_at)<span class="pg-timeline-date">{{ $shipping->shipped_at->format('d/m/Y') }}</span>@endif
                        @if($key === 'delivered' && $shipping->delivered_at)<span class="pg-timeline-date">{{ $shipping->delivered_at->format('d/m/Y') }}</span>@endif
                    </li>
                @endforeach
            </ol>
            @if($shipping->tracking_number)
                <p class="pg-hint" style="margin-top: 1rem;">Transporteur : {{ $shipping->carrier_name ?: '—' }} · N° de suivi : <strong style="color: var(--ink);">{{ $shipping->tracking_number }}</strong></p>
            @endif
        @endif
    </section>
@endif

<section class="pg-section pg-split" style="align-items: start;">
    <div>
        <h2 class="pg-section-title">Articles</h2>
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
        <div style="margin-top: 1rem; padding-top: .75rem; border-top: 1px solid var(--line);">
            @if($order->shipping_cost > 0)
                <div class="pg-row"><span>Livraison</span><strong>{{ number_format($order->shipping_cost, 0, ',', ' ') }} FCFA</strong></div>
            @endif
            <div class="pg-row pg-row-total"><span>Total</span><strong>{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</strong></div>
        </div>
    </div>

    <dl class="pg-panel pg-order-facts">
        <div>
            <dt>Client</dt>
            <dd>{{ $order->customer_name }}<br>{{ $order->customer_email }}@if($order->customer_phone)<br>{{ $order->customer_phone }}@endif</dd>
        </div>
        <div>
            <dt>Paiement</dt>
            <dd>
                <span class="pg-badge {{ $order->isPaid() ? 'is-success' : 'is-warning' }}">{{ $order->isPaid() ? 'Payée' : 'En attente de paiement' }}</span>
                @if($order->paid_at)<br>le {{ $order->paid_at->format('d/m/Y à H:i') }}@endif
            </dd>
        </div>
        @if($shipping)
            <div>
                <dt>Adresse de livraison</dt>
                <dd>{{ $shipping->recipient_name }}<br>{{ $shipping->address_line }}<br>{{ $shipping->city }}, {{ $shipping->country }}</dd>
            </div>
        @endif
    </dl>
</section>
