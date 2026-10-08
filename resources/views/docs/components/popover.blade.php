@php
    $props = [
        ['trigger', 'slot', '—', 'What opens the popover on click, usually a button.'],
        ['title', 'string|null', 'null', 'Heading with a close button. Also names the panel for screen readers.'],
        ['footer', 'slot', '—', 'Actions under the content.'],
        ['placement', "'top'|'bottom'|'left'|'right'", "'bottom'", 'Preferred side. It flips when there is no room.'],
        ['width', 'string|null', 'null', 'CSS width of the panel, e.g. "280px". It never grows past the screen.'],
        ['open', 'bool', 'false', 'Render it open.'],
        ['id', 'string|null', 'derived', 'Id of the panel, referenced by the trigger\'s aria-controls.'],
    ];

    $examples = [
        [
            'title' => 'Filters next to a table',
            'code' => <<<'BLADE'
                <x-avian::popover title="Filters" width="280px">
                    <x-slot:trigger>
                        <x-avian::button variant="light" icon="fas fa-filter">Filters</x-avian::button>
                    </x-slot:trigger>

                    <x-avian::select name="status" label="Status" :options="$statuses" wire:model.live="status" />

                    <x-slot:footer>
                        <x-avian::button size="sm" variant="light" wire:click="resetFilters">Reset</x-avian::button>
                        <x-avian::button size="sm" x-on:click="hide()">Done</x-avian::button>
                    </x-slot:footer>
                </x-avian::popover>
                BLADE,
        ],
        [
            'title' => 'Details on a name',
            'code' => <<<'BLADE'
                <x-avian::popover placement="right">
                    <x-slot:trigger>
                        <button type="button" class="link">{{ $customer->name }}</button>
                    </x-slot:trigger>

                    <strong>{{ $customer->name }}</strong><br>
                    {{ $customer->email }}<br>
                    {{ $customer->orders_count }} orders
                </x-avian::popover>
                BLADE,
        ],
        [
            'title' => 'Reacting to it',
            'text' => 'The popover dispatches aui-popover-open and aui-popover-close, for loading content only when it opens.',
            'code' => <<<'BLADE'
                <x-avian::popover x-on:aui-popover-open="$wire.loadHistory()">
                    ...
                </x-avian::popover>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Popover', 'subtitle' => 'A floating panel opened by a click'])
<p class="aui-showcase-lead">
    A small panel anchored to its trigger, for content that is too rich for a tooltip but too small
    for a modal: quick filters, a short form, a contact card.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap; gap: 12px">
        <x-avian::popover title="Filters" width="280px">
            <x-slot:trigger>
                <x-avian::button variant="light" icon="fas fa-filter">Filters</x-avian::button>
            </x-slot:trigger>

            <x-avian::select name="popover_status" label="Status" :options="['paid' => 'Paid', 'pending' => 'Pending', 'cancelled' => 'Cancelled']" placeholder="Any status" />

            <x-slot:footer>
                <x-avian::button size="sm" variant="light" x-on:click="hide()">Cancel</x-avian::button>
                <x-avian::button size="sm" x-on:click="hide()">Apply</x-avian::button>
            </x-slot:footer>
        </x-avian::popover>

        <x-avian::popover placement="right">
            <x-slot:trigger>
                <x-avian::button variant="light" icon="fas fa-user">Customer</x-avian::button>
            </x-slot:trigger>

            <strong>Rina Wijaya</strong><br>
            rina@example.com<br>
            12 orders since 2024
        </x-avian::popover>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>A click outside or <kbd class="aui-kbd">Esc</kbd> closes it; Esc also returns focus to the trigger. Call <code>hide()</code> from anything inside.</li>
        <li>Opened with the keyboard, focus moves to the first control in the panel. The trigger gets <code>aria-expanded</code> and <code>aria-controls</code>.</li>
        <li>The panel is <code>position: fixed</code>, so it is not clipped by a scrolling container, and it follows its trigger as the page scrolls.</li>
        <li>Use a dropdown for a list of actions and a modal for a task that needs the user's full attention.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
