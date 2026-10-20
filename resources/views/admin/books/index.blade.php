@extends('layouts.admin')
@section('title', 'Livres')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Livres</h1>
            <p class="page-subtitle">Gérez les ouvrages, leurs formats, prix et stocks.</p>
        </div>
        <div class="page-actions">
            <a href="{{ route('admin.books.create') }}" class="btn btn-primary">
                <x-admin.icon name="plus" />
                <span>Ajouter un livre</span>
            </a>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.books.index') }}" method="GET" class="toolbar" role="search">
            <div class="input-group">
                <x-admin.icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Titre ou auteur…" aria-label="Rechercher un livre">
            </div>
            <select name="category_id" class="select" aria-label="Catégorie">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="visibility" class="select" aria-label="Visibilité">
                <option value="">Toutes visibilités</option>
                <option value="active" @selected(request('visibility') === 'active')>Visibles</option>
                <option value="hidden" @selected(request('visibility') === 'hidden')>Masqués</option>
            </select>
            <button type="submit" class="btn btn-secondary">Filtrer</button>
            @if(request()->hasAny(['search', 'category_id', 'visibility']))
                <a href="{{ route('admin.books.index') }}" class="btn btn-ghost">Réinitialiser</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Ouvrage</th>
                        <th>Catégorie</th>
                        <th>Formats & prix</th>
                        <th class="text-center">Stock</th>
                        <th class="text-center">Statut</th>
                        <th class="text-right"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td class="cell-primary">
                                <div class="book-cell">
                                    <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="" class="book-thumb" loading="lazy">
                                    <div style="min-width: 0;">
                                        <div class="cell-title">{{ $book->title }}</div>
                                        <div class="cell-sub">{{ $book->author }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Catégorie" class="text-muted">{{ $book->category?->name ?? '—' }}</td>
                            <td data-label="Formats">
                                <div class="stack" style="gap: .15rem;">
                                    @if($book->is_pdf_available)<span>PDF · <span class="text-strong">{{ number_format($book->price_pdf, 0, ',', ' ') }} F</span></span>@endif
                                    @if($book->is_physical_available)<span>Papier · <span class="text-strong">{{ number_format($book->price_physical, 0, ',', ' ') }} F</span></span>@endif
                                    @if($book->is_audio_available)<span>Audio · <span class="text-strong">{{ number_format($book->price_audio, 0, ',', ' ') }} F</span></span>@endif
                                    @unless($book->is_pdf_available || $book->is_physical_available || $book->is_audio_available)<span class="text-muted">Aucun format en vente</span>@endunless
                                </div>
                            </td>
                            <td data-label="Stock" class="text-center">
                                @if($book->is_physical_available)
                                    <span class="badge {{ $book->stock_physical <= 5 ? 'badge-danger' : ($book->stock_physical <= 10 ? 'badge-warning' : '') }}">{{ $book->stock_physical }} ex.</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td data-label="Statut" class="text-center">
                                <div class="badges" style="justify-content: center;">
                                    <span class="badge {{ $book->is_active ? 'badge-success' : '' }}">{{ $book->is_active ? 'Visible' : 'Masqué' }}</span>
                                    @if($book->is_featured)<span class="badge badge-accent">En avant</span>@endif
                                </div>
                            </td>
                            <td class="td-actions">
                                <div class="cell-actions">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-secondary btn-sm">
                                        <x-admin.icon name="edit" />
                                        <span>Modifier</span>
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" class="inline-form" data-confirm="Retirer « {{ $book->title }} » du catalogue ? Les commandes existantes sont conservées.">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="Supprimer" aria-label="Supprimer {{ $book->title }}">
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
                                    <p class="empty-title">Aucun livre trouvé</p>
                                    <p>Modifiez vos filtres ou ajoutez un nouvel ouvrage.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $books->links('admin.partials.pagination') }}
    </div>
@endsection
