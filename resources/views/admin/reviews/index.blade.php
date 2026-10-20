@extends('layouts.admin')
@section('title', 'Avis clients')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Avis clients</h1>
            <p class="page-subtitle">Les avis ne sont publiés sur les fiches qu'après validation.</p>
        </div>
        <nav class="segmented" aria-label="Filtrer les avis">
            <a href="{{ route('admin.reviews.index') }}" class="{{ ! request('filter') ? 'is-active' : '' }}">Tous</a>
            <a href="{{ route('admin.reviews.index', ['filter' => 'pending']) }}" class="{{ request('filter') === 'pending' ? 'is-active' : '' }}">En attente</a>
            <a href="{{ route('admin.reviews.index', ['filter' => 'approved']) }}" class="{{ request('filter') === 'approved' ? 'is-active' : '' }}">Publiés</a>
        </nav>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Avis</th>
                        <th>Livre</th>
                        <th class="text-center">Note</th>
                        <th class="text-center">Statut</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td class="cell-primary" style="max-width: 420px;">
                                <div class="cell-title">{{ $review->customer_name ?? $review->user?->name ?? 'Anonyme' }}</div>
                                <p style="margin-top: .2rem; color: var(--text-soft);">{{ $review->comment }}</p>
                                <div class="cell-sub" style="margin-top: .25rem;">{{ $review->created_at->format('d/m/Y à H:i') }}</div>
                            </td>
                            <td data-label="Livre">
                                <div>{{ $review->book?->title ?? 'Livre supprimé' }}</div>
                                @if($review->book?->trashed())<div class="cell-sub">Retiré du catalogue</div>@endif
                            </td>
                            <td data-label="Note" class="text-center" style="white-space: nowrap;">
                                <span aria-label="{{ $review->rating }} sur 5" style="color: var(--accent); letter-spacing: 1px;">{{ str_repeat('★', $review->rating) }}</span><span style="color: var(--border-strong); letter-spacing: 1px;" aria-hidden="true">{{ str_repeat('★', 5 - $review->rating) }}</span>
                            </td>
                            <td data-label="Statut" class="text-center">
                                <span class="badge {{ $review->is_approved ? 'badge-success' : 'badge-warning' }}">{{ $review->is_approved ? 'Publié' : 'En attente' }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="cell-actions">
                                    @if($review->is_approved)
                                        <form action="{{ route('admin.reviews.hide', $review) }}" method="POST" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-secondary btn-sm">
                                                <x-admin.icon name="eye-off" />
                                                <span>Masquer</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.reviews.approve', $review) }}" method="POST" class="inline-form">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <x-admin.icon name="check" />
                                                <span>Publier</span>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" class="inline-form" data-confirm="Supprimer définitivement cet avis ?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer" aria-label="Supprimer l'avis">
                                            <x-admin.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="empty">
                                    <p class="empty-title">Aucun avis</p>
                                    <p>Aucun avis ne correspond à ce filtre.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $reviews->links('admin.partials.pagination') }}
    </div>
@endsection
