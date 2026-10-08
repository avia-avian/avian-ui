@php
    $props = [
        ['text', 'string|null', 'null', 'The tooltip text. Use a `content` slot instead for a little markup.'],
        ['placement', "'top'|'bottom'|'left'|'right'", "'top'", 'Preferred side. It flips to the opposite side when there is no room.'],
        ['delay', 'int', '150', 'Milliseconds of hover or focus before it shows.'],
        ['id', 'string|null', 'derived', 'Id of the tooltip element, referenced by aria-describedby.'],
    ];

    $examples = [
        [
            'title' => 'Label an icon-only button',
            'text' => 'The tooltip shows the label to sighted users. Still give the button its own accessible name, the tooltip only describes it.',
            'code' => <<<'BLADE'
                <x-avian::tooltip text="Edit order">
                    <x-avian::button variant="light" icon="fas fa-pen" icon-only label="Edit order" />
                </x-avian::tooltip>
                BLADE,
        ],
        [
            'title' => 'Explain a status in a table',
            'code' => <<<'BLADE'
                <x-avian::tooltip :text="'Paid on '.$order->paid_at->format('M j, Y')" placement="right">
                    <x-avian::badge variant="success" dot>Paid</x-avian::badge>
                </x-avian::tooltip>
                BLADE,
        ],
        [
            'title' => 'A little markup',
            'code' => <<<'BLADE'
                <x-avian::tooltip>
                    <i class="fas fa-circle-info" aria-label="Shortcut"></i>

                    <x-slot:content>Search with <strong>Ctrl + K</strong></x-slot:content>
                </x-avian::tooltip>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Tooltip', 'subtitle' => 'A short hint on hover and focus'])
<p class="aui-showcase-lead">
    A small dark label that appears next to an element on hover and on keyboard focus. Use it to name
    icon-only buttons or add a detail to a status. For anything clickable inside, use a popover.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap; gap: 12px">
        @foreach (['top', 'bottom', 'left', 'right'] as $placement)
            <x-avian::tooltip :text="'Tooltip on the '.$placement" :placement="$placement">
                <x-avian::button variant="light">{{ ucfirst($placement) }}</x-avian::button>
            </x-avian::tooltip>
        @endforeach

        <x-avian::tooltip text="Edit order">
            <x-avian::button variant="light" icon="fas fa-pen" icon-only label="Edit order" />
        </x-avian::tooltip>

        <x-avian::tooltip text="Paid on Mar 4, 2026" placement="right">
            <x-avian::badge variant="success" dot>Paid</x-avian::badge>
        </x-avian::tooltip>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The tooltip is <code>position: fixed</code>, so a scrolling table or a card never clips it.</li>
        <li>It describes the first focusable element in the slot through <code>aria-describedby</code>. With nothing focusable inside (a badge, an icon), the wrapper becomes focusable so keyboard users can reach it.</li>
        <li><kbd class="aui-kbd">Esc</kbd> hides it. On touch screens it shows on tap and hides on the next tap elsewhere.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
