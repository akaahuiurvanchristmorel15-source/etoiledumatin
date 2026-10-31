@extends('layouts.admin')
@section('title', 'Tableau de bord')

@section('content')
    @php
        $salesByFormat = [
            'e-Books PDF' => $pdfSalesCount,
            'Livres papier' => $physicalSalesCount,
            'Livres audio' => $audioSalesCount,
        ];
        $totalSales = max(array_sum($salesByFormat), 1);
    @endphp

    <div class="page-header">
        <div>
            <p class="page-eyebrow">{{ ucfirst(now()->locale('fr')->isoFormat('dddd D MMMM YYYY')) }}</p>
            <h1 class="page-title">Bonjour, {{ strtok(auth()->user()->name, ' ') }}</h1>
            <p class="page-subtitle">Vue d'ensemble des ventes, des expéditions et du catalogue · <span class="live-dot" aria-hidden="true"></span>mis à jour automatiquement</p>
        </div>
        <div class="page-actions">
            @can('manage-orders')
                <a href="{{ route('admin.orders.export-csv') }}" class="btn btn-secondary">
                    <x-admin.icon name="download" />
                    <span>Exporter les ventes</span>
                </a>
            @endcan
            @can('manage-catalog')
                <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
                    <x-admin.icon name="plus" />
                    <span>Ajouter un livre</span>
                </a>
            @endcan
        </div>
    </div>

    {{-- Rafraîchi toutes les 30 secondes sans rechargement (js/live.js) --}}
    <div data-live="dashboard" data-live-poll="30">
    <p class="help" style="margin: -.5rem 0 1rem;">Dernière mise à jour : {{ now()->format('H:i:s') }}</p>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Chiffre d'affaires</div>
            <div class="stat-value">{{ number_format($totalRevenue, 0, ',', ' ') }} <small>FCFA</small></div>
            <div class="stat-meta">{{ number_format($monthRevenue, 0, ',', ' ') }} FCFA ce mois-ci</div>
        </div>
        <div class="stat">
            <div class="stat-label">Commandes payées</div>
            <div class="stat-value">{{ $paidOrdersCount }}</div>
            <div class="stat-meta">Depuis l'ouverture</div>
        </div>
        <div class="stat">
            <div class="stat-label">Colis à préparer</div>
            <div class="stat-value">{{ $pendingLogisticsCount }}</div>
            <div class="stat-meta">
                @can('manage-orders')
                    <a class="link" href="{{ route('admin.orders.index', ['status' => 'paid', 'shipping_status' => 'pending']) }}">Voir les expéditions</a>
                @else
                    En attente d'envoi
                @endcan
            </div>
        </div>
        <div class="stat">
            <div class="stat-label">Avis à modérer</div>
            <div class="stat-value">{{ $pendingReviewsCount }}</div>
            <div class="stat-meta">
                @can('manage-reviews')
                    <a class="link" href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}">Modérer les avis</a>
                @else
                    En attente de validation
                @endcan
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-bottom: 1.25rem;">
        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Ventes par format</h2>
                    <p class="card-subtitle">Exemplaires vendus (commandes payées)</p>
                </div>
            </div>
            <div class="card-body">
                @foreach($salesByFormat as $label => $count)
                    <div class="progress-row">
                        <div class="progress-meta"><span>{{ $label }}</span><span class="text-strong">{{ $count }}</span></div>
                        <div class="progress" role="presentation"><span style="width: {{ round($count / $totalSales * 100) }}%;"></span></div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Stock faible</h2>
                    <p class="card-subtitle">Livres papier à 10 exemplaires ou moins</p>
                </div>
                @can('manage-catalog')
                    <a href="{{ route('admin.books.index') }}" class="link">Catalogue</a>
                @endcan
            </div>
            <div class="card-body">
                @forelse($lowStockBooks as $book)
                    <div class="list-item">
                        <div style="min-width: 0;">
                            <div class="cell-title">{{ $book->title }}</div>
                            <div class="cell-sub">{{ $book->author }}</div>
                        </div>
                        <div style="display: flex; align-items: center; gap: .5rem;">
                            <span class="badge {{ $book->stock_physical <= 3 ? 'badge-danger' : 'badge-warning' }}">{{ $book->stock_physical }} ex.</span>
                            @can('manage-catalog')
                                <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-ghost btn-sm btn-icon" aria-label="Modifier {{ $book->title }}"><x-admin.icon name="edit" /></a>
                            @endcan
                        </div>
                    </div>
                @empty
                    <p class="text-muted">Aucun livre en stock faible.</p>
                @endforelse
            </div>
        </section>
    </div>

    <section class="card">
        <div class="card-header">
            <div>
                <h2 class="card-title">Dernières commandes</h2>
                <p class="card-subtitle">Les 8 plus récentes</p>
            </div>
            @can('manage-orders')
                <a href="{{ route('admin.orders.index') }}" class="link">Toutes les commandes</a>
            @endcan
        </div>
        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Paiement</th>
                        <th class="text-center">Expédition</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentOrders as $order)
                        <tr>
                            <td class="cell-primary">
                                @can('manage-orders')
                                    <a href="{{ route('admin.orders.show', $order) }}" class="cell-title mono">{{ $order->order_number }}</a>
                                @else
                                    <span class="cell-title mono">{{ $order->order_number }}</span>
                                @endcan
                                <div class="cell-sub">{{ $order->created_at->format('d/m/Y à H:i') }}</div>
                            </td>
                            <td data-label="Client">{{ $order->customer_name }}</td>
                            <td data-label="Total" class="text-right text-strong">{{ number_format($order->total_amount, 0, ',', ' ') }} F</td>
                            <td data-label="Paiement" class="text-center">@include('admin.partials.payment-badge', ['status' => $order->payment_status])</td>
                            <td data-label="Expédition" class="text-center">@include('admin.partials.shipping-badge', ['shipping' => $order->shippingAddress])</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty">Aucune commande pour le moment.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @can('manage-team')
        <section class="card">
            <div class="card-header"><h2 class="card-title">Activité récente</h2></div>
            <div class="card-body">
                @forelse($recentLogs as $log)
                    <div class="list-item">
                        <div>
                            <span class="text-strong">{{ $log->user?->name ?? 'Système' }}</span>
                            <span class="cell-sub mono" style="margin-left: .35rem;">{{ $log->action }}</span>
                        </div>
                        <span class="cell-sub" style="white-space: nowrap;">{{ $log->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="text-muted">Aucune activité enregistrée.</p>
                @endforelse
            </div>
        </section>
    @endcan
    </div>
@endsection
