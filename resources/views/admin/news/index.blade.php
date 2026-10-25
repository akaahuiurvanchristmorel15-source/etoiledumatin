@extends('layouts.admin')
@section('title', 'Actualités')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Actualités</h1>
            <p class="page-subtitle">Annoncez les événements, sorties de livres et conférences aux lecteurs.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                <x-admin.icon name="plus" />
                <span>Nouvelle actualité</span>
            </a>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.news.index') }}" method="GET" class="toolbar" role="search">
            <div class="input-group">
                <x-admin.icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Titre…" aria-label="Rechercher une actualité">
            </div>
            <select name="type" class="select" aria-label="Type">
                <option value="">Tous les types</option>
                @foreach(\App\Enums\NewsType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(request('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <select name="status" class="select" aria-label="Statut">
                <option value="">Tous statuts</option>
                <option value="published" @selected(request('status') === 'published')>Publiées</option>
                <option value="draft" @selected(request('status') === 'draft')>Brouillons</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['search', 'type', 'status']))
                <a href="{{ route('admin.news.index') }}" class="btn btn-ghost">Réinitialiser</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Actualité</th>
                        <th>Type</th>
                        <th>Événement</th>
                        <th class="text-center">Statut</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($news as $article)
                        @php
                            [$statusLabel, $statusClass] = match (true) {
                                ! $article->is_published => ['Brouillon', ''],
                                $article->published_at?->isFuture() => ['Programmée', 'badge-warning'],
                                default => ['Publiée', 'badge-success'],
                            };
                        @endphp
                        <tr>
                            <td class="cell-primary">
                                <div class="cell-title">{{ $article->title }}</div>
                                <div class="cell-sub">
                                    @if($article->published_at)
                                        {{ $article->published_at->isFuture() ? 'Prévue le' : 'Publiée le' }} {{ $article->published_at->format('d/m/Y à H:i') }}
                                    @else
                                        Créée le {{ $article->created_at->format('d/m/Y') }}
                                    @endif
                                </div>
                            </td>
                            <td data-label="Type" class="text-muted">{{ $article->type->label() }}</td>
                            <td data-label="Événement" class="text-muted">
                                @if($article->event_date || $article->location)
                                    <div>{{ $article->event_date?->format('d/m/Y à H:i') }}</div>
                                    <div class="cell-sub">{{ $article->location }}</div>
                                @else
                                    —
                                @endif
                            </td>
                            <td data-label="Statut" class="text-center">
                                <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="td-actions">
                                <div class="cell-actions">
                                    <a href="{{ route('admin.news.edit', $article) }}" class="btn btn-secondary btn-sm">
                                        <x-admin.icon name="edit" />
                                        <span>Modifier</span>
                                    </a>
                                    <form action="{{ route('admin.news.destroy', $article) }}" method="POST" class="inline-form" data-confirm="Supprimer l'actualité « {{ $article->title }} » ?">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer" aria-label="Supprimer {{ $article->title }}">
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
                                    <p class="empty-title">Aucune actualité</p>
                                    <p>Annoncez un premier événement ou une sortie de livre.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $news->links('admin.partials.pagination') }}
    </div>
@endsection
