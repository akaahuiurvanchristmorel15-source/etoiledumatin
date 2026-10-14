@extends('layouts.admin')
@section('title', 'Catalogue des livres')

@push('styles')
<style>
    .bk { display: flex; flex-direction: column; gap: 1.5rem; }

    .bk-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1rem; }
    .bk-eyebrow { font-size: .7rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--accent-ink); }
    .bk-lead { margin: .25rem 0 0; color: var(--muted); font-size: .9rem; max-width: 56ch; }

    /* Filtres */
    .bk-filters { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); padding: 1rem 1.25rem; }
    .bk-filters form { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; }
    .bk-search { position: relative; flex: 1 1 260px; min-width: 0; }
    .bk-search svg { position: absolute; left: .85rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .bk-input, .bk-select {
        width: 100%; min-height: 42px; font-family: inherit; font-size: .87rem; color: var(--text);
        background: var(--surface-alt); border: 1px solid var(--border); border-radius: 10px; padding: .6rem .9rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .bk-input { padding-left: 2.5rem; }
    .bk-select { flex: 0 1 240px; width: auto; }
    .bk-input:focus, .bk-select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); background: #fff; }
    .bk-reset { font-size: .8rem; font-weight: 600; color: var(--accent-ink); text-decoration: none; white-space: nowrap; }
    .bk-reset:hover { text-decoration: underline; }

    /* Table */
    .bk-panel { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
    .bk-panel .admin-table-container { border: 0; box-shadow: none; border-radius: 0; }
    .bk-count { padding: .85rem 1.25rem; border-bottom: 1px solid var(--border); font-size: .78rem; color: var(--muted); }
    .bk-item { display: flex; align-items: center; gap: .85rem; min-width: 0; }
    .bk-cover { width: 44px; height: 64px; border-radius: 6px; overflow: hidden; background: #EFECE5; border: 1px solid var(--border); flex-shrink: 0; }
    .bk-cover img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .bk-title { font-weight: 600; color: var(--text); font-size: .9rem; line-height: 1.3; }
    .bk-author { font-size: .76rem; color: var(--muted); }
    .bk-badges { display: flex; gap: .35rem; flex-wrap: wrap; align-items: center; }
    .badge-dark { background: rgba(31, 30, 27, .08); color: var(--text); }
    .badge-purple { background: rgba(108, 52, 131, .12); color: #6C3483; }
    .badge-muted { background: rgba(119, 115, 107, .14); color: var(--muted); }
    .bk-price { font-weight: 600; color: var(--text); font-size: .83rem; }
    .bk-price.sub { font-weight: 500; color: var(--muted); font-size: .76rem; }
    .bk-price.audio { color: #6C3483; }
    .cell-muted { color: var(--muted); }
    .ta-c { text-align: center; } .ta-r { text-align: right; }

    .bk-actions { display: flex; align-items: center; justify-content: flex-end; gap: .5rem; }
    .bk-actions form { margin: 0; }
    .btn-icon { width: 36px; height: 36px; padding: 0 !important; min-height: 0 !important; }
    .btn-danger {
        width: 36px; height: 36px; display: inline-grid; place-items: center; cursor: pointer;
        background: var(--surface); color: #BE123C; border: 1px solid rgba(225, 29, 72, .25); border-radius: 10px;
        transition: background .15s, border-color .15s;
    }
    .btn-danger:hover { background: rgba(225, 29, 72, .08); border-color: #BE123C; }
    .btn-danger:focus-visible { outline: 2px solid #BE123C; outline-offset: 2px; }

    .bk-empty { padding: 3rem 1rem; text-align: center; color: var(--muted); font-size: .88rem; }
    .bk-pages { padding: 1rem 1.25rem; border-top: 1px solid var(--border); }

    @media (max-width: 768px) {
        .bk { gap: 1.15rem; }
        .bk-head { align-items: stretch; }
        .bk-head .admin-btn-primary { width: 100%; }
        .bk-filters { padding: .9rem; }
        .bk-filters form { flex-direction: column; align-items: stretch; }
        .bk-select { flex: 1 1 auto; width: 100%; }
        .bk-reset { text-align: center; padding: .25rem 0; }
        .bk-panel { background: transparent !important; border: none !important; box-shadow: none !important; overflow: visible !important; }
        .bk-count { background: var(--surface); border: 1px solid var(--border); border-radius: 14px; margin-bottom: 0.85rem; padding: 0.75rem 1rem; }
        .bk-pages { padding: 1rem .9rem; overflow-x: auto; background: var(--surface); border: 1px solid var(--border); border-radius: 16px; margin-top: 1rem; }
        .bk-actions { width: 100%; justify-content: flex-end; }
        .bk-prices { text-align: right; }
        .bk-badges { justify-content: flex-end; }
    }
</style>
@endpush

@section('content')
<div class="bk">

    <!-- En-tête -->
    <div class="bk-head">
        <div>
            <span class="bk-eyebrow">Bibliothèque éditoriale</span>
            <h1 class="hero-title font-editorial" style="font-size: 2.2rem; margin: 0.2rem 0 0.4rem 0;">Catalogue des Livres</h1>
            <p class="bk-lead">Gérez les œuvres physiques, les manuscrits numériques PDF, les tarifs et les stocks disponibles.</p>
        </div>
        <a href="{{ route('admin.books.create') }}" class="admin-btn-primary">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span>Ajouter un livre</span>
        </a>
    </div>

    <!-- Recherche & filtres -->
    <div class="bk-filters">
        <form action="{{ route('admin.books.index') }}" method="GET" role="search">
            <div class="bk-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="search" name="search" value="{{ request('search') }}" class="bk-input" placeholder="Rechercher par titre, auteur, mot-clé…" aria-label="Rechercher un livre">
            </div>

            <select name="category_id" class="bk-select" aria-label="Filtrer par collection">
                <option value="">Toutes les collections</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>

            <button type="submit" class="admin-btn-primary">Filtrer</button>

            @if(request()->hasAny(['search', 'category_id']))
                <a href="{{ route('admin.books.index') }}" class="bk-reset">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- Liste -->
    <div class="bk-panel">
        @if(method_exists($books, 'total'))
            <div class="bk-count">{{ $books->total() }} ouvrage(s)</div>
        @endif

        <div class="admin-table-container">
            <table class="admin-table stack-table">
                <thead>
                    <tr>
                        <th>Ouvrage</th>
                        <th>Collection</th>
                        <th>Formats</th>
                        <th>Prix</th>
                        <th class="ta-c">Stock physique</th>
                        <th class="ta-c">Visibilité</th>
                        <th class="ta-r">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $book)
                        <tr>
                            <td class="cell-main">
                                <div class="bk-item">
                                    <div class="bk-cover">
                                        <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="Couverture de {{ $book->title }}" loading="lazy">
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="bk-title">{{ $book->title }}</div>
                                        <div class="bk-author">{{ $book->author }}</div>
                                        @if($book->is_featured)
                                            <span class="admin-badge admin-badge-gold" style="margin-top: .3rem;">Bestseller</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td data-label="Collection" class="cell-muted">{{ $book->category?->name ?? 'Non classé' }}</td>
                            <td data-label="Formats">
                                <div class="bk-badges">
                                    @if($book->is_pdf_available)<span class="admin-badge admin-badge-gold">PDF</span>@endif
                                    @if($book->is_physical_available)<span class="admin-badge badge-dark">Relié</span>@endif
                                    @if($book->is_audio_available)<span class="admin-badge badge-purple">Audio</span>@endif
                                </div>
                            </td>
                            <td data-label="Prix">
                                <div class="bk-prices">
                                    @if($book->is_pdf_available)
                                        <div class="bk-price">PDF : {{ number_format((float) ($book->price_pdf ?? 0), 0, ',', ' ') }} FCFA</div>
                                    @endif
                                    @if($book->is_physical_available)
                                        <div class="bk-price sub">Papier : {{ number_format((float) ($book->price_physical ?? 0), 0, ',', ' ') }} FCFA</div>
                                    @endif
                                    @if($book->is_audio_available)
                                        <div class="bk-price sub audio">Audio : {{ number_format((float) ($book->price_audio ?? 0), 0, ',', ' ') }} FCFA</div>
                                    @endif
                                </div>
                            </td>
                            <td data-label="Stock" class="ta-c">
                                @if($book->is_physical_available)
                                    <span class="admin-badge {{ $book->stock_physical <= 5 ? 'admin-badge-red' : 'admin-badge-gold' }}">{{ $book->stock_physical }} ex.</span>
                                @else
                                    <span class="cell-muted" style="font-size: .75rem;">N/A</span>
                                @endif
                            </td>
                            <td data-label="Visibilité" class="ta-c">
                                @if($book->is_active)
                                    <span class="admin-badge admin-badge-green">Actif</span>
                                @else
                                    <span class="admin-badge badge-muted">Masqué</span>
                                @endif
                            </td>
                            <td class="ta-r cell-actions">
                                <div class="bk-actions">
                                    <a href="{{ route('admin.books.edit', $book) }}" class="admin-btn-secondary btn-icon" title="Modifier" aria-label="Modifier {{ $book->title }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                    </a>
                                    <form action="{{ route('admin.books.destroy', $book) }}" method="POST" onsubmit="return confirm('Confirmez-vous la suppression de ce livre ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger" title="Supprimer" aria-label="Supprimer {{ $book->title }}">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="bk-empty">Aucun livre ne correspond à votre recherche.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($books->hasPages())
            <div class="bk-pages">{{ $books->links() }}</div>
        @endif
    </div>

</div>
@endsection