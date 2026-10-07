{{--
    Renders a Laravel paginator (simple or length-aware) with `aui-*` classes.

    Usage:
        <x-avian::pagination :paginator="$users" />

    Pass any `Illuminate\Contracts\Pagination\Paginator`. A length-aware
    paginator (the default from `paginate()`) also gets numbered page links
    and a "Showing X to Y of Z results" summary; a simple paginator
    (`simplePaginate()`) and a cursor paginator (`cursorPaginate()`) only get
    Previous/Next.

    Inside a Livewire component the links become buttons that call
    `gotoPage()` from Livewire's `WithPagination`, so paging never leaves the
    page or touches the URL. Pass `:livewire="false"` to force plain links, or
    `:livewire="true"` to force buttons.
--}}
@props([
    'paginator' => null,
    'onEachSide' => 1,
    'livewire' => null,
])

@php
    $hasPages = $paginator !== null && $paginator->hasPages();
    $isLengthAware = $paginator instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
    $isCursor = $paginator instanceof \Illuminate\Contracts\Pagination\CursorPaginator;

    $livewire ??= class_exists(\Livewire\Livewire::class) && \Livewire\Livewire::current() !== null;
@endphp

@if ($hasPages)
    <nav {{ $attributes->class(['aui-pagination']) }} role="navigation" aria-label="Pagination">
        @if ($isLengthAware)
            <p class="aui-pagination-summary">
                Showing
                <span class="aui-pagination-summary-strong">{{ $paginator->firstItem() }}</span>
                to
                <span class="aui-pagination-summary-strong">{{ $paginator->lastItem() }}</span>
                of
                <span class="aui-pagination-summary-strong">{{ $paginator->total() }}</span>
                results
            </p>
        @endif

        <div class="aui-pagination-links">
            @if ($paginator->onFirstPage())
                <span class="aui-pagination-link aui-pagination-link-disabled" aria-disabled="true">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </span>
            @elseif ($isCursor)
                <x-avian-ui::pagination.link :$paginator :cursor="$paginator->previousCursor()" :$livewire rel="prev" aria-label="Previous page">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </x-avian-ui::pagination.link>
            @else
                <x-avian-ui::pagination.link :$paginator :page="$paginator->currentPage() - 1" :$livewire rel="prev" aria-label="Previous page">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </x-avian-ui::pagination.link>
            @endif

            @if ($isLengthAware)
                @php
                    $current = $paginator->currentPage();
                    $last = $paginator->lastPage();
                    $start = max($current - $onEachSide, 1);
                    $end = min($current + $onEachSide, $last);
                @endphp

                @if ($start > 1)
                    <x-avian-ui::pagination.link :$paginator :page="1" :$livewire>1</x-avian-ui::pagination.link>

                    @if ($start > 2)
                        <span class="aui-pagination-ellipsis">&hellip;</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page === $current)
                        <span class="aui-pagination-link aui-pagination-link-active" aria-current="page">{{ $page }}</span>
                    @else
                        <x-avian-ui::pagination.link :$paginator :$page :$livewire>{{ $page }}</x-avian-ui::pagination.link>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="aui-pagination-ellipsis">&hellip;</span>
                    @endif

                    <x-avian-ui::pagination.link :$paginator :page="$last" :$livewire>{{ $last }}</x-avian-ui::pagination.link>
                @endif
            @endif

            @if ($paginator->hasMorePages() && $isCursor)
                <x-avian-ui::pagination.link :$paginator :cursor="$paginator->nextCursor()" :$livewire rel="next" aria-label="Next page">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </x-avian-ui::pagination.link>
            @elseif ($paginator->hasMorePages())
                <x-avian-ui::pagination.link :$paginator :page="$paginator->currentPage() + 1" :$livewire rel="next" aria-label="Next page">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </x-avian-ui::pagination.link>
            @else
                <span class="aui-pagination-link aui-pagination-link-disabled" aria-disabled="true">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
