@props([
    'book' => null,
    'title' => null,
    'author' => null,
    'image' => null,
    'price' => 'Buy Now',
    'id' => null,
])

@php
    // Support both: Book model object and scalar props (backward compatibility)
    if ($book) {
        $bookId = $book->id;
        $bookTitle = $book->title;
        $bookAuthor = $book->author;
        $bookSlug = $book->slug;
        $bookImage = $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg');
        $bookPrice = $book->is_pdf_available ? $book->formatted_price_pdf : ($book->is_physical_available ? $book->formatted_price_physical : '');
        $bookRating = $book->average_rating;
        $reviewCount = $book->approvedReviews ? $book->approvedReviews->count() : 0;
        $hasPdf = $book->is_pdf_available;
        $hasPhysical = $book->is_physical_available;
        $hasAudio = $book->is_audio_available;
        $detailUrl = route('catalog.show', $bookSlug);
    } else {
        $bookId = $id;
        $bookTitle = $title;
        $bookAuthor = $author;
        $bookSlug = null;
        $bookImage = asset('images/books/' . $image);
        $bookPrice = $price;
        $bookRating = 5.0;
        $reviewCount = 0;
        $hasPdf = false;
        $hasPhysical = false;
        $hasAudio = false;
        $detailUrl = '#';
    }

    $fullStars = floor($bookRating);
    $hasHalfStar = ($bookRating - $fullStars) >= 0.5;
    $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
@endphp

<article class="book-card" data-title="{{ strtolower($bookTitle) }}" data-author="{{ strtolower($bookAuthor) }}" data-id="{{ $bookId }}">
    
    {{-- Couverture avec lien vers la fiche détaillée --}}
    <a href="{{ $detailUrl }}" class="book-cover-wrap" aria-label="Voir les détails de {{ $bookTitle }}">
        <img 
            src="{{ $bookImage }}" 
            alt="Couverture du livre {{ $bookTitle }} par {{ $bookAuthor }}"
            class="book-cover-img"
            loading="lazy"
            data-fallback="{{ asset('images/books/fallback-book.jpg') }}"
        >

        {{-- Badges format --}}
        @if($book)
            <div style="position:absolute;top:8px;left:8px;display:flex;gap:4px;flex-wrap:wrap;">
                @if($hasPdf)
                    <span style="background:#2F6F4E;color:#fff;font-size:0.6rem;font-weight:700;padding:2px 7px;border-radius:6px;text-transform:uppercase;">PDF</span>
                @endif
                @if($hasPhysical)
                    <span style="background:#D9AE5C;color:#191919;font-size:0.6rem;font-weight:700;padding:2px 7px;border-radius:6px;text-transform:uppercase;">Papier</span>
                @endif
                @if($hasAudio)
                    <span style="background:#6C3483;color:#fff;font-size:0.6rem;font-weight:700;padding:2px 7px;border-radius:6px;text-transform:uppercase;">Audio</span>
                @endif
            </div>
        @endif
    </a>

    {{-- Informations du livre --}}
    <div class="book-info">
        <div class="book-rating" aria-label="Note {{ $bookRating }} sur 5">
            @for($i = 0; $i < $fullStars; $i++)
                <span aria-hidden="true">★</span>
            @endfor
            @if($hasHalfStar)
                <span aria-hidden="true" style="opacity:0.6;">★</span>
            @endif
            @for($i = 0; $i < $emptyStars; $i++)
                <span aria-hidden="true" style="color:#D5CFC5;">★</span>
            @endfor
            <span style="color:#8B857C;font-size:0.68rem;margin-left:2px;">({{ number_format($bookRating, 1) }})</span>
        </div>

        <h3 class="book-title" title="{{ $bookTitle }}">
            <a href="{{ $detailUrl }}" style="color:inherit;text-decoration:none;">{{ $bookTitle }}</a>
        </h3>

        <p class="book-author" title="{{ $bookAuthor }}">
            {{ $bookAuthor }}
        </p>

        {{-- Prix et bouton d'achat --}}
        @if($book && ($hasPdf || $hasPhysical || $hasAudio))
            <div style="display:flex;align-items:center;gap:0.4rem;margin-bottom:0.5rem;flex-wrap:wrap;">
                @if($hasPdf)
                    <span style="font-size:0.82rem;font-weight:800;color:#1E1D1B;">{{ $book->formatted_price_pdf }}</span>
                @endif
                @if($hasPdf && ($hasPhysical || $hasAudio))
                    <span style="color:#D5CFC5;font-size:0.7rem;">|</span>
                @endif
                @if($hasPhysical)
                    <span style="font-size:0.75rem;font-weight:600;color:#8B857C;">{{ $book->formatted_price_physical }}</span>
                @endif
                @if($hasPhysical && $hasAudio)
                    <span style="color:#D5CFC5;font-size:0.7rem;">|</span>
                @endif
                @if($hasAudio)
                    <span style="font-size:0.75rem;font-weight:700;color:#6C3483;">{{ $book->formatted_price_audio }}</span>
                @endif
            </div>
        @endif

        <a 
            href="{{ $detailUrl }}"
            class="buy-btn"
            aria-label="Voir {{ $bookTitle }} par {{ $bookAuthor }}"
        >
            <span class="buy-btn-text">{{ $book ? 'Voir le livre' : $bookPrice }}</span>
        </a>
    </div>

</article>
