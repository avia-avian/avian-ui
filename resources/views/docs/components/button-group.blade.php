@php
    $props = [
        ['attached', 'bool', 'false', 'button-group: joins the buttons into one segmented control with shared borders.'],
        ['vertical', 'bool', 'false', 'button-group: stacks the buttons in a column.'],
        ['label', 'string|null', 'null', 'button-group and toolbar: aria-label announced for the group.'],
        ['end (slot)', 'slot', '—', 'toolbar: actions pushed to the right edge.'],
    ];

    $examples = [
        [
            'title' => 'Spaced group',
            'text' => 'Without `attached` the group just lines buttons up with a gap, e.g. for form actions.',
            'code' => <<<'BLADE'
                <x-avian::button-group>
                    <x-avian::button variant="light">Cancel</x-avian::button>
                    <x-avian::button>Save</x-avian::button>
                </x-avian::button-group>
                BLADE,
        ],
        [
            'title' => 'Segmented control',
            'text' => 'Mark the selected button with `active`. For a toggle that switches client-side, bind aria-pressed instead — it gets the same look.',
            'code' => <<<'BLADE'
                <x-avian::button-group attached label="View">
                    <x-avian::button variant="light" icon="fas fa-list" active>List</x-avian::button>
                    <x-avian::button variant="light" icon="fas fa-grip">Grid</x-avian::button>
                </x-avian::button-group>

                {{-- Alpine --}}
                <x-avian::button-group attached x-data="{ view: 'list' }">
                    <x-avian::button variant="light" x-on:click="view = 'list'" x-bind:aria-pressed="view === 'list'">List</x-avian::button>
                    <x-avian::button variant="light" x-on:click="view = 'grid'" x-bind:aria-pressed="view === 'grid'">Grid</x-avian::button>
                </x-avian::button-group>
                BLADE,
        ],
        [
            'title' => 'Row actions',
            'text' => 'Attached icon buttons keep table rows compact. The label is also shown as a native tooltip.',
            'code' => <<<'BLADE'
                <x-avian::button-group attached label="Order actions">
                    <x-avian::button variant="light" size="sm" icon="fas fa-eye" icon-only label="View" />
                    <x-avian::button variant="light" size="sm" icon="fas fa-pen" icon-only label="Edit" />
                    <x-avian::button variant="light" size="sm" icon="fas fa-trash" icon-only label="Delete" />
                </x-avian::button-group>
                BLADE,
        ],
        [
            'title' => 'Toolbar above a table',
            'text' => 'Filters and search go in the default slot, actions in `end`. It wraps onto two lines on narrow screens.',
            'code' => <<<'BLADE'
                <x-avian::toolbar label="Orders">
                    <x-avian::input name="q" icon="fas fa-search" placeholder="Search orders" :field="false" />

                    <x-avian::button-group attached label="Status">
                        <x-avian::button variant="light" active>All</x-avian::button>
                        <x-avian::button variant="light">Paid</x-avian::button>
                        <x-avian::button variant="light">Pending</x-avian::button>
                    </x-avian::button-group>

                    <x-slot:end>
                        <x-avian::button variant="light" icon="fas fa-download">Export</x-avian::button>
                        <x-avian::button icon="fas fa-plus">New order</x-avian::button>
                    </x-slot:end>
                </x-avian::toolbar>

                <x-avian::table :headers="['Order', 'Customer', 'Total']">...</x-avian::table>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Button group & toolbar', 'subtitle' => 'Grouped buttons, segmented controls and action bars'])
<p class="aui-showcase-lead">
    <code>button-group</code> lines related buttons up — spaced, or joined into a segmented control.
    <code>toolbar</code> is the bar above a table or list: filters on the left, actions on the right.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <x-avian::toolbar label="Orders">
            <x-avian::input name="toolbar_q" icon="fas fa-search" placeholder="Search orders" :field="false" />

            <x-avian::button-group attached label="Status">
                <x-avian::button variant="light" active>All</x-avian::button>
                <x-avian::button variant="light">Paid</x-avian::button>
                <x-avian::button variant="light">Pending</x-avian::button>
            </x-avian::button-group>

            <x-slot:end>
                <x-avian::button variant="light" icon="fas fa-download">Export</x-avian::button>
                <x-avian::button icon="fas fa-plus">New order</x-avian::button>
            </x-slot:end>
        </x-avian::toolbar>

        <div class="aui-row" style="flex-wrap: wrap; align-items: flex-start">
            <x-avian::button-group attached label="View">
                <x-avian::button variant="light" icon="fas fa-list" active>List</x-avian::button>
                <x-avian::button variant="light" icon="fas fa-grip">Grid</x-avian::button>
                <x-avian::button variant="light" icon="fas fa-calendar">Calendar</x-avian::button>
            </x-avian::button-group>

            <x-avian::button-group attached label="Order actions">
                <x-avian::button variant="light" size="sm" icon="fas fa-eye" icon-only label="View" />
                <x-avian::button variant="light" size="sm" icon="fas fa-pen" icon-only label="Edit" />
                <x-avian::button variant="light" size="sm" icon="fas fa-trash" icon-only label="Delete" />
            </x-avian::button-group>

            <x-avian::button-group attached label="Save">
                <x-avian::button icon="fas fa-check">Save</x-avian::button>
                <x-avian::button icon="fas fa-chevron-down" icon-only label="More save options" />
            </x-avian::button-group>

            <x-avian::button-group attached vertical label="Alignment">
                <x-avian::button variant="outline" color="secondary" size="sm">Top</x-avian::button>
                <x-avian::button variant="outline" color="secondary" size="sm">Middle</x-avian::button>
                <x-avian::button variant="outline" color="secondary" size="sm">Bottom</x-avian::button>
            </x-avian::button-group>

            <x-avian::button-group>
                <x-avian::button variant="light">Cancel</x-avian::button>
                <x-avian::button>Save</x-avian::button>
            </x-avian::button-group>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>An attached group rounds only its outer corners and overlaps borders, so neighbouring buttons share one line.</li>
        <li>The <code>active</code> button, or one with <code>aria-pressed="true"</code>, gets the selected look — light buttons turn primary-tinted.</li>
        <li>Both render <code>role="group"</code> / <code>role="toolbar"</code>; pass <code>label</code> so screen readers know what the controls are for.</li>
        <li>Inside a toolbar, a search input takes a fixed width instead of stretching across the whole row.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
