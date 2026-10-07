{{--
    A table row that can expand to reveal a hidden detail row underneath.

        <x-avian::table :headers="['', 'Order', 'Customer', 'Total']">
            @foreach ($orders as $order)
                <x-avian::table.row wire:key="order-{{ $order->id }}">
                    <td>{{ $order->number }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>{{ $order->total }}</td>

                    <x-slot:details>
                        @include('orders.partials.lines', ['order' => $order])
                    </x-slot:details>
                </x-avian::table.row>
            @endforeach
        </x-avian::table>

    The row adds its own toggle cell, so give the table an empty heading for
    it — first by default, or last with `toggle="end"`. A row without a
    `details` slot still renders an empty toggle cell, so plain and
    expandable rows can share a table and keep their columns lined up.

    The detail cell spans every column: the parent table's `headers` (or
    `columns`) are counted, or pass `colspan` yourself.

    `clickable` also toggles the row when the row itself is clicked; clicks
    on links, buttons and form controls inside it are left alone.

    Under Livewire, give the row a `wire:key`. The open state lives in the
    browser and survives re-renders.
--}}
@aware([
    'headers' => [],
    'columns' => null,
])

@props([
    'expanded' => false,
    'toggle' => 'start',
    'clickable' => false,
    'colspan' => null,
    'label' => null,
])

@php
    $hasDetails = isset($details) && $details->hasActualContent();
    $toggle = $toggle === 'end' ? 'end' : 'start';

    // A colspan past the last column is harmless, so 100 covers a table
    // whose column count cannot be worked out.
    $span = $colspan ?? $columns ?? (count($headers) ?: 100);

    // A stable id ties the button to the detail row with aria-controls. A
    // random one would change on every Livewire render, while the detail row
    // keeps its first id (its own attributes are left alone, see below).
    $key = $attributes->get('id') ?? $attributes->get('wire:key');
    $detailsId = filled($key) ? 'aui-row-'.\Illuminate\Support\Str::slug((string) $key).'-details' : null;

    $label ??= __('avian-ui::messages.details');
@endphp

@if (! $hasDetails)
    <tr {{ $attributes }}>
        @if ($toggle === 'start')
            <td class="aui-table-toggle-cell"></td>
        @endif

        {{ $slot }}

        @if ($toggle === 'end')
            <td class="aui-table-toggle-cell"></td>
        @endif
    </tr>
@else
    <tr
        x-data="auiTableRow({ expanded: @js((bool) $expanded) })"
        @if ($clickable) x-on:click="clickRow($event)" @endif
        x-bind:class="{ 'is-expanded': open }"
        {{ $attributes->class([
            'aui-table-row-expandable',
            'aui-table-row-clickable' => $clickable,
            'is-expanded' => $expanded,
        ]) }}
    >
        @if ($toggle === 'end')
            {{ $slot }}
        @endif

        <td @class(['aui-table-toggle-cell', 'aui-table-align-right' => $toggle === 'end'])>
            <button
                type="button"
                class="aui-table-toggle"
                x-on:click.stop="toggle()"
                aria-expanded="{{ $expanded ? 'true' : 'false' }}"
                x-bind:aria-expanded="open.toString()"
                aria-label="{{ $label }}"
                @if ($detailsId) aria-controls="{{ $detailsId }}" @endif
            >
                <i class="fas fa-chevron-right" aria-hidden="true"></i>
            </button>
        </td>

        @if ($toggle === 'start')
            {{ $slot }}
        @endif
    </tr>

    {{-- Shown and hidden by the row above. wire:ignore.self stops a Livewire
         re-render from putting the server's `hidden` back on an open row,
         while the content inside still updates. --}}
    <tr
        {{ $details->attributes->class(['aui-table-details', 'aui-table-details-indented' => $toggle === 'start']) }}
        @if ($detailsId) id="{{ $detailsId }}" @endif
        @if (! $expanded) hidden @endif
        wire:ignore.self
    >
        <td colspan="{{ $span }}">
            {{ $details }}
        </td>
    </tr>
@endif
