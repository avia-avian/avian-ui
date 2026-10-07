@php
    $props = [
        ['multiple', 'bool', 'false', 'Let several items stay open at once. By default opening one closes the others.'],
        ['flush', 'bool', 'false', 'No outer border or padding, for use inside a card.'],
        ['accordion.item: title', 'string|slot', 'null', 'Header text; use <x-slot:title> for custom markup.'],
        ['accordion.item: subtitle', 'string|null', 'null', 'Muted line under the title.'],
        ['accordion.item: icon', 'string|null', 'null', 'Icon class shown before the title.'],
        ['accordion.item: open', 'bool', 'false', 'Start expanded.'],
        ['accordion.item: name', 'string|null', 'null', 'Key for opening it from outside with expand(name) / collapse(name).'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::accordion>
                    <x-avian::accordion.item title="Shipping address" open>...</x-avian::accordion.item>
                    <x-avian::accordion.item title="Billing address">...</x-avian::accordion.item>
                </x-avian::accordion>
                BLADE,
        ],
        [
            'title' => 'Several open, with icons and subtitles',
            'code' => <<<'BLADE'
                <x-avian::accordion multiple>
                    <x-avian::accordion.item title="Payment" subtitle="Transfer · due in 30 days" icon="fas fa-credit-card" open>
                        ...
                    </x-avian::accordion.item>
                    <x-avian::accordion.item title="Notes" icon="fas fa-note-sticky">...</x-avian::accordion.item>
                </x-avian::accordion>
                BLADE,
        ],
        [
            'title' => 'Inside a card',
            'code' => <<<'BLADE'
                <x-avian::card title="FAQ">
                    <x-avian::accordion flush>
                        <x-avian::accordion.item title="How do I reset my password?">...</x-avian::accordion.item>
                    </x-avian::accordion>
                </x-avian::card>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Accordion', 'subtitle' => 'Collapsible sections'])
<p class="aui-showcase-lead">
    Stacks of sections that expand and collapse — long forms split into steps, order details, FAQs.
</p>

<div class="aui-showcase-demo">
    <div class="aui-grid">
        <x-avian::accordion>
            <x-avian::accordion.item title="Shipping address" subtitle="Jl. Raya Darmo 12, Surabaya" icon="fas fa-truck" open>
                Delivered by the Surabaya DC fleet, usually within two working days.
            </x-avian::accordion.item>
            <x-avian::accordion.item title="Billing address" icon="fas fa-file-invoice">
                Same as the shipping address.
            </x-avian::accordion.item>
            <x-avian::accordion.item title="Notes" icon="fas fa-note-sticky">
                Call the store manager before unloading.
            </x-avian::accordion.item>
        </x-avian::accordion>

        <x-avian::card title="FAQ (flush, multiple)">
            <x-avian::accordion flush multiple>
                <x-avian::accordion.item title="How do I reset my password?" open>
                    Use "Forgot password" on the sign-in page; the link expires after an hour.
                </x-avian::accordion.item>
                <x-avian::accordion.item title="Who approves purchase orders?">
                    Your department head, then finance for orders above the limit.
                </x-avian::accordion.item>
            </x-avian::accordion>
        </x-avian::card>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Each header is a button with <code>aria-expanded</code> / <code>aria-controls</code>, so it works with the keyboard and screen readers.</li>
        <li>Closed items are hidden inline on first paint, so nothing flashes open before Alpine starts.</li>
        <li>The open state lives in Alpine and survives Livewire re-renders.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
