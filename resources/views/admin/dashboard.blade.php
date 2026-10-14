@extends('layouts.admin')
@section('title', 'Tableau de bord')

@push('styles')
<style>
    .dash { display: flex; flex-direction: column; gap: 1.5rem; }

    /* En-tête de page */
    .dash-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem; }
    .dash-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--accent-ink); }
    .dash-lead { margin: .25rem 0 0; color: var(--muted); font-size: .9rem; max-width: 52ch; }
    .dash-actions { display: flex; flex-wrap: wrap; gap: .6rem; }

    /* KPI */
    .kpi-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 1rem; }
    .kpi-top { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-bottom: 1rem; }
    .kpi-label { font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--muted); }
    .kpi-icon { width: 36px; height: 36px; border-radius: 10px; display: grid; place-items: center; flex-shrink: 0; background: rgba(119, 115, 107, .1); color: var(--muted); font-size: .68rem; font-weight: 700; }
    .kpi-icon.gold { background: var(--accent-soft); color: var(--accent-ink); }
    .kpi-icon.green { background: rgba(47, 111, 78, .12); color: #2F6F4E; }
    .kpi-value { font-size: 1.75rem; font-weight: 700; letter-spacing: -.02em; line-height: 1.1; color: var(--text); word-break: break-word; }
    .kpi-value small { font-size: .78rem; font-weight: 600; color: var(--muted); letter-spacing: 0; }
    .kpi-value.warn { color: var(--accent-ink); }
    .kpi-note { margin: .4rem 0 0; font-size: .78rem; color: var(--muted); }
    .kpi-note.positive { color: #2F6F4E; font-weight: 600; }
    .dash-link { color: var(--accent-ink); font-weight: 600; text-decoration: none; font-size: .8rem; white-space: nowrap; }
    .dash-link:hover { text-decoration: underline; }

    /* Panneaux */
    .dash-split { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.6fr); gap: 1rem; align-items: start; }
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); }
    .panel-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: .5rem 1rem; padding: 1.15rem 1.5rem; border-bottom: 1px solid var(--border); }
    .panel-title { margin: 0; font-size: 1rem; font-weight: 700; letter-spacing: -.01em; }
    .panel-sub { margin: .15rem 0 0; font-size: .78rem; color: var(--muted); }
    .panel-body { padding: 1.25rem 1.5rem; }
    .panel .admin-table-container { border: 0; box-shadow: none; border-radius: 0 0 var(--radius) var(--radius); }

    /* Barres de répartition */
    .bar-row + .bar-row { margin-top: 1.25rem; }
    .bar-meta { display: flex; justify-content: space-between; gap: .75rem; font-size: .82rem; font-weight: 600; margin-bottom: .45rem; }
    .bar-meta span:last-child { color: var(--muted); font-weight: 500; }
    .bar-track { height: 8px; background: #EFECE5; border-radius: 999px; overflow: hidden; }
    .bar-fill { height: 100%; border-radius: 999px; }
    .bar-fill.gold { background: var(--accent); }
    .bar-fill.dark { background: #2B2A27; }
    .bar-hint { display: block; margin-top: .3rem; font-size: .72rem; color: var(--muted); }

    .empty { padding: 2rem 1rem; text-align: center; color: var(--muted); font-size: .85rem; }
    .cell-strong { font-weight: 600; color: var(--text); }
    .cell-muted { color: var(--muted); }
    .cell-sub { font-size: .72rem; color: var(--muted); }
    .mono { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
    .ta-c { text-align: center; } .ta-r { text-align: right; }
    .order-link { color: var(--text); font-weight: 700; text-decoration: none; border-bottom: 1px solid var(--accent); }
    .btn-sm { padding: .35rem .8rem; font-size: .76rem; min-height: 34px; }

    /* Journal */
    .log-list { display: flex; flex-direction: column; }
    .log-item { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: .75rem 0; border-bottom: 1px solid var(--border); font-size: .82rem; }
    .log-item:last-child { border-bottom: 0; padding-bottom: 0; }
    .log-item:first-child { padding-top: 0; }
    .log-main { display: flex; align-items: center; gap: .65rem; min-width: 0; flex-wrap: wrap; }
    .log-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }
    .log-time { font-size: .74rem; color: var(--muted); white-space: nowrap; }

    /* Responsive */
    @media (max-width: 1200px) {
        .kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 1024px) {
        .dash-split { grid-template-columns: 1fr; }
    }
    @media (max-width: 768px) {
        .dash { gap: 1.15rem; }
        .dash-head { align-items: stretch; }
        .dash-actions { width: 100%; }
        .dash-actions .admin-btn-primary, .dash-actions .admin-btn-secondary { flex: 1 1 100%; }
        .kpi-grid { grid-template-columns: 1fr; gap: .75rem; }
        .kpi-value { font-size: 1.5rem; }
        .panel-head, .panel-body { padding-left: 1.1rem; padding-right: 1.1rem; }
        .log-item { flex-direction: column; align-items: flex-start; gap: .25rem; }
    }
</style>
@endpush

@section('content')
<div class="dash">

    <!-- En-tête -->
    <div class="dash-head">
        <div>
            <span class="dash-eyebrow">Supervision &amp; gestion</span>
            <h1 class="hero-title font-editorial" style="font-size: 2.2rem; margin: 0.2rem 0 0.4rem 0;">Tableau de Bord &amp; Indicateurs</h1>
            <p class="dash-lead">Aperçu des ventes, de la logistique d'expédition et de l'inventaire des œuvres.</p>
        </div>
        <div class="dash-actions">
            <a href="{{ route('admin.orders.export-csv') }}" class="admin-btn-secondary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                <span>Exporter en CSV</span>
            </a>
            <a href="{{ route('admin.books.create') }}" class="admin-btn-primary">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span>Ajouter un livre</span>
            </a>
        </div>
    </div>

    <!-- KPI -->
    <div class="kpi-grid">
        <div class="admin-stat-card">
            <div class="kpi-top">
                <span class="kpi-label">Revenus encaissés</span>
                <span class="kpi-icon gold">FCFA</span>
            </div>
            <div>
                <div class="kpi-value">{{ number_format($totalRevenue, 0, ',', ' ') }} <small>FCFA</small></div>
                <p class="kpi-note positive">{{ number_format($monthRevenue, 0, ',', ' ') }} FCFA ce mois-ci</p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="kpi-top">
                <span class="kpi-label">Commandes confirmées</span>
                <span class="kpi-icon green">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
            </div>
            <div>
                <div class="kpi-value">{{ $paidOrdersCount }}</div>
                <p class="kpi-note">Transactions validées</p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="kpi-top">
                <span class="kpi-label">Colis à préparer</span>
                <span class="kpi-icon {{ $pendingLogisticsCount > 0 ? 'gold' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                </span>
            </div>
            <div>
                <div class="kpi-value {{ $pendingLogisticsCount > 0 ? 'warn' : '' }}">{{ $pendingLogisticsCount }}</div>
                <p class="kpi-note"><a class="dash-link" href="{{ route('admin.orders.index', ['shipping_status' => 'pending']) }}">Traiter les expéditions ›</a></p>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="kpi-top">
                <span class="kpi-label">Avis à modérer</span>
                <span class="kpi-icon {{ $pendingReviewsCount > 0 ? 'gold' : '' }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </span>
            </div>
            <div>
                <div class="kpi-value {{ $pendingReviewsCount > 0 ? 'warn' : '' }}">{{ $pendingReviewsCount }}</div>
                <p class="kpi-note"><a class="dash-link" href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}">Gérer les avis clients ›</a></p>
            </div>
        </div>
    </div>

    <!-- Ventes & stocks -->
    <div class="dash-split">
        <section class="panel">
            <div class="panel-head">
                <h2 class="panel-title">Répartition des ventes</h2>
            </div>
            <div class="panel-body">
                @php
                    $totalSales = $pdfSalesCount + $physicalSalesCount;
                    $pdfPercent = $totalSales > 0 ? round(($pdfSalesCount / $totalSales) * 100) : 0;
                    $physPercent = $totalSales > 0 ? round(($physicalSalesCount / $totalSales) * 100) : 0;
                @endphp

                <div class="bar-row">
                    <div class="bar-meta"><span>e-Books numériques (PDF)</span><span>{{ $pdfSalesCount }} téléchargement(s)</span></div>
                    <div class="bar-track"><div class="bar-fill gold" style="width: {{ $pdfPercent }}%;"></div></div>
                    <span class="bar-hint">{{ $pdfPercent }}% des volumes vendus</span>
                </div>

                <div class="bar-row">
                    <div class="bar-meta"><span>Livres reliés physiques</span><span>{{ $physicalSalesCount }} exemplaire(s)</span></div>
                    <div class="bar-track"><div class="bar-fill dark" style="width: {{ $physPercent }}%;"></div></div>
                    <span class="bar-hint">{{ $physPercent }}% des volumes vendus</span>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-head">
                <div>
                    <h2 class="panel-title">Alertes de stock faible</h2>
                    <p class="panel-sub">Livres physiques à 10 exemplaires ou moins</p>
                </div>
                <a href="{{ route('admin.books.index') }}" class="dash-link">Gérer l'inventaire ›</a>
            </div>

            @if($lowStockBooks->isEmpty())
                <p class="empty">Tous les stocks de livres physiques dépassent 10 exemplaires.</p>
            @else
                <div class="admin-table-container">
                    <table class="admin-table stack-table">
                        <thead>
                            <tr>
                                <th>Titre</th>
                                <th>Auteur</th>
                                <th class="ta-c">Stock</th>
                                <th class="ta-r">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($lowStockBooks as $book)
                                <tr>
                                    <td class="cell-main" data-label="Titre">
                                        <div style="font-weight: 700; color: #1E1D1B; font-size: 0.95rem;">{{ $book->title }}</div>
                                        <div class="cell-muted" style="font-size: 0.75rem;">{{ $book->author }}</div>
                                    </td>
                                    <td data-label="Auteur" class="cell-muted">{{ $book->author }}</td>
                                    <td data-label="Stock" class="ta-c">
                                        <span class="admin-badge {{ $book->stock_physical <= 3 ? 'admin-badge-red' : 'admin-badge-gold' }}">{{ $book->stock_physical }} ex.</span>
                                    </td>
                                    <td class="cell-actions" style="text-align: right;">
                                        <a href="{{ route('admin.books.edit', $book) }}" class="admin-btn-secondary" style="padding: 0.4rem 0.85rem; font-size: 0.78rem;">Modifier l'ouvrage ›</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>

    <!-- Dernières commandes -->
    <section class="panel">
        <div class="panel-head">
            <div>
                <h2 class="panel-title">Dernières commandes</h2>
                <p class="panel-sub">Les 8 transactions les plus récentes</p>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="dash-link">Voir toutes les commandes ›</a>
        </div>

        <div class="admin-table-container">
            <table class="admin-table stack-table">
                <thead>
                    <tr>
                        <th>N° commande</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th class="ta-c">Paiement</th>
                        <th class="ta-c">Expédition</th>
                        <th class="ta-r">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="cell-main" data-label="N° commande">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="order-link mono" style="font-weight: 800; font-size: 0.95rem;">
                                        {{ $order->order_number }}
                                    </a>
                                    <span style="font-size: 0.75rem; color: var(--muted); font-weight: 500;">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </td>
                            <td data-label="Client">
                                <div class="cell-strong">{{ $order->customer_name }}</div>
                                <div class="cell-sub">{{ $order->customer_email }}</div>
                            </td>
                            <td data-label="Date" class="cell-muted">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td data-label="Articles">{{ $order->items->count() }} article(s)</td>
                            <td data-label="Total" class="cell-strong">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</td>
                            <td data-label="Paiement" class="ta-c">
                                @if($order->payment_status === 'paid')
                                    <span class="admin-badge admin-badge-green">Payé</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="admin-badge admin-badge-gold">En attente</span>
                                @else
                                    <span class="admin-badge admin-badge-red">Échoué</span>
                                @endif
                            </td>
                            <td data-label="Expédition" class="ta-c">
                                @if($order->shippingAddress)
                                    <span class="admin-badge {{ $order->shippingAddress->status === 'delivered' ? 'admin-badge-green' : 'admin-badge-gold' }}">
                                        {{ $order->shippingAddress->status_label ?? $order->shippingAddress->status }}
                                    </span>
                                @else
                                    <span class="cell-sub">100 % numérique</span>
                                @endif
                            </td>
                            <td class="cell-actions" style="text-align: right;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn-secondary" style="padding: 0.45rem 0.95rem; font-size: 0.78rem;">Détails ›</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty">Aucune commande enregistrée pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Journal d'audit -->
    <section class="panel">
        <div class="panel-head">
            <h2 class="panel-title">Activité &amp; sécurité</h2>
        </div>
        <div class="panel-body">
            <div class="log-list">
                @forelse($recentLogs as $log)
                    <div class="log-item">
                        <div class="log-main">
                            <span class="log-dot"></span>
                            <span class="cell-strong">{{ $log->user?->name ?? 'Système' }}</span>
                            <span class="mono cell-sub">{{ $log->action }}</span>
                        </div>
                        <span class="log-time">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="empty">Aucun journal d'activité enregistré.</p>
                @endforelse
            </div>
        </div>
    </section>

</div>
@endsection