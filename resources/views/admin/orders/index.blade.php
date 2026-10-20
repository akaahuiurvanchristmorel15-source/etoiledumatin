@extends('layouts.admin')
@section('title', 'Commandes')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Commandes</h1>
            <p class="page-subtitle">Paiements Paystack et suivi des expéditions.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.orders.export-csv') }}" class="btn btn-secondary">
                <x-admin.icon name="download" />
                <span>Exporter (CSV)</span>
            </a>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="toolbar" role="search">
            <div class="input-group">
                <x-admin.icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="N° de commande, nom, e-mail, téléphone…" aria-label="Rechercher une commande">
            </div>
            <select name="status" class="select" aria-label="Paiement">
                <option value="">Tous les paiements</option>
                <option value="paid" @selected(request('status') === 'paid')>Payées</option>
                <option value="pending" @selected(request('status') === 'pending')>En attente</option>
                <option value="failed" @selected(request('status') === 'failed')>Échouées</option>
            </select>
            <select name="shipping_status" class="select" aria-label="Expédition">
                <option value="">Toutes les expéditions</option>
                <option value="pending" @selected(request('shipping_status') === 'pending')>À préparer</option>
                <option value="processing" @selected(request('shipping_status') === 'processing')>En préparation</option>
                <option value="shipped" @selected(request('shipping_status') === 'shipped')>Expédiées</option>
                <option value="delivered" @selected(request('shipping_status') === 'delivered')>Livrées</option>
                <option value="cancelled" @selected(request('shipping_status') === 'cancelled')>Annulées</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'shipping_status']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Réinitialiser</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Commande</th>
                        <th>Client</th>
                        <th class="text-right">Total</th>
                        <th class="text-center">Paiement</th>
                        <th class="text-center">Expédition</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="cell-primary">
                                <a href="{{ route('admin.orders.show', $order) }}" class="cell-title mono">{{ $order->order_number }}</a>
                                <div class="cell-sub">{{ $order->created_at->format('d/m/Y à H:i') }} · {{ $order->items->count() }} article(s)</div>
                            </td>
                            <td data-label="Client">
                                <div>{{ $order->customer_name }}</div>
                                <div class="cell-sub">{{ $order->customer_email }}</div>
                            </td>
                            <td data-label="Total" class="text-right text-strong">{{ number_format($order->total_amount, 0, ',', ' ') }} F</td>
                            <td data-label="Paiement" class="text-center">@include('admin.partials.payment-badge', ['status' => $order->payment_status])</td>
                            <td data-label="Expédition" class="text-center">@include('admin.partials.shipping-badge', ['shipping' => $order->shippingAddress])</td>
                            <td class="td-actions">
                                <div class="cell-actions">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-secondary btn-sm">Détails</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty">
                                    <p class="empty-title">Aucune commande</p>
                                    <p>Aucune commande ne correspond à ces critères.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $orders->links('admin.partials.pagination') }}
    </div>
@endsection
