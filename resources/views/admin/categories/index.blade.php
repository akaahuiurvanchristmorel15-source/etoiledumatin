@extends('layouts.admin')
@section('title', 'Catégories')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Catégories</h1>
            <p class="page-subtitle">Organisez le catalogue en collections affichées dans la boutique.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <x-admin.icon name="plus" />
                <span>Nouvelle catégorie</span>
            </a>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Catégorie parente</th>
                        <th class="text-center">Livres</th>
                        <th class="text-center">Ordre</th>
                        <th class="text-center">Statut</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td class="cell-primary">
                                <div class="cell-title">{{ $category->name }}</div>
                                <div class="cell-sub mono">{{ $category->slug }}</div>
                            </td>
                            <td data-label="Parente" class="text-muted">{{ $category->parent?->name ?? '—' }}</td>
                            <td data-label="Livres" class="text-center">{{ $category->books_count }}</td>
                            <td data-label="Ordre" class="text-center text-muted">{{ $category->display_order }}</td>
                            <td data-label="Statut" class="text-center">
                                <span class="badge {{ $category->is_active ? 'badge-success' : '' }}">{{ $category->is_active ? 'Active' : 'Masquée' }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="cell-actions">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-secondary btn-sm">
                                        <x-admin.icon name="edit" />
                                        <span>Modifier</span>
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-form" data-confirm="Supprimer la catégorie « {{ $category->name }} » ?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="{{ $category->books_count > 0 ? 'Contient des livres' : 'Supprimer' }}" aria-label="Supprimer {{ $category->name }}" @disabled($category->books_count > 0)>
                                            <x-admin.icon name="trash" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty">
                                    <p class="empty-title">Aucune catégorie</p>
                                    <p>Créez une première catégorie pour classer vos livres.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
