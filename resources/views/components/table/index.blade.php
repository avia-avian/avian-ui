@props([
    'headers' => [],
    'hover' => true,
    'striped' => false,
    'size' => null,
    'paginator' => null,
    'empty' => null,
    'emptyText' => null,
    'emptyIcon' => 'fas fa-inbox',
    'columns' => null,
    'sortBy' => null,
    'sortDirection' => null,
    'sortParam' => 'sort',
    'directionParam' => 'direction',
    'livewire' => null,
    'stickyHeader' => false,
    'stickyFooter' => false,
    'stickyColumns' => 0,
    'stickyColumnsEnd' => 0,
    'maxHeight' => null,
])

@php
    $showEmpty = $empty !== false && ! $slot->hasActualContent();
    $emptyColumns = $columns ?? max(count($headers), 1);

    $stickyColumns = max(0, (int) $stickyColumns);
    $stickyColumnsEnd = max(0, (int) $stickyColumnsEnd);
    $sticky = $stickyHeader || $stickyFooter || $stickyColumns > 0 || $stickyColumnsEnd > 0;

    // A frozen header or footer sticks to the edge of the scroll box, so the
    // box needs a height to scroll in: without one, cap it at 70% of the screen.
    $maxHeight ??= $stickyHeader || $stickyFooter ? '70vh' : null;
    $maxHeight = is_int($maxHeight) || (is_string($maxHeight) && is_numeric($maxHeight)) ? $maxHeight.'px' : $maxHeight;
@endphp

<div
    class="aui-table-wrap"
    @if (filled($maxHeight)) style="max-height: {{ $maxHeight }}" @endif
    @if ($stickyColumns > 0 || $stickyColumnsEnd > 0) x-data="auiTable({{ Illuminate\Support\Js::from(['start' => $stickyColumns, 'end' => $stickyColumnsEnd]) }})" @endif
>
    <table {{ $attributes->class([
        'aui-table',
        'aui-table-hover' => $hover,
        'aui-table-striped' => $striped,
        'aui-table-'.$size => filled($size),
        'aui-table-sticky' => $sticky,
        'aui-table-sticky-header' => $stickyHeader,
        'aui-table-sticky-footer' => $stickyFooter,
    ]) }}>
        @if (filled($headers) || isset($head))
            <thead>
                @isset($head)
                    {{ $head }}
                @else
                    <tr>
                        @foreach ($headers as $header)
                            @if (is_array($header))
                                <x-avian-ui::table.heading
                                    :sort="$header['sort'] ?? null"
                                    :align="$header['align'] ?? null"
                                >{{ $header['label'] ?? '' }}</x-avian-ui::table.heading>
                            @else
                                <th>{{ $header }}</th>
                            @endif
                        @endforeach
                    </tr>
                @endisset
            </thead>
        @endif

        <tbody>
            @if ($showEmpty)
                <tr class="aui-table-empty">
                    <td colspan="{{ $emptyColumns }}">
                        @if ($empty instanceof \Illuminate\View\ComponentSlot)
                            {{ $empty }}
                        @else
                            <x-avian-ui::empty
                                :icon="$emptyIcon"
                                :title="$empty ?? __('avian-ui::messages.no_results')"
                                :text="$emptyText"
                            />
                        @endif
                    </td>
                </tr>
            @else
                {{ $slot }}
            @endif
        </tbody>

        @isset($foot)
            <tfoot>{{ $foot }}</tfoot>
        @endisset
    </table>
</div>

@if ($paginator !== null)
    <x-avian-ui::pagination :paginator="$paginator" :livewire="$livewire" />
@endif
