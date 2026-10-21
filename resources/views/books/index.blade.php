@extends('layouts.app')

@section('title', 'NSOROMMA — Librairie panafricaine : livres papier, PDF & audio')
@section('meta_description', 'NSOROMMA : spiritualité, philosophie, religion et savoirs africains en livre papier, e-book PDF et livre audio.')
@section('body_class', 'broks-page home-page')
@section('canonical', route('home'))

@push('structured-data')
    @php
        $siteSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'WebSite',
                    'name' => 'NSOROMMA',
                    'url' => route('home'),
                    'inLanguage' => 'fr',
                    'potentialAction' => [
                        '@type' => 'SearchAction',
                        'target' => ['@type' => 'EntryPoint', 'urlTemplate' => route('catalog.index').'?search={search_term_string}'],
                        'query-input' => 'required name=search_term_string',
                    ],
                ],
                [
                    '@type' => 'BookStore',
                    'name' => 'NSOROMMA',
                    'url' => route('home'),
                    'logo' => asset('images/brand/nsoromma-logo.png'),
                    'description' => 'Librairie panafricaine : spiritualité, philosophie, religion et savoirs africains en papier, PDF et audio.',
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($siteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/home.css') }}?v={{ filemtime(public_path('css/home.css')) }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/home.js') }}?v={{ filemtime(public_path('js/home.js')) }}" defer></script>
@endpush

