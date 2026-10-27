@extends('layouts.app')
@section('title', $book->title.' — NSOROMMA')
@section('meta_description', Str::limit(strip_tags($book->description), 160))
@section('canonical', route('catalog.show', $book->slug))
@section('og_type', 'book')
@section('og_image', $book->cover_image ? asset($book->cover_image) : asset('images/brand/nsoromma-logo.png'))
@section('twitter_card', 'summary_large_image')

@push('structured-data')
    @php
        $offers = collect([
            'pdf' => ['available' => $book->is_pdf_available, 'price' => $book->price_pdf, 'name' => 'e-Book PDF', 'inStock' => true],
            'physical' => ['available' => $book->is_physical_available, 'price' => $book->price_physical, 'name' => 'Livre papier', 'inStock' => $book->stock_physical > 0],
        ])->filter(fn (array $offer) => $offer['available'])->map(fn (array $offer) => [
            '@type' => 'Offer',
            'name' => $offer['name'],
            'price' => (string) $offer['price'],
            'priceCurrency' => 'XOF',
            'availability' => $offer['inStock'] ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('catalog.show', $book->slug),
        ])->values();
        $approvedReviews = $book->approvedReviews;
        $bookSchema = array_filter([
            '@context' => 'https://schema.org',
            '@type' => ['Book', 'Product'],
            'name' => $book->title,
            'author' => ['@type' => 'Person', 'name' => $book->author],
            'description' => Str::limit(strip_tags($book->description), 500),
            'image' => $book->cover_image ? asset($book->cover_image) : null,
            'url' => route('catalog.show', $book->slug),
            'isbn' => $book->isbn ?: null,
            'numberOfPages' => $book->pages_count ?: null,
            'inLanguage' => $book->language,
            'publisher' => $book->publisher ? ['@type' => 'Organization', 'name' => $book->publisher] : null,
            'datePublished' => $book->published_year ? (string) $book->published_year : null,
            'genre' => $book->category?->name,
            'offers' => $offers->isNotEmpty() ? $offers->all() : null,
            'aggregateRating' => $approvedReviews->isNotEmpty() ? [
                '@type' => 'AggregateRating',
                'ratingValue' => round($approvedReviews->avg('rating'), 1),
                'reviewCount' => $approvedReviews->count(),
            ] : null,
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($bookSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/catalog-page.css') }}?v={{ filemtime(public_path('css/catalog-page.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ filemtime(public_path('css/pages.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}" defer></script>
@endpush

@section('content')
    @php
        $formats = collect([
            'pdf' => ['available' => $book->is_pdf_available, 'label' => 'e-Book PDF', 'sub' => 'Téléchargement immédiat, exemplaire personnalisé', 'price' => $book->formatted_price_pdf],
            'physical' => ['available' => $book->is_physical_available, 'label' => 'Livre papier', 'sub' => $book->stock_physical > 0 ? $book->stockLabel().' · expédié sous 48 h' : 'Épuisé — rupture de stock', 'price' => $book->formatted_price_physical, 'disabled' => $book->stock_physical <= 0],
        ])->filter(fn (array $format) => $format['available']);
        $defaultFormat = $formats->filter(fn (array $format) => empty($format['disabled']))->keys()->first();
        $reviews = $book->approvedReviews;
        $averageRating = $reviews->isNotEmpty() ? round($reviews->avg('rating'), 1) : null;
        $paragraphs = collect(preg_split('/\R{2,}/', trim($book->description)))->filter();
        $audioPlans = $book->audioPlans();
        $audioParts = $book->offersAudioSubscription() ? $book->parts->filter(fn ($part) => $part->tracks->isNotEmpty()) : collect();
    @endphp

    <div class="pg pg-detail">
        <ol class="pg-crumbs" style="padding-top: 1rem;">
            <li><a href="{{ route('home') }}">Accueil</a></li>
            <li><a href="{{ route('catalog.index') }}">Catalogue</a></li>
            @if($book->category)
                <li><a href="{{ route('catalog.index', ['category' => $book->category->slug]) }}">{{ $book->category->name }}</a></li>
            @endif
        </ol>

        <section class="spotlight" style="margin-top: 2rem;">
            <article class="spotlight-slide">
                <div class="spotlight-top">
                    <div class="spotlight-cover">
                        <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}"
                             alt="Couverture de {{ $book->title }}"
                             onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                    </div>

                    <div class="spotlight-intro">
                        <h1 class="spotlight-title {{ mb_strlen($book->title) > 55 ? 'is-long' : '' }}">{{ $book->title }}</h1>
                        <p class="spotlight-author">
                            <a href="{{ route('catalog.index', ['author' => $book->author]) }}" style="color: inherit; text-decoration: none;">{{ $book->author }}</a>
                        </p>
                        @if($averageRating)
                            <p class="pg-hint" style="margin-top: .6rem;">
                                <span class="pg-stars" aria-hidden="true">@for($star = 1; $star <= 5; $star++)<span class="{{ $star <= round($averageRating) ? '' : 'is-off' }}">★</span>@endfor</span>
                                {{ number_format($averageRating, 1, ',', ' ') }} · {{ $reviews->count() }} avis
                            </p>
                        @endif

                        @if($formats->isNotEmpty())
                            <form action="{{ route('cart.add') }}" method="POST" class="pg-detail-formats" x-data="{ format: @js($defaultFormat) }">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">

                                <div class="pg-choices" role="radiogroup" aria-label="Choisir un format">
                                    @foreach($formats as $key => $format)
                                        <label class="pg-choice">
                                            <input type="radio" name="format" value="{{ $key }}" x-model="format" @checked($key === $defaultFormat) @disabled(! empty($format['disabled']))>
                                            <span class="pg-choice-main">
                                                <span class="pg-choice-dot" aria-hidden="true"></span>
                                                <span>
                                                    <span class="pg-choice-title">{{ $format['label'] }}</span>
                                                    <span class="pg-choice-sub">{{ $format['sub'] }}</span>
                                                </span>
                                            </span>
                                            <span class="pg-choice-price">{{ $format['price'] }}</span>
                                        </label>
                                    @endforeach
                                </div>

                                <div class="pg-detail-buy">
                                    <label class="pg-qty" x-show="format === 'physical'" x-cloak style="display: none;">
                                        <span>Quantité</span>
                                        <select name="quantity">
                                            @for($quantity = 1; $quantity <= max(1, min(10, $book->stock_physical)); $quantity++)
                                                <option value="{{ $quantity }}">{{ $quantity }}</option>
                                            @endfor
                                        </select>
                                    </label>
                                    <button type="submit" class="pg-btn" @disabled(! $defaultFormat)>
                                        <span>Ajouter au panier</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="8 7 17 7 17 16"/></svg>
                                    </button>

                                    <div class="spotlight-tools" style="margin-left: auto;">
                                        <button type="button" class="spotlight-tool" data-share data-share-title="{{ $book->title }}" data-share-url="{{ url()->current() }}" aria-label="Partager ce livre">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><line x1="8.2" y1="10.8" x2="15.8" y2="6.2"/><line x1="8.2" y1="13.2" x2="15.8" y2="17.8"/></svg>
                                            <span class="spotlight-tool-tip" data-share-label>Partager</span>
                                        </button>
                                        @if($book->is_pdf_available)
                                            <a href="{{ route('catalog.preview', $book->slug) }}" class="spotlight-tool" aria-label="Lire un extrait">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><polyline points="7 10 12 15 17 10"/><path d="M5 20h14"/></svg>
                                                <span class="spotlight-tool-tip">Lire un extrait</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        @else
                            <p class="pg-subtitle">Ce livre n'est pas disponible à la vente pour le moment.</p>
                        @endif

                        @if($book->is_audio_available && $book->audio_sample_path)
                            <div class="pg-player"
                                 x-data="{ audio: null, playing: false, progress: 0,
                                    toggle() {
                                        if (! this.audio) {
                                            this.audio = new Audio(@js(asset($book->audio_sample_path)));
                                            this.audio.addEventListener('timeupdate', () => { this.progress = this.audio.duration ? this.audio.currentTime / this.audio.duration * 100 : 0 });
                                            this.audio.addEventListener('ended', () => { this.playing = false; this.progress = 0 });
                                        }
                                        this.playing ? this.audio.pause() : this.audio.play();
                                        this.playing = ! this.playing;
                                    },
                                    seek(event) { if (this.audio && this.audio.duration) { this.audio.currentTime = event.offsetX / event.currentTarget.clientWidth * this.audio.duration } }
                                 }">
                                <button type="button" class="pg-player-btn" @click="toggle()" :aria-label="playing ? 'Mettre en pause l\'extrait audio' : 'Écouter l\'extrait audio'" aria-label="Écouter l'extrait audio">
                                    <svg x-show="! playing" width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6 4 20 12 6 20 6 4"/></svg>
                                    <svg x-show="playing" x-cloak width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" style="display: none;"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
                                </button>
                                <div class="pg-player-track" @click="seek($event)" role="presentation"><span class="pg-player-fill" :style="`width: ${progress}%`"></span></div>
                                <span class="pg-player-label">Extrait audio</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="spotlight-panel">
                    <div class="spotlight-body">
                        <div class="spotlight-main">
                            <h2 class="spotlight-heading">Description</h2>
                            @foreach($paragraphs as $paragraph)
                                <p class="spotlight-text">{{ $paragraph }}</p>
                            @endforeach

                            @if($book->excerpt)
                                <h2 class="spotlight-heading" style="margin-top: 2.25rem;">Extrait</h2>
                                <blockquote class="pg-excerpt">{!! nl2br(e(Str::limit($book->excerpt, 900))) !!}</blockquote>
                            @endif
                        </div>

                        <dl class="spotlight-details">
                            <div>
                                <dt>Édition</dt>
                                <dd>{{ $book->publisher ?: 'NSOROMMA' }}@if($book->published_year), {{ $book->published_year }}@endif
                                    @if($book->category)<br>Collection {{ $book->category->name }}@endif</dd>
                            </div>
                            <div>
                                <dt>Langue</dt>
                                <dd>{{ $book->language }}</dd>
                            </div>
                            @if($book->pages_count || $book->isbn || $book->weight_grams)
                                <div>
                                    <dt>Caractéristiques</dt>
                                    <dd>
                                        @if($book->pages_count){{ $book->pages_count }} pages<br>@endif
                                        @if($book->isbn)ISBN : {{ $book->isbn }}<br>@endif
                                        @if($book->is_physical_available && $book->weight_grams)Poids : {{ $book->weight_grams }} g @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>
            </article>
        </section>

        {{-- Écoute audio par abonnement --}}
        @if($audioParts->isNotEmpty())
            <section class="pg-section" id="ecoute" aria-labelledby="listen-title">
                <h2 id="listen-title" class="pg-related-title">Écouter ce livre</h2>
                <p class="pg-subtitle" style="margin-top: -.5rem;">Abonnez-vous à la partie de votre choix, puis écoutez-la en streaming depuis votre bibliothèque, dans la langue qui vous convient.</p>

                <div class="pg-audio-parts">
                    @foreach($audioParts as $part)
                        <form action="{{ route('cart.add') }}" method="POST" class="pg-audio-part">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <input type="hidden" name="format" value="audio">
                            <input type="hidden" name="book_part_id" value="{{ $part->id }}">

                            <div class="pg-audio-part-head">
                                <span class="pg-owned-meta">Partie {{ $part->position }}</span>
                                <h3 class="pg-owned-title" style="margin-top: .2rem;">{{ $part->title }}</h3>
                                @if($part->summary)
                                    <p class="pg-hint" style="margin: .4rem 0 0;">{{ $part->summary }}</p>
                                @endif
                                <p class="pg-hint" style="margin: .5rem 0 0;">{{ implode(' · ', $part->availableLanguages()) }}</p>
                            </div>

                            <div class="pg-audio-part-buy">
                                <label class="sr-only" for="plan_{{ $part->id }}">Durée d'abonnement</label>
                                <select id="plan_{{ $part->id }}" name="access_days" class="pg-select">
                                    @foreach($audioPlans as $days => $price)
                                        <option value="{{ $days }}">{{ config('audio.plans.'.$days) }} — {{ number_format($price, 0, ',', ' ') }} FCFA</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="pg-btn">S'abonner</button>
                            </div>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Avis --}}
        <section class="pg-section" aria-labelledby="reviews-title">
            <h2 id="reviews-title" class="pg-related-title">Avis des lecteurs</h2>
            <div class="pg-reviews">
                <div>
                    @forelse($reviews as $review)
                        @php $reviewer = $review->customer_name ?? $review->user?->name ?? 'Lecteur'; @endphp
                        <article class="pg-review">
                            <span class="pg-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($reviewer, 0, 1)) }}</span>
                            <div>
                                <div class="pg-review-head">
                                    <span class="pg-review-name">{{ $reviewer }}</span>
                                    <span class="pg-stars" aria-label="{{ $review->rating }} sur 5">@for($star = 1; $star <= 5; $star++)<span class="{{ $star <= $review->rating ? '' : 'is-off' }}" aria-hidden="true">★</span>@endfor</span>
                                    <span class="pg-review-date">{{ $review->created_at->format('d/m/Y') }}</span>
                                </div>
                                <p class="pg-review-text">{{ $review->comment }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="pg-subtitle" style="margin-top: 0;">Aucun avis pour l'instant. Soyez le premier à partager votre lecture.</p>
                    @endforelse
                </div>

                <form action="{{ route('books.review', $book) }}" method="POST" class="pg-panel pg-form">
                    @csrf
                    <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="pg-honeypot" aria-hidden="true">
                    <h3 class="pg-summary-title" style="margin-bottom: 0;">Donner mon avis</h3>
                    <div class="pg-field">
                        <span class="pg-label" id="rating-label">Note</span>
                        <div class="pg-rating-input" role="radiogroup" aria-labelledby="rating-label">
                            @for($star = 5; $star >= 1; $star--)
                                <input type="radio" id="rating-{{ $star }}" name="rating" value="{{ $star }}" @checked((int) old('rating', 5) === $star)>
                                <label for="rating-{{ $star }}" aria-label="{{ $star }} sur 5">★</label>
                            @endfor
                        </div>
                        @error('rating')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pg-field">
                        <label class="pg-label" for="customer_name">Votre nom</label>
                        <input id="customer_name" type="text" name="customer_name" value="{{ old('customer_name', auth()->user()?->name) }}" required maxlength="100" class="pg-input @error('customer_name') is-invalid @enderror">
                        @error('customer_name')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="pg-field">
                        <label class="pg-label" for="comment">Votre avis</label>
                        <textarea id="comment" name="comment" required minlength="5" maxlength="1000" class="pg-textarea @error('comment') is-invalid @enderror">{{ old('comment') }}</textarea>
                        @error('comment')<span class="pg-error">{{ $message }}</span>@enderror
                    </div>
                    <button type="submit" class="pg-btn pg-btn-block">Publier mon avis</button>
                    <p class="pg-hint" style="margin: 0;">Les avis sont publiés après relecture par notre équipe.</p>
                </form>
            </div>
        </section>

        {{-- Livres similaires --}}
        @if($relatedBooks->isNotEmpty())
            <section class="pg-section" aria-labelledby="related-title" style="margin-top: 4.5rem;">
                <h2 id="related-title" class="pg-related-title">Dans la même veine</h2>
                <ul class="catalog-grid">
                    @foreach($relatedBooks as $index => $related)
                        <li class="catalog-item" style="--i: {{ $index }};">
                            <a href="{{ route('catalog.show', $related->slug) }}" class="catalog-card">
                                <span class="catalog-cover">
                                    <img src="{{ $related->cover_image ? asset($related->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="Couverture de {{ $related->title }}" loading="lazy">
                                </span>
                                <span class="catalog-formats">{{ collect(['PDF' => $related->is_pdf_available, 'Papier' => $related->is_physical_available, 'Audio' => $related->is_audio_available])->filter()->keys()->implode(' · ') }}</span>
                                <span class="catalog-book-title">{{ $related->title }}</span>
                                <span class="catalog-book-author">{{ $related->author }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
