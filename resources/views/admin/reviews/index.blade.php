@extends('layouts.admin')
@section('title', 'Modération des Avis Clients — Administration')

@section('content')
<div style="display: flex; flex-direction: column; gap: 2rem;">

    <!-- En-tête -->
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding-bottom: 1.5rem; border-bottom: 1px solid rgba(139, 133, 124, 0.1);">
        <div>
            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; color: #D9AE5C;">
                ★ Retours Lecteurs &amp; Notations
            </span>
            <h1 class="hero-title font-editorial" style="font-size: 2.4rem; line-height: 1.15; margin: 0.25rem 0 0.5rem 0;">
                Modération des Avis
            </h1>
            <p style="font-size: 0.9rem; color: #786B5A;">
                Validez ou modérez les commentaires des lecteurs avant leur parution publique sur les fiches de livres.
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('admin.reviews.index') }}" class="{{ !request('filter') ? 'admin-btn-primary' : 'admin-btn-secondary' }}" style="padding: 0.45rem 1rem; font-size: 0.78rem;">
                Tous
            </a>
            <a href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}" class="{{ request('filter') === 'pending' ? 'admin-btn-primary' : 'admin-btn-secondary' }}" style="padding: 0.45rem 1rem; font-size: 0.78rem;">
                En Attente
            </a>
            <a href="{{ route('admin.reviews.index', ['filter' => 'approved']) }}" class="{{ request('filter') === 'approved' ? 'admin-btn-primary' : 'admin-btn-secondary' }}" style="padding: 0.45rem 1rem; font-size: 0.78rem;">
                Approuvés
            </a>
        </div>
    </div>

    <!-- Table des Avis -->
    <div class="admin-table-container">
        <div>
            <table class="admin-table stack-table">
                <thead>
                    <tr>
                        <th>Ouvrage Concerné</th>
                        <th>Lecteur</th>
                        <th>Note</th>
                        <th>Commentaire</th>
                        <th>Date</th>
                        <th style="text-align: center;">Statut</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td class="cell-main" data-label="Ouvrage Concerné">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem;">
                                    <div>
                                        <div style="font-weight: 700; color: #1E1D1B; font-size: 0.95rem;">{{ $review->book?->title ?? 'Ouvrage supprimé' }}</div>
                                        <div style="font-size: 0.75rem; color: #8B857C;">{{ $review->book?->author }}</div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.25rem; color: #D9AE5C; font-weight: 800; font-size: 0.85rem; flex-shrink: 0;">
                                        <span>{{ $review->rating }}</span>
                                        <span>★</span>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Lecteur">
                                <div style="text-align: right;">
                                    <div style="font-weight: 700; color: #1E1D1B;">{{ $review->customer_name ?? $review->user?->name ?? 'Anonyme' }}</div>
                                    <div style="font-size: 0.72rem; color: #8B857C;">{{ $review->customer_email }}</div>
                                </div>
                            </td>
                            <td data-label="Note">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.25rem; color: #D9AE5C; font-weight: 800;">
                                    <span>{{ $review->rating }} / 5</span>
                                    <span>★</span>
                                </div>
                            </td>
                            <td data-label="Commentaire" style="text-align: right;">
                                <p style="font-size: 0.82rem; color: #1E1D1B; line-height: 1.45; margin: 0; font-style: italic;">« {{ $review->comment }} »</p>
                            </td>
                            <td data-label="Date" style="color: #8B857C; font-size: 0.75rem;">
                                {{ $review->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td data-label="Statut" style="text-align: center;">
                                @if($review->is_approved)
                                    <span class="admin-badge admin-badge-green">Approuvé</span>
                                @else
                                    <span class="admin-badge admin-badge-gold">En attente</span>
                                @endif
                            </td>
                            <td class="cell-actions" style="text-align: right;">
                                <div style="display: flex; align-items: center; justify-content: flex-end; gap: 0.5rem;">
                                    @if(! $review->is_approved)
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="admin-btn-primary" style="padding: 0.45rem 0.95rem; font-size: 0.78rem;">
                                                Approuver
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Confirmez-vous la suppression de cet avis ?');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: 1px solid rgba(225, 29, 72, 0.25); color: #BE123C; border-radius: 10px; padding: 0.45rem 0.75rem; cursor: pointer; display: flex; align-items: center;" title="Supprimer">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2.5rem; color: #8B857C;">
                                Aucun avis ne correspond aux critères sélectionnés.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="admin-pagination-box" style="padding: 1.25rem 1.5rem; border-top: 1px solid rgba(139, 133, 124, 0.1); margin-top: 1rem;">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
