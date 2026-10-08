@php
    $props = [
        ['steps', 'array', '[]', 'Titles, or arrays of title, description, icon, href and status.'],
        ['current', 'int', '1', 'The current step, from 1. Steps before it are complete, after it upcoming.'],
        ['vertical', 'bool', 'false', 'Stack the steps in a column, with room for descriptions.'],
        ['size', "'sm'|null", 'null', 'Smaller markers.'],
        ['label', 'string|null', "'Progress'", 'Accessible name of the list.'],
    ];

    $examples = [
        [
            'title' => 'Checkout progress',
            'code' => <<<'BLADE'
                <x-avian::stepper :steps="['Cart', 'Shipping', 'Payment', 'Review']" :current="2" />
                BLADE,
        ],
        [
            'title' => 'Order history, vertical',
            'text' => 'A step\'s own status wins over `current`, which is how to show a failed step. Only complete steps follow their href.',
            'code' => <<<'BLADE'
                <x-avian::stepper vertical :current="3" :steps="[
                    ['title' => 'Ordered', 'description' => 'Mar 4, 10:30'],
                    ['title' => 'Packed', 'description' => 'Mar 4, 15:02', 'href' => route('orders.packing', $order)],
                    ['title' => 'Payment failed', 'description' => 'Card declined', 'status' => 'error'],
                    ['title' => 'Shipped'],
                ]" />
                BLADE,
        ],
        [
            'title' => 'From an enum',
            'code' => <<<'BLADE'
                <x-avian::stepper
                    :steps="collect(OrderStatus::cases())->map->label()"
                    :current="$order->status->position()"
                />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Stepper', 'subtitle' => 'Progress through a multi-step process'])
<p class="aui-showcase-lead">
    Shows which step of a process the user is on: a checkout, an onboarding flow, an order's journey.
    It only displays progress. To split a form into steps, use the <strong>wizard</strong>.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 32px">
        <x-avian::stepper :steps="['Cart', 'Shipping', 'Payment', 'Review']" :current="2" />

        <x-avian::stepper size="sm" :current="3" :steps="[
            ['title' => 'Account', 'description' => 'Email & password'],
            ['title' => 'Profile', 'description' => 'Name & photo'],
            ['title' => 'Team', 'description' => 'Invite people'],
            ['title' => 'Done'],
        ]" />

        <x-avian::stepper vertical :current="3" :steps="[
            ['title' => 'Ordered', 'description' => 'Mar 4, 10:30'],
            ['title' => 'Packed', 'description' => 'Mar 4, 15:02'],
            ['title' => 'Payment failed', 'description' => 'Card declined', 'status' => 'error'],
            ['title' => 'Shipped'],
        ]" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Renders an ordered list; the current step has <code>aria-current="step"</code>.</li>
        <li>A step's <code>status</code> is <code>complete</code>, <code>current</code>, <code>upcoming</code> or <code>error</code>.</li>
        <li>On phones a horizontal stepper keeps every marker but only the current step's title, so it fits any number of steps.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
