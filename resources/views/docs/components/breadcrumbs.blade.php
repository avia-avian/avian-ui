@php
    $props = [
        ['items', 'array|null', 'null', 'label => url pairs, or a list of [label, href, icon] arrays. The last one is the current page. Leave it out to write items in the slot.'],
        ['navigate', 'bool', 'false', 'Adds wire:navigate to every generated link.'],
        ['breadcrumbs.item: href', 'string|null', 'null', 'Link target. Without it, the item is the current page.'],
        ['breadcrumbs.item: icon', 'string|null', 'null', 'Icon class shown before the label.'],
    ];

    $examples = [
        [
            'title' => 'Above the page header',
            'code' => <<<'BLADE'
                <x-avian::breadcrumbs navigate :items="[
                    'Dashboard' => route('dashboard'),
                    'Orders' => route('orders.index'),
                    $order->number => null,
                ]" />

                <x-avian::page-header :title="$order->number" />
                BLADE,
        ],
        [
            'title' => 'With icons',
            'text' => 'Use a list of arrays when an item needs an icon or two items share a label.',
            'code' => <<<'BLADE'
                <x-avian::breadcrumbs :items="[
                    ['label' => 'Home', 'href' => route('home'), 'icon' => 'fas fa-house'],
                    ['label' => 'Settings', 'href' => route('settings')],
                    ['label' => 'Users'],
                ]" />
                BLADE,
        ],
        [
            'title' => 'Written by hand',
            'code' => <<<'BLADE'
                <x-avian::breadcrumbs>
                    <x-avian::breadcrumbs.item href="/" icon="fas fa-house">Home</x-avian::breadcrumbs.item>
                    <x-avian::breadcrumbs.item>Profile</x-avian::breadcrumbs.item>
                </x-avian::breadcrumbs>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Breadcrumbs', 'subtitle' => 'Where this page sits'])
<p class="aui-showcase-lead">
    A trail of links back up the page hierarchy, placed above the page header on detail and
    nested pages.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 24px">
        <x-avian::breadcrumbs :items="['Dashboard' => '#', 'Sales' => '#', 'Orders' => '#', 'ORD-2026-0042' => null]" />

        <x-avian::breadcrumbs :items="[
            ['label' => 'Home', 'href' => '#', 'icon' => 'fas fa-house'],
            ['label' => 'Settings', 'href' => '#', 'icon' => 'fas fa-gear'],
            ['label' => 'Users'],
        ]" />

        <div>
            <x-avian::breadcrumbs :items="['Inventory' => '#', 'Warehouses' => '#', 'Surabaya DC' => null]" />
            <x-avian::page-header title="Surabaya DC" subtitle="Distribution centre · 12 racks" />
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Rendered as <code>&lt;nav aria-label="Breadcrumb"&gt;</code> with an ordered list; the last item carries <code>aria-current="page"</code> and is never a link.</li>
        <li>Long trails wrap onto a second line instead of overflowing.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
