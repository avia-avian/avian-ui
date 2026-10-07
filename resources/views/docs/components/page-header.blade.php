@php
    $props = [
        ['title', 'string|null', 'null', 'Page title, rendered as the page\'s <h1>.'],
        ['subtitle', 'string|null', 'null', 'Muted line under the title.'],
        ['actions (slot)', 'slot', '—', 'Buttons shown on the right: the page\'s main actions.'],
        ['default slot', 'slot', '—', 'Extra content under the subtitle, e.g. breadcrumbs or badges.'],
    ];

    $examples = [
        [
            'title' => 'Title with actions',
            'code' => <<<'BLADE'
                <x-avian::page-header title="Records" subtitle="Everything your team has created.">
                    <x-slot:actions>
                        <x-avian::button variant="light" icon="fas fa-download">Export</x-avian::button>
                        <x-avian::button href="{{ route('records.create') }}" icon="fas fa-plus">New record</x-avian::button>
                    </x-slot:actions>
                </x-avian::page-header>
                BLADE,
        ],
        [
            'title' => 'Detail page with status',
            'text' => 'Anything in the default slot is placed under the subtitle.',
            'code' => <<<'BLADE'
                <x-avian::page-header :title="$order->number" subtitle="Placed {{ $order->created_at->diffForHumans() }}">
                    <x-avian::badge :variant="$order->status->variant()" dot>{{ $order->status->label() }}</x-avian::badge>

                    <x-slot:actions>
                        <x-avian::button variant="light" href="{{ route('orders.index') }}" icon="fas fa-arrow-left">Back</x-avian::button>
                    </x-slot:actions>
                </x-avian::page-header>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Page header', 'subtitle' => 'Title and main actions at the top of a page'])
<p class="aui-showcase-lead">
    Put one at the top of each page: it gives the page its <code>&lt;h1&gt;</code>, a short description
    and a place for the page-level actions (New, Export…) on the right.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 28px">
        <x-avian::page-header title="Records" subtitle="Everything your team has created.">
            <x-slot:actions>
                <x-avian::button variant="light" icon="fas fa-download">Export</x-avian::button>
                <x-avian::button icon="fas fa-plus">New record</x-avian::button>
            </x-slot:actions>
        </x-avian::page-header>

        <x-avian::page-header title="ORD-2026-0042" subtitle="Placed 2 hours ago">
            <x-avian::badge variant="success" dot style="margin-top: 8px">Paid</x-avian::badge>

            <x-slot:actions>
                <x-avian::button variant="light" icon="fas fa-arrow-left">Back</x-avian::button>
            </x-slot:actions>
        </x-avian::page-header>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The title is an <code>&lt;h1&gt;</code>, so use one page header per page and <code>h2</code> (card titles) below it.</li>
        <li>The title block and the actions sit on one row; below 768px the actions move under the title.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