@section('content')
    @php
        $shelfTones = ['sand', 'sage', 'slate', 'clay', 'lilac'];
        $formatOf = fn ($book) => $book->is_pdf_available ? 'pdf' : ($book->hasStock() ? 'physical' : ($book->is_audio_available ? 'audio' : null));
    @endphp

    <div class="home">
        @if($spotlightBooks->isNotEmpty())
            <section class="spotlight" x-data="{ current: 0, total: {{ $spotlightBooks->count() }}, go(step) { this.current = (this.current + step + this.total) % this.total } }"
                     @keydown.arrow-up.prevent="go(-1)" @keydown.arrow-down.prevent="go(1)" aria-roledescription="carrousel" aria-label="Livres à la une">

                @if($spotlightBooks->count() > 1)
                    <div class="spotlight-nav" aria-label="Changer de livre">
                        <button type="button" class="spotlight-arrow" @click="go(-1)" aria-label="Livre précédent">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 5 19 12"/></svg>
                        </button>
                        <button type="button" class="spotlight-arrow is-ring" @click="go(1)" aria-label="Livre suivant">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
                        </button>
                        <span class="spotlight-counter" aria-live="polite"><span x-text="String(current + 1).padStart(2, '0')">01</span> / {{ str_pad($spotlightBooks->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                @endif

                @foreach($spotlightBooks as $index => $book)
                    @php
                        $format = $formatOf($book);
                        $review = $book->approvedReviews->first();
                        $paragraphs = collect(preg_split('/\R{2,}/', trim($book->description)))->filter()->take(2);
                        $details = collect([
                            'Format' => trim(collect([$book->pages_count ? $book->pages_count.' pages' : null, $book->published_year, $book->isbn ? 'ISBN : '.$book->isbn : null])->filter()->implode(' · ')),
                        ])->filter();
                    @endphp

                    <article class="spotlight-slide" x-show="current === {{ $index }}" @if($index > 0) x-cloak style="display: none;" @endif
                             x-transition:enter="slide-enter" x-transition:enter-start="slide-enter-start" x-transition:enter-end="slide-enter-end"
                             aria-roledescription="diapositive" aria-label="{{ $index + 1 }} sur {{ $spotlightBooks->count() }}">

                        <div class="spotlight-top">
                            <a href="{{ route('catalog.show', $book->slug) }}" class="spotlight-cover" tabindex="-1" aria-hidden="true">
                                <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt=""
                                     onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                            </a>

                            <div class="spotlight-intro">
                                <h1 class="spotlight-title {{ mb_strlen($book->title) > 55 ? 'is-long' : '' }}">{{ $book->title }}</h1>
                                <p class="spotlight-author">{{ $book->author }}</p>
                                @if($book->excerpt || $book->description)
                                    <p class="spotlight-tagline">{{ Str::limit($book->excerpt ?: $book->description, 150) }}</p>
                                @endif

                                <div class="spotlight-actions">
                                    <a href="{{ route('catalog.show', $book->slug) }}" class="spotlight-cta">
                                        <span>Découvrir le livre</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="8 7 17 7 17 16"/></svg>
                                    </a>

                                    <div class="spotlight-tools">
                                        @if($format)
                                            <form action="{{ route('cart.add') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                                <input type="hidden" name="format" value="{{ $format }}">
                                                <button type="submit" class="spotlight-tool" aria-label="Ajouter « {{ $book->title }} » au panier">
                                                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 7h12l1 13H5z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
                                                    <span class="spotlight-tool-tip">Ajouter au panier</span>
                                                </button>
                                            </form>
                                        @endif
                                        <button type="button" class="spotlight-tool" data-share data-share-title="{{ $book->title }}" data-share-url="{{ route('catalog.show', $book->slug) }}" aria-label="Partager « {{ $book->title }} »">
                                            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><line x1="8.2" y1="10.8" x2="15.8" y2="6.2"/><line x1="8.2" y1="13.2" x2="15.8" y2="17.8"/></svg>
                                            <span class="spotlight-tool-tip" data-share-label>Partager</span>
                                        </button>
                                        @if($book->is_pdf_available)
                                            <a href="{{ route('catalog.preview-pdf', $book) }}" class="spotlight-tool" aria-label="Lire un extrait de « {{ $book->title }} »">
                                                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 4v11"/><polyline points="7 10 12 15 17 10"/><path d="M5 20h14"/></svg>
                                                <span class="spotlight-tool-tip">Lire un extrait</span>
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="spotlight-panel">
                            <div class="spotlight-body">
                                <div class="spotlight-main">
                                    <h2 class="spotlight-heading">Description</h2>
                                    @foreach($paragraphs as $paragraph)
                                        <p class="spotlight-text">{{ Str::limit($paragraph, 420) }}</p>
                                    @endforeach

                                    @if($review)
                                        <figure class="spotlight-review">
                                            <span class="spotlight-review-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($review->customer_name ?? $review->user?->name ?? 'L', 0, 1)) }}</span>
                                            <div>
                                                <figcaption class="spotlight-review-name">{{ $review->customer_name ?? $review->user?->name ?? 'Lecteur' }}</figcaption>
                                                <blockquote class="spotlight-review-text">{{ Str::limit($review->comment, 160) }}</blockquote>
                                            </div>
                                        </figure>
                                    @endif
                                </div>

                                <dl class="spotlight-details">
                                    <div>
                                        <dt>Édition</dt>
                                        <dd>{{ $book->publisher ?: 'NSOROMMA' }}@if($book->category)<br>Collection {{ $book->category->name }}@endif</dd>
                                    </div>
                                    <div>
                                        <dt>Langue</dt>
                                        <dd>{{ $book->language }}</dd>
                                    </div>
                                    @if($details->isNotEmpty())
                                        <div>
                                            <dt>Format</dt>
                                            <dd>{{ $details->first() }}</dd>
                                        </div>
                                    @endif
                                    <div>
                                        <dt>Disponible en</dt>
                                        <dd>
                                            @if($book->is_pdf_available)e-Book PDF · {{ $book->formatted_price_pdf }}<br>@endif
                                            @if($book->is_physical_available)Papier · {{ $book->formatted_price_physical }} <span class="spotlight-stock">({{ Str::lcfirst($book->stockLabel()) }})</span><br>@endif
                                            @if($book->is_audio_available)Audio · {{ $book->formatted_price_audio }}@endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </article>
                @endforeach
            </section>
        @else
            <div class="home-empty">
                <p class="home-empty-title">Les étagères se remplissent bientôt.</p>
                <p>Aucun livre n'est encore publié dans la boutique.</p>
            </div>
        @endif

        @if($shelves->isNotEmpty())
            <div class="collections">
                <div class="collections-head">
                    <h2 class="collections-title">Nos collections</h2>
                    <a href="{{ route('catalog.index') }}" class="collections-link">Tout le catalogue ({{ $booksCount }})</a>
                </div>

                @foreach($shelves as $index => $shelf)
                    <section class="shelf shelf-{{ $shelfTones[$index % count($shelfTones)] }}" aria-labelledby="shelf-title-{{ $index }}" data-shelf>
                        <div class="shelf-head">
                            <h3 class="shelf-title" id="shelf-title-{{ $index }}"><a href="{{ $shelf['url'] }}">{{ $shelf['title'] }}</a></h3>
                            <div class="shelf-meta">
                                <a href="{{ $shelf['url'] }}" class="shelf-count">{{ $shelf['count'] }} {{ Str::plural('livre', $shelf['count']) }}</a>
                                <button type="button" class="shelf-arrow" data-scroll="-1" aria-label="Livres précédents : {{ $shelf['title'] }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                                </button>
                                <button type="button" class="shelf-arrow" data-scroll="1" aria-label="Livres suivants : {{ $shelf['title'] }}">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
                                </button>
                            </div>
                        </div>

                        <div class="shelf-stage">
                            <ul class="shelf-row" data-shelf-row>
                                @foreach($shelf['books'] as $bookIndex => $book)
                                    <li class="shelf-book" style="--i: {{ $bookIndex }};">
                                        <a href="{{ route('catalog.show', $book->slug) }}" class="shelf-book-link">
                                            <img src="{{ $book->cover_image ? asset($book->cover_image) : asset('images/books/fallback-book.jpg') }}"
                                                 alt="{{ $book->title }}, {{ $book->author }}" loading="lazy"
                                                 onerror="this.onerror=null;this.src='{{ asset('images/books/fallback-book.jpg') }}';">
                                            <span class="shelf-book-caption" aria-hidden="true">
                                                <span class="shelf-book-title">{{ $book->title }}</span>
                                                <span class="shelf-book-author">{{ $book->author }}</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="shelf-board" aria-hidden="true"><span class="shelf-screw"></span><span class="shelf-screw"></span></div>
                        </div>
                    </section>
                @endforeach
            </div>
        @endif
    </div>
@endsection
