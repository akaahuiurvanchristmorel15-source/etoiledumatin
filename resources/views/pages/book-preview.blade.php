<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, follow">
    <title>Extrait — {{ $book->title }} — NSOROMMA</title>
    <link rel="canonical" href="{{ route('catalog.show', $book->slug) }}">
    <link rel="icon" type="image/png" href="{{ asset('images/brand/favicon.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,500;0,600;1,400&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/reader.css') }}?v={{ filemtime(public_path('css/reader.css')) }}">
</head>
<body class="reader">
    @php
        $previewUrl = route('catalog.preview-pdf', $book);
        $cover = $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg');
    @endphp

    <header class="reader-bar">
        <a href="{{ route('catalog.show', $book->slug) }}" class="reader-back" aria-label="Retour à la fiche du livre">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="11 6 5 12 11 18"/></svg>
            <span>Retour</span>
        </a>

        <div class="reader-heading">
            <p class="reader-kicker">Extrait gratuit</p>
            <h1 class="reader-title">{{ $book->title }}</h1>
            <p class="reader-author">{{ $book->author }}</p>
        </div>

        <form action="{{ route('cart.add') }}" method="POST" class="reader-buy">
            @csrf
            <input type="hidden" name="book_id" value="{{ $book->id }}">
            <input type="hidden" name="format" value="pdf">
            <button type="submit" class="reader-btn">
                <span class="reader-btn-label">Acheter<span class="reader-btn-extra"> l'e-book</span></span>
                <span class="reader-btn-price">{{ $book->formatted_price_pdf }}</span>
            </button>
        </form>
    </header>

    <main class="reader-stage">
        {{-- Ordinateur et tablette : le PDF s'affiche directement dans la page --}}
        <div class="reader-sheet">
            <iframe src="{{ $previewUrl }}#view=FitH&amp;toolbar=0&amp;navpanes=0" title="Extrait de « {{ $book->title }} »"></iframe>
        </div>

        {{-- Mobile : les navigateurs n'affichent pas les PDF intégrés, on propose de l'ouvrir --}}
        <div class="reader-mobile">
            <img src="{{ $cover }}" alt="Couverture de « {{ $book->title }} »" class="reader-cover">
            <p class="reader-mobile-text">Découvrez les premières pages de ce livre, offertes par NSOROMMA.</p>
            <a href="{{ $previewUrl }}" class="reader-btn reader-btn-ghost">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 5.5A1.5 1.5 0 0 1 3.5 4H9a3 3 0 0 1 3 3v13a2.5 2.5 0 0 0-2.5-2.5h-6A1.5 1.5 0 0 1 2 16z"/><path d="M22 5.5A1.5 1.5 0 0 0 20.5 4H15a3 3 0 0 0-3 3v13a2.5 2.5 0 0 1 2.5-2.5h6a1.5 1.5 0 0 0 1.5-1.5z"/></svg>
                <span>Lire l'extrait</span>
            </a>
        </div>
    </main>

    <footer class="reader-foot">
        <span>Aperçu limité aux premières pages</span>
        <a href="{{ $previewUrl }}">Ouvrir le PDF en plein écran</a>
    </footer>
</body>
</html>
