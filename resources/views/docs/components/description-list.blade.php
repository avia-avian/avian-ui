@php
    $props = [
        ['items', 'iterable|null', 'null', 'label => value pairs, for simple lists. Rendered before the slot.'],
        ['columns', 'int', '2', '1 to 3 columns. Three drop to two on tablets; all drop to one on phones.'],
        ['inline', 'bool', 'false', 'Each label beside its value instead of above it, for narrow sidebars.'],
        ['divided', 'bool', 'false', 'A rule under every row.'],
        ['item: label', 'string', '—', 'The term.'],
        ['item: value', 'mixed', 'null', 'The value, if there is no slot. Dates are formatted, enums show label() or their value, booleans Yes / No.'],
        ['item: date-format', 'string', "'M j, Y g:i A'", 'PHP format for a date value.'],
        ['item: empty', 'string', "'—'", 'Shown when the value is blank.'],
        ['item: copyable', 'bool', 'false', 'Adds a copy button next to the value.'],
        ['item: copy', 'string|null', 'null', 'Text to copy, when it differs from what is shown.'],
        ['item: full', 'bool', 'false', 'Span the whole row (notes, addresses).'],
    ];

    $examples = [
        [
            'title' => 'An order\'s details',
            'code' => <<<'BLADE'
                <x-avian::card title="Order details">
                    <x-avian::description-list>
                        <x-avian::description-list.item label="Status">
                            <x-avian::badge :variant="$order->status->variant()" dot>{{ $order->status->label() }}</x-avian::badge>
                        </x-avian::description-list.item>
                        <x-avian::description-list.item label="Customer" :value="$order->customer->name" />
                        <x-avian::description-list.item label="Invoice no." :value="$order->number" copyable />
                        <x-avian::description-list.item label="Created" :value="$order->created_at" />
                        <x-avian::description-list.item label="Notes" :value="$order->notes" full />
                    </x-avian::description-list>
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'From an array, in a sidebar',
            'code' => <<<'BLADE'
                <x-avian::description-list inline divided :columns="1" :items="[
                    'SKU' => $product->sku,
                    'Stock' => $product->stock,
                    'Published' => $product->is_published,
                    'Updated' => $product->updated_at,
                ]" />
                BLADE,
        ],
        [
            'title' => 'A copy button on its own',
            'text' => 'The copy button is a component too, for API keys, links and ids anywhere on a page.',
            'code' => <<<'BLADE'
                <code>{{ $token->plain }}</code>
                <x-avian::copy-button :text="$token->plain" label="Copy token" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Description list', 'subtitle' => 'Label and value details for a record'])
<p class="aui-showcase-lead">
    The details block at the top of a "show" page: status, customer, dates, notes. Values can be plain
    text, dates, enums or any markup, and a copy button can sit next to ids and numbers.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 24px">
        <x-avian::card title="Order details">
            <x-avian::description-list>
                <x-avian::description-list.item label="Status">
                    <x-avian::badge variant="success" dot>Paid</x-avian::badge>
                </x-avian::description-list.item>
                <x-avian::description-list.item label="Customer" value="Rina Wijaya" />
                <x-avian::description-list.item label="Invoice no." value="INV-2026-00142" copyable />
                <x-avian::description-list.item label="Created" :value="now()->subDays(3)" />
                <x-avian::description-list.item label="Coupon" />
                <x-avian::description-list.item label="Total" value="Rp 1.280.000" />
                <x-avian::description-list.item label="Notes" value="Leave the parcel with the building security if nobody answers." full />
            </x-avian::description-list>
        </x-avian::card>

        <x-avian::card title="Product" style="max-width: 360px">
            <x-avian::description-list inline divided :columns="1" :items="['SKU' => 'AV-1042', 'Stock' => 18, 'Published' => true, 'Updated' => now()->subHours(5)]" />
        </x-avian::card>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Renders a real <code>&lt;dl&gt;</code>, so screen readers announce each label with its value.</li>
        <li>A blank value shows <code>empty</code> (a muted dash) instead of leaving a hole in the grid.</li>
        <li>The copy button uses the Clipboard API, with a fallback for plain-http hosts, and dispatches <code>aui-copied</code>. <code>&lt;x-avian::copy-button&gt;</code> takes <code>text</code>, <code>label</code> and <code>size</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
