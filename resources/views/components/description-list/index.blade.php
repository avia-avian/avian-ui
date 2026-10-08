{{--
    Label / value details, the block at the top of every "show" page:

        <x-avian::description-list>
            <x-avian::description-list.item label="Status">
                <x-avian::badge variant="success" dot>Paid</x-avian::badge>
            </x-avian::description-list.item>
            <x-avian::description-list.item label="Invoice" :value="$order->number" copyable />
            <x-avian::description-list.item label="Created" :value="$order->created_at" />
            <x-avian::description-list.item label="Notes" :value="$order->notes" full />
        </x-avian::description-list>

    Or straight from an array of label => value:

        <x-avian::description-list :items="['SKU' => $product->sku, 'Stock' => $product->stock]" />

    `columns` (1–3) collapses to one on phones; `inline` puts each label
    beside its value, for a narrow sidebar; `divided` rules off the rows.
--}}
@props([
    'items' => null,
    'columns' => 2,
    'inline' => false,
    'divided' => false,
])

@php
    $columns = max(1, min(3, (int) $columns));
@endphp

<dl {{ $attributes->class([
    'aui-dl',
    'aui-dl-cols-'.$columns,
    'aui-dl-inline' => $inline,
    'aui-dl-divided' => $divided,
]) }}>
    @if (is_iterable($items))
        @foreach ($items as $itemLabel => $itemValue)
            <x-avian-ui::description-list.item :label="$itemLabel" :value="$itemValue" />
        @endforeach
    @endif

    {{ $slot }}
</dl>
