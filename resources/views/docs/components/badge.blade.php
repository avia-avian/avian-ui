@php
    $props = [
        ['variant', 'string', "'neutral'", 'neutral, primary, success, warning, danger, info, dark, purple, indigo, teal, orange or pink.'],
        ['size', "'sm'|null", 'null', 'Smaller badge for dense tables and lists.'],
        ['dot', 'bool', 'false', 'Adds a small coloured dot before the text — the usual look for a status.'],
        ['icon', 'string|null', 'null', 'Icon class shown before the text.'],
        ['uppercase', 'bool', 'false', 'Uppercase, letter-spaced text for short labels.'],
    ];

    $examples = [
        [
            'title' => 'Status badges',
            'code' => <<<'BLADE'
                <x-avian::badge variant="success" dot>Complete</x-avian::badge>
                <x-avian::badge variant="warning" dot>Ongoing</x-avian::badge>
                <x-avian::badge variant="danger" dot>Not started</x-avian::badge>
                BLADE,
        ],
        [
            'title' => 'Mapping a model status to a variant',
            'text' => 'Keep the status → colour mapping in one place (an enum method or a match) instead of repeating it in every view.',
            'code' => <<<'BLADE'
                // app/Enums/OrderStatus.php
                public function variant(): string
                {
                    return match ($this) {
                        self::Paid => 'success',
                        self::Pending => 'warning',
                        self::Cancelled => 'danger',
                    };
                }

                {{-- View --}}
                <x-avian::badge :variant="$order->status->variant()" dot>
                    {{ $order->status->label() }}
                </x-avian::badge>
                BLADE,
        ],
        [
            'title' => 'Icons, sizes and labels',
            'code' => <<<'BLADE'
                <x-avian::badge variant="info" icon="fas fa-bolt">New</x-avian::badge>
                <x-avian::badge variant="primary" size="sm">v2.1</x-avian::badge>
                <x-avian::badge variant="info" uppercase>Beta</x-avian::badge>
                BLADE,
        ],
        [
            'title' => 'Counter next to a label',
            'code' => <<<'BLADE'
                <x-avian::button variant="light">
                    Inbox <x-avian::badge variant="danger" size="sm">{{ $unread }}</x-avian::badge>
                </x-avian::button>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Badge', 'subtitle' => 'Small coloured labels for status and counts'])
<p class="aui-showcase-lead">
    A pill-shaped label for statuses (paid, pending), categories and counters. Badges are only for
    display — use a button when it should be clickable.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <div class="aui-row" style="flex-wrap: wrap">
            @foreach (['neutral', 'primary', 'success', 'warning', 'danger', 'info', 'dark', 'purple', 'indigo', 'teal', 'orange', 'pink'] as $variant)
                <x-avian::badge :variant="$variant">{{ ucfirst($variant) }}</x-avian::badge>
            @endforeach
        </div>
        <div class="aui-row" style="flex-wrap: wrap">
            <x-avian::badge variant="success" dot>Complete</x-avian::badge>
            <x-avian::badge variant="warning" dot>Ongoing</x-avian::badge>
            <x-avian::badge variant="danger" dot>Not started</x-avian::badge>
            <x-avian::badge variant="info" icon="fas fa-bolt">New</x-avian::badge>
            <x-avian::badge variant="primary" size="sm">Small</x-avian::badge>
            <x-avian::badge variant="info" uppercase>Uppercase</x-avian::badge>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The slot is the badge text; it is escaped like any Blade output.</li>
        <li>Colours come from the active theme, so badges follow <code>data-theme</code> like everything else.</li>
        <li>Use <code>dot</code> for states and plain badges for categories, so the two read differently in the same table.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
