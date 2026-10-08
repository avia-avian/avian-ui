{{--
    A table-shaped loading placeholder, sized like <x-avian::table>:

        public function placeholder()
        {
            return view('components.orders-placeholder'); // <x-avian::skeleton.table :rows="8" :columns="5" />
        }

    It is announced to screen readers as a busy status with `label` as text.
--}}
@props([
    'rows' => 5,
    'columns' => 4,
    'header' => true,
    'label' => null,
    'animate' => true,
])

@php
    $rows = max(1, (int) $rows);
    $columns = max(1, (int) $columns);

    // Varying widths so the placeholder reads as data, not a grid of bars.
    $widths = ['70%', '45%', '85%', '55%', '35%', '65%'];
@endphp

<div {{ $attributes->class(['aui-table-wrap', 'aui-skeleton-table']) }} role="status" aria-busy="true">
    <span class="aui-sr-only">{{ $label ?? __('avian-ui::messages.loading') }}</span>

    <table class="aui-table" aria-hidden="true">
        @if ($header)
            <thead>
                <tr>
                    @for ($column = 0; $column < $columns; $column++)
                        <th><span @class(['aui-skeleton', 'aui-skeleton-text', 'aui-skeleton-static' => ! $animate]) style="width: {{ $column === 0 ? '50%' : '40%' }}"></span></th>
                    @endfor
                </tr>
            </thead>
        @endif

        <tbody>
            @for ($row = 0; $row < $rows; $row++)
                <tr>
                    @for ($column = 0; $column < $columns; $column++)
                        <td><span @class(['aui-skeleton', 'aui-skeleton-text', 'aui-skeleton-static' => ! $animate]) style="width: {{ $widths[($row + $column * 2) % count($widths)] }}"></span></td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</div>
