@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="ds-pagination-nav">
        <div class="ds-pagination-wrap">
            @if ($paginator->onFirstPage())
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">&#8249;</span>
                </span>
            @else
                <a class="ds-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('pagination.previous') }}">
                    <span aria-hidden="true">&#8249;</span>
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="ds-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">&#8250;</span>
                </a>
            @else
                <span class="ds-page-btn is-disabled" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                    <span aria-hidden="true">&#8250;</span>
                </span>
            @endif
        </div>
    </nav>
@endif
