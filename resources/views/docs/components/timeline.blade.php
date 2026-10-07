@php
    $props = [
        ['size', "'sm'|null", 'null', 'Timeline only: tighter spacing for a sidebar or a card.'],
        ['title', 'string|null', 'null', 'Item: the event heading.'],
        ['time', 'string|DateTimeInterface|null', 'null', 'Item: when it happened. A date is formatted and written into <time datetime>.'],
        ['time-format', 'string', "'M j, Y g:i A'", 'Item: PHP date format used for a date time.'],
        ['relative', 'bool', 'false', 'Item: shows a date as "3 hours ago", with the exact time on hover.'],
        ['icon', 'string|null', 'null', 'Item: icon class shown in a round marker instead of a dot.'],
        ['variant', 'string|null', 'null', 'Item: marker colour — success, warning, danger, info or neutral. Omit for the theme\'s primary colour.'],
    ];

    $examples = [
        [
            'title' => 'Order history',
            'code' => <<<'BLADE'
                <x-avian::timeline>
                    @foreach ($order->events()->latest()->get() as $event)
                        <x-avian::timeline.item :title="$event->title" :time="$event->created_at" relative>
                            {{ $event->note }}
                        </x-avian::timeline.item>
                    @endforeach
                </x-avian::timeline>
                BLADE,
        ],
        [
            'title' => 'Icons and colours by event type',
            'code' => <<<'BLADE'
                <x-avian::timeline.item
                    :title="$event->title"
                    :time="$event->created_at"
                    :icon="match ($event->type) { 'paid' => 'fas fa-check', 'refund' => 'fas fa-rotate-left', default => 'fas fa-circle-info' }"
                    :variant="match ($event->type) { 'paid' => 'success', 'refund' => 'danger', default => null }"
                />
                BLADE,
        ],
        [
            'title' => 'Compact, in a card',
            'code' => <<<'BLADE'
                <x-avian::card title="Recent activity">
                    <x-avian::timeline size="sm">
                        <x-avian::timeline.item title="Invoice sent" time="Today" />
                        <x-avian::timeline.item title="Quote approved" time="Yesterday" variant="success" />
                    </x-avian::timeline>
                </x-avian::card>
                BLADE,
        ],
    ];

    $now = now();
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Timeline', 'subtitle' => 'Events in order'])
<p class="aui-showcase-lead">
    A vertical list of what happened and when: an order's history, an audit log, a project's milestones.
    Each item has a title, a time and optional details in its slot; a marker colour or icon tells event types apart.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::timeline>
            <x-avian::timeline.item title="Order delivered" :time="$now->copy()->subMinutes(20)" relative icon="fas fa-box-open" variant="success">
                Signed for by J. Rivera at the front desk.
            </x-avian::timeline.item>
            <x-avian::timeline.item title="Out for delivery" :time="$now->copy()->subHours(5)" relative icon="fas fa-truck" />
            <x-avian::timeline.item title="Delivery delayed" :time="$now->copy()->subDay()" relative icon="fas fa-triangle-exclamation" variant="warning">
                Weather at the regional hub pushed the route back a day.
            </x-avian::timeline.item>
            <x-avian::timeline.item title="Payment received" :time="$now->copy()->subDays(3)" icon="fas fa-credit-card" variant="info" />
            <x-avian::timeline.item title="Order placed" :time="$now->copy()->subDays(3)->subHour()" icon="fas fa-cart-shopping" variant="neutral" />
        </x-avian::timeline>

        <x-avian::timeline size="sm">
            <x-avian::timeline.item title="Release 2.4 shipped" time="Oct 2" variant="success" />
            <x-avian::timeline.item title="Code freeze" time="Sep 28" />
            <x-avian::timeline.item title="Security review failed" time="Sep 21" variant="danger">
                Two findings in the export endpoint, fixed in the next sprint.
            </x-avian::timeline.item>
            <x-avian::timeline.item title="Kick-off" time="Sep 1" variant="neutral" />
        </x-avian::timeline>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>It renders an ordered list (<code>&lt;ol&gt;</code>), so screen readers announce how many events there are.</li>
        <li>Items appear in the order you write them — sort the query newest or oldest first as the page needs.</li>
        <li>A date <code>time</code> always fills the <code>&lt;time datetime&gt;</code> attribute; plain strings are printed as given.</li>
        <li>Dots and icon markers can be mixed in one timeline; the connecting line stays centred under both.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
