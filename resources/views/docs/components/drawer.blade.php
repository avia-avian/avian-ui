@php
    $props = [
        ['name', 'string|null', 'null', 'Name used to open and close it — the same events as the modal.'],
        ['title', 'string|null', 'null', 'Heading of the panel.'],
        ['subtitle', 'string|null', 'null', 'Muted line under the title.'],
        ['position', "'right'|'left'", "'right'", 'Side the panel slides in from.'],
        ['size', "'sm'|'md'|'lg'|'xl'", "'md'", 'Panel width: 320, 420, 640 or 880px. Full width below 640px.'],
        ['open', 'bool', 'false', 'Render it already open.'],
        ['close-on-escape', 'bool', 'true', 'Close on Esc.'],
        ['close-on-overlay', 'bool', 'true', 'Close when the backdrop is clicked.'],
        ['closeable', 'bool', 'true', 'Show the × button in the header.'],
        ['header / footer (slots)', 'slot', '—', 'Replace the header; add a sticky footer for actions.'],
    ];

    $examples = [
        [
            'title' => 'Filters panel',
            'code' => <<<'BLADE'
                <x-avian::button variant="light" icon="fas fa-filter" modal="order-filters">Filters</x-avian::button>

                <x-avian::drawer name="order-filters" title="Filters">
                    <x-avian::select name="status" label="Status" :options="$statuses" wire:model="status" />

                    <x-slot:footer>
                        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
                        <x-avian::button wire:click="applyFilters" x-on:click="hide()">Apply</x-avian::button>
                    </x-slot:footer>
                </x-avian::drawer>
                BLADE,
        ],
        [
            'title' => 'Open it from anywhere',
            'text' => 'A drawer listens to the modal events, so every modal trigger works.',
            'code' => <<<'BLADE'
                $this->dispatch('aui-modal-open', name: 'order-filters');   // Livewire
                window.AvianUI.openDrawer('order-filters');                // JS
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Drawer', 'subtitle' => 'Side panel over the page'])
<p class="aui-showcase-lead">
    A panel that slides in from the edge of the screen: filters, a quick-edit form or a record's
    details, while the list stays visible behind it.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap">
        <x-avian::button variant="light" icon="fas fa-filter" modal="doc-drawer-filters">Filters (right)</x-avian::button>
        <x-avian::button variant="light" icon="fas fa-bars" modal="doc-drawer-left">Menu (left, sm)</x-avian::button>
    </div>
</div>

<x-avian::drawer name="doc-drawer-filters" title="Filters" subtitle="Narrow down the order list">
    <div class="aui-stack">
        <x-avian::select name="drawer_status" label="Status" placeholder="Any status" :options="['open' => 'Open', 'paid' => 'Paid', 'shipped' => 'Shipped']" />
        <x-avian::datepicker name="drawer_period" label="Period" mode="range" placeholder="Pick a range" />
        <x-avian::multi-select name="drawer_regions" label="Regions" :options="['jatim' => 'Jawa Timur', 'jateng' => 'Jawa Tengah', 'jabar' => 'Jawa Barat']" />
    </div>

    <x-slot:footer>
        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
        <x-avian::button icon="fas fa-check" x-on:click="hide()">Apply</x-avian::button>
    </x-slot:footer>
</x-avian::drawer>

<x-avian::drawer name="doc-drawer-left" title="Menu" position="left" size="sm">
    <div class="aui-stack" style="gap: 6px">
        <a href="#">Dashboard</a>
        <a href="#">Orders</a>
        <a href="#">Customers</a>
    </div>
</x-avian::drawer>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Same Alpine component as <code>&lt;x-avian::modal&gt;</code>: <code>modal="name"</code> on a button, <code>aui-modal-open</code> / <code>aui-modal-close</code> events, and <code>hide()</code> inside.</li>
        <li>The body scrolls on its own; header and footer stay put. Page scroll is locked while it is open.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
