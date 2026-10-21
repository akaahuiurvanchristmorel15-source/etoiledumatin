@extends('layouts.admin')
@section('title', 'Stock')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Stock des livres papier</h1>
            <p class="page-subtitle">Quantités restantes, du plus faible au plus élevé. Le stock baisse automatiquement à chaque commande payée.</p>
        </div>
    </div>

    <div class="stats">
        <div class="stat">
            <div class="stat-label">Exemplaires en stock</div>
            <div class="stat-value">{{ number_format($totalCopies, 0, ',', ' ') }}</div>
            <div class="stat-meta">Sur {{ $paperTitles }} {{ Str::plural('titre', $paperTitles) }} papier</div>
        </div>
        <div class="stat">
            <div class="stat-label">Stock faible</div>
            <div class="stat-value">{{ $lowStock }}</div>
            <div class="stat-meta">{{ \App\Models\Book::LOW_STOCK_THRESHOLD }} exemplaires ou moins</div>
        </div>
        <div class="stat">
            <div class="stat-label">Épuisés</div>
            <div class="stat-value">{{ $outOfStock }}</div>
            <div class="stat-meta">Ne peuvent plus être commandés en papier</div>
        </div>
        <div class="stat">
            <div class="stat-label">Disponibles</div>
            <div class="stat-value">{{ $paperTitles - $outOfStock }}</div>
            <div class="stat-meta">Titres papier commandables</div>
        </div>
    </div>

    <div class="card">
        <form action="{{ route('admin.stock.index') }}" method="GET" class="toolbar" role="search">
            <div class="input-group">
                <x-admin.icon name="search" />
                <input type="search" name="search" value="{{ request('search') }}" class="input" placeholder="Titre ou auteur…" aria-label="Rechercher un livre">
            </div>
            <nav class="segmented" aria-label="Filtrer par niveau de stock">
                <a href="{{ request()->fullUrlWithQuery(['level' => null, 'page' => null]) }}" class="{{ ! request('level') ? 'is-active' : '' }}">Tous</a>
                <a href="{{ request()->fullUrlWithQuery(['level' => 'low', 'page' => null]) }}" class="{{ request('level') === 'low' ? 'is-active' : '' }}">Faible ({{ $lowStock }})</a>
                <a href="{{ request()->fullUrlWithQuery(['level' => 'out', 'page' => null]) }}" class="{{ request('level') === 'out' ? 'is-active' : '' }}">Épuisés ({{ $outOfStock }})</a>
            </nav>
            <button type="submit" class="btn btn-secondary">Rechercher</button>
        </form>

        <div class="table-wrap">
            <table class="table table-responsive">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th class="text-center">Restant</th>
                        <th class="text-center">État</th>
                        <th class="text-right">Ajuster le stock</th>
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
                                        <div class="cell-sub">{{ $book->author }} · {{ number_format($book->price_physical, 0, ',', ' ') }} FCFA</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Restant" class="text-center">
                                <span class="stock-count">{{ $book->stock_physical }}</span>
                            </td>
                            <td data-label="État" class="text-center">
                                @switch($book->stockLevel())
                                    @case('out')
                                        <span class="badge badge-danger">Épuisé</span>
                                        @break
                                    @case('low')
                                        <span class="badge badge-warning">Faible</span>
                                        @break
                                    @default
                                        <span class="badge badge-success">En stock</span>
                                @endswitch
                            </td>
                            <td class="td-actions">
                                <form action="{{ route('admin.stock.update', $book) }}" method="POST" class="stock-form">
                                    @csrf
                                    @method('PATCH')
                                    <label for="stock-{{ $book->id }}" class="sr-only">Nouveau stock pour {{ $book->title }}</label>
                                    <input id="stock-{{ $book->id }}" type="number" name="stock_physical" value="{{ $book->stock_physical }}" min="0" max="100000" required class="input stock-input">
                                    <button type="submit" class="btn btn-secondary btn-sm">Enregistrer</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty">
                                    <p class="empty-title">Aucun livre papier</p>
                                    <p>Aucun livre ne correspond à ces critères.</p>
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
