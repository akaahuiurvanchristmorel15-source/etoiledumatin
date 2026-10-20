@if ($paginator->hasPages())
    <nav class="site-pagination" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="site-page is-disabled" aria-hidden="true">←</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="site-page" rel="prev" aria-label="Page précédente">←</a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="site-page is-disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="site-page is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" class="site-page" aria-label="Page {{ $page }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="site-page" rel="next" aria-label="Page suivante">→</a>
        @else
            <span class="site-page is-disabled" aria-hidden="true">→</span>
        @endif
    </nav>
@endif
