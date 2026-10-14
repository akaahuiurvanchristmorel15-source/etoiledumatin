@extends('layouts.admin')
@section('title', 'Gestion des Commandes — Administration')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">

    <!-- En-tête -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
        <div>
            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #D9AE5C;">
                ★ Ventes &amp; Expéditions
            </span>
            <h1 class="hero-title font-editorial" style="font-size: 2.4rem; line-height: 1.15; margin: 0.25rem 0 0.5rem 0;">
                Commandes &amp; Expéditions
            </h1>
            <p style="font-size: 0.9rem; color: #786B5A;">
                Suivi des règlements Paystack, expédition des commandes physiques et traçabilité des colis.
            </p>
        </div>
        <a href="{{ route('admin.orders.export-csv') }}" class="admin-btn-secondary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <span>Exporter en CSV</span>
        </a>
    </div>

    <!-- Filtres -->
    <div style="background-color: #FFFDF9; border: 1px solid rgba(217, 174, 92, 0.25); border-radius: 20px; padding: 1.25rem 1.5rem; box-shadow: 0 4px 14px rgba(60, 45, 25, 0.04);">
        <form action="{{ route('admin.orders.index') }}" method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
            <div style="flex: 1; min-width: 260px; position: relative;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par N° commande, client, email, tél..." style="width: 100%; background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem 0.65rem 2.5rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: #8B857C;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            </div>

            <select name="status" style="background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                <option value="">Tous les règlements</option>
                <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Payé</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Échoué</option>
            </select>

            <select name="shipping_status" style="background: #F8F1E5; border: 1px solid rgba(139, 133, 124, 0.2); border-radius: 12px; padding: 0.65rem 1rem; font-size: 0.85rem; color: #1E1D1B; outline: none;">
                <option value="">Tous les statuts de livraison</option>
                <option value="pending" {{ request('shipping_status') === 'pending' ? 'selected' : '' }}>À préparer</option>
                <option value="processing" {{ request('shipping_status') === 'processing' ? 'selected' : '' }}>En cours</option>
                <option value="shipped" {{ request('shipping_status') === 'shipped' ? 'selected' : '' }}>Expédié</option>
                <option value="delivered" {{ request('shipping_status') === 'delivered' ? 'selected' : '' }}>Livré</option>
                <option value="cancelled" {{ request('shipping_status') === 'cancelled' ? 'selected' : '' }}>Annulé</option>
            </select>

            <button type="submit" class="admin-btn-primary" style="padding: 0.65rem 1.25rem;">
                Filtrer
            </button>

            @if(request()->hasAny(['search', 'status', 'shipping_status']))
                <a href="{{ route('admin.orders.index') }}" style="font-size: 0.78rem; font-weight: 700; color: #D9AE5C; text-decoration: underline;">
                    Effacer
                </a>
            @endif
        </form>
    </div>

    <!-- Table des Commandes -->
    <div class="admin-table-container">
        <div>
            <table class="admin-table stack-table">
                <thead>
                    <tr>
                        <th>N° Commande</th>
                        <th>Client &amp; Contact</th>
                        <th>Date</th>
                        <th>Montant &amp; Canal</th>
                        <th style="text-align: center;">Paiement</th>
                        <th style="text-align: center;">Statut Expédition</th>
                        <th style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td class="cell-main" data-label="N° Commande">
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    <span style="font-family: monospace; font-weight: 800; color: #1E1D1B; font-size: 0.95rem;">
                                        <a href="{{ route('admin.orders.show', $order) }}" style="color: #1E1D1B; text-decoration: underline;">
                                            {{ $order->order_number }}
                                        </a>
                                    </span>
                                    <span style="font-size: 0.75rem; color: #8B857C; font-weight: 500;">
                                        {{ $order->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                            </td>
                            <td data-label="Client &amp; Contact">
                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: #1E1D1B;">{{ $order->customer_name }}</div>
                                    <div style="font-size: 0.72rem; color: #8B857C;">{{ $order->customer_email }}</div>
                                    <div style="font-size: 0.72rem; color: #8B857C; font-family: monospace;">{{ $order->customer_phone }}</div>
                                </div>
                            </td>
                            <td data-label="Date" style="color: #8B857C;">
                                {{ $order->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td data-label="Montant &amp; Canal">
                                <div style="text-align: right;">
                                    <div style="font-weight: 800; color: #1E1D1B;">{{ number_format($order->total_amount, 0, ',', ' ') }} FCFA</div>
                                    <div style="font-size: 0.72rem; color: #8B857C; text-transform: uppercase;">{{ $order->payment_method ?? 'Paystack' }}</div>
                                </div>
                            </td>
                            <td data-label="Paiement" style="text-align: center;">
                                @if($order->payment_status === 'paid')
                                    <span class="admin-badge admin-badge-green">Payé</span>
                                @elseif($order->payment_status === 'pending')
                                    <span class="admin-badge admin-badge-gold">En attente</span>
                                @else
                                    <span class="admin-badge admin-badge-red">Échoué</span>
                                @endif
                            </td>
                            <td data-label="Statut Expédition" style="text-align: center;">
                                @if($order->shippingAddress)
                                    <span class="admin-badge {{ in_array($order->shippingAddress->status, ['shipped', 'delivered']) ? 'admin-badge-green' : 'admin-badge-gold' }}">
                                        {{ $order->shippingAddress->status_label ?? $order->shippingAddress->status }}
                                    </span>
                                @else
                                    <span style="font-size: 0.72rem; color: #8B857C;">100% Numérique</span>
                                @endif
                            </td>
                            <td class="cell-actions" style="text-align: right;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="admin-btn-secondary" style="padding: 0.45rem 0.95rem; font-size: 0.78rem;">
                                    Gérer la commande ›
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: #8B857C;">
                                Aucune commande ne correspond aux critères sélectionnés.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($orders->hasPages())
            <div class="admin-pagination-box" style="padding: 1.25rem 1.5rem; border-top: 1px solid rgba(139, 133, 124, 0.1); margin-top: 1rem;">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
