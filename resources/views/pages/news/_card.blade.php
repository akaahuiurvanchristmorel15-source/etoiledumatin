<a href="{{ route('news.show', $item->slug) }}" class="news-card">
    <span class="news-card-media">
        <img src="{{ $item->coverUrl() }}" alt="" loading="lazy"
             data-fallback="{{ asset('images/brand/nsoromma-logo.png') }}">
        <span class="news-badge">{{ $item->type->label() }}</span>
    </span>
    <span class="news-card-body">
        <time class="news-card-date" datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->locale('fr')->translatedFormat('j F Y') }}</time>
        <span class="news-card-title">{{ $item->title }}</span>
        <span class="news-card-excerpt">{{ Str::limit($item->excerpt, 160) }}</span>
        @if($item->event_date || $item->location)
            <span class="news-card-event">
                @if($item->event_date)
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                        {{ $item->event_date->locale('fr')->translatedFormat('j M Y · H\hi') }}
                    </span>
                @endif
                @if($item->location)
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
                        {{ $item->location }}
                    </span>
                @endif
            </span>
        @endif
    </span>
</a>
