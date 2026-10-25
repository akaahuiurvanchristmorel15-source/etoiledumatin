@extends('layouts.app')
@section('title', $article->title.' — Actualités NSOROMMA')
@section('meta_description', Str::limit(strip_tags($article->excerpt), 160))
@section('canonical', route('news.show', $article->slug))
@section('og_type', 'article')
@section('og_image', $article->coverUrl())
@section('twitter_card', 'summary_large_image')

@push('structured-data')
    @php
        $structuredData = array_filter([
            '@context' => 'https://schema.org',
            '@type' => $article->event_date ? 'Event' : 'NewsArticle',
            ($article->event_date ? 'name' : 'headline') => $article->title,
            'description' => $article->excerpt,
            'image' => $article->coverUrl(),
            'url' => route('news.show', $article->slug),
            'datePublished' => $article->event_date ? null : $article->published_at->toAtomString(),
            'startDate' => $article->event_date?->toAtomString(),
            'location' => $article->event_date && $article->location ? ['@type' => 'Place', 'name' => $article->location, 'address' => $article->location] : null,
            'organizer' => $article->event_date ? ['@type' => 'Organization', 'name' => 'NSOROMMA', 'url' => route('home')] : null,
            'publisher' => $article->event_date ? null : ['@type' => 'Organization', 'name' => 'NSOROMMA'],
        ]);
    @endphp
    <script type="application/ld+json">{!! json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/news.css') }}?v={{ filemtime(public_path('css/news.css')) }}">
@endpush

@section('content')
    <div class="news">
        <article class="news-article">
            <a href="{{ route('news.index') }}" class="news-back">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                Toutes les actualités
            </a>

            <header>
                <a href="{{ route('news.index', ['type' => $article->type->value]) }}" class="news-badge">{{ $article->type->label() }}</a>
                <h1 class="news-title">{{ $article->title }}</h1>
                <div class="news-article-meta">
                    <time datetime="{{ $article->published_at->toAtomString() }}">Publié le {{ $article->published_at->locale('fr')->translatedFormat('j F Y') }}</time>
                </div>
                <p class="news-article-lead">{{ $article->excerpt }}</p>
            </header>

            @if($article->cover_image)
                <figure class="news-article-cover">
                    <img src="{{ asset($article->cover_image) }}" alt="{{ $article->title }}">
                </figure>
            @endif

            @if($article->event_date || $article->location)
                <dl class="news-event-box">
                    @if($article->event_date)
                        <div>
                            <dt>Date</dt>
                            <dd><time datetime="{{ $article->event_date->toAtomString() }}">{{ Str::ucfirst($article->event_date->locale('fr')->translatedFormat('l j F Y')) }} à {{ $article->event_date->format('H\hi') }}</time></dd>
                        </div>
                    @endif
                    @if($article->location)
                        <div>
                            <dt>Lieu</dt>
                            <dd>{{ $article->location }}</dd>
                        </div>
                    @endif
                </dl>
            @endif

            <div class="news-article-content">
                @foreach(preg_split('/\R{2,}/', trim($article->content)) as $paragraph)
                    <p>{!! nl2br(e($paragraph)) !!}</p>
                @endforeach
            </div>

            @if($article->book?->is_active)
                <a href="{{ route('catalog.show', $article->book->slug) }}" class="news-book">
                    <img src="{{ $article->book->cover_image ? asset($article->book->cover_image) : asset('images/books/fallback-book.jpg') }}" alt="" loading="lazy">
                    <span>
                        <span class="news-book-label">Le livre</span>
                        <span class="news-book-title">{{ $article->book->title }}</span>
                        <span class="news-book-author">{{ $article->book->author }}</span>
                    </span>
                    <span class="news-book-cta">Voir le livre</span>
                </a>
            @endif
        </article>

        @if($related->isNotEmpty())
            <section class="news-related" aria-labelledby="news-related-title">
                <h2 id="news-related-title" class="news-related-title">À lire aussi</h2>
                <ul class="news-grid">
                    @foreach($related as $item)
                        <li>@include('pages.news._card', ['item' => $item])</li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endsection
