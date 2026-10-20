@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <span>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}</span>

        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="is-disabled" aria-hidden="true">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Page précédente">‹</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="is-disabled">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="is-active" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Page suivante">›</a>
            @else
                <span class="is-disabled" aria-hidden="true">›</span>
            @endif
        </div>
    </nav>
@endif
