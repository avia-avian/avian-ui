@php
    $props = [
        ['tabs', 'array', '[]', '<x-avian::tabs>: key => label pairs. Each key must match a panel\'s name.'],
        ['active', 'string|null', 'null', '<x-avian::tabs>: key of the tab open on load. Defaults to the first key.'],
        ['variant', "'line'|'pill'|'segmented'|null", 'null', '<x-avian::tabs>: look of the tab list. Omit (or "line") for underlined tabs.'],
        ['name', 'string|null', 'null', '<x-avian::tabs.panel>: the tab key this panel belongs to.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'text' => 'List the tabs once in :tabs, then add one panel per key. The first tab is open by default.',
            'code' => <<<'BLADE'
                <x-avian::tabs :tabs="['overview' => 'Overview', 'activity' => 'Activity', 'settings' => 'Settings']">
                    <x-avian::tabs.panel name="overview">...</x-avian::tabs.panel>
                    <x-avian::tabs.panel name="activity">...</x-avian::tabs.panel>
                    <x-avian::tabs.panel name="settings">...</x-avian::tabs.panel>
                </x-avian::tabs>
                BLADE,
        ],
        [
            'title' => 'Variants',
            'code' => <<<'BLADE'
                <x-avian::tabs :tabs="$tabs" variant="pill">...</x-avian::tabs>
                <x-avian::tabs :tabs="$tabs" variant="segmented">...</x-avian::tabs>
                BLADE,
        ],
        [
            'title' => 'Open a specific tab (e.g. from the URL)',
            'text' => 'Useful after a redirect, so the user lands back on the tab they were editing.',
            'code' => <<<'BLADE'
                <x-avian::tabs :tabs="$tabs" :active="request('tab', 'overview')">...</x-avian::tabs>

                return redirect()->route('users.show', [$user, 'tab' => 'settings']);
                BLADE,
        ],
        [
            'title' => 'Listening for tab changes',
            'text' => 'Every switch dispatches aui-tab-changed with the new key — e.g. to update the URL or lazy-load data.',
            'code' => <<<'BLADE'
                <x-avian::tabs
                    :tabs="$tabs"
                    x-on:aui-tab-changed="history.replaceState(null, '', '?tab=' + $event.detail.tab)"
                >
                    ...
                </x-avian::tabs>
                BLADE,
        ],
        [
            'title' => 'Tabs inside a form',
            'text' => 'Hidden panels are only hidden with CSS, so all their fields are still submitted together.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('products.update', $product) }}" method="PUT">
                    <x-avian::tabs :tabs="['general' => 'General', 'pricing' => 'Pricing']">
                        <x-avian::tabs.panel name="general">
                            <x-avian::input name="name" label="Name" />
                        </x-avian::tabs.panel>
                        <x-avian::tabs.panel name="pricing">
                            <x-avian::input name="price" label="Price" numeric />
                        </x-avian::tabs.panel>
                    </x-avian::tabs>
                </x-avian::form>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Tabs', 'subtitle' => 'Switch between panels without leaving the page'])
<p class="aui-showcase-lead">
    Split related content into panels and show one at a time: profile sections, settings groups,
    a long form. Switching happens in the browser with Alpine — no page reload and no request.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 28px">
        <x-avian::tabs :tabs="['overview' => 'Overview', 'activity' => 'Activity', 'settings' => 'Settings']">
            <x-avian::tabs.panel name="overview">Line tabs (the default). The first panel is shown on load.</x-avian::tabs.panel>
            <x-avian::tabs.panel name="activity">
                <x-avian::empty title="No activity yet" text="Actions will show up here." />
            </x-avian::tabs.panel>
            <x-avian::tabs.panel name="settings">Settings panel.</x-avian::tabs.panel>
        </x-avian::tabs>

        <x-avian::tabs :tabs="['overview' => 'Overview', 'activity' => 'Activity']" variant="pill">
            <x-avian::tabs.panel name="overview">Pill tabs stand alone, with no shared underline.</x-avian::tabs.panel>
            <x-avian::tabs.panel name="activity">Same state, different look.</x-avian::tabs.panel>
        </x-avian::tabs>

        <x-avian::tabs :tabs="['day' => 'Day', 'week' => 'Week', 'month' => 'Month']" variant="segmented" active="week">
            <x-avian::tabs.panel name="day">Daily figures.</x-avian::tabs.panel>
            <x-avian::tabs.panel name="week">Segmented tabs, opened on "Week" with active="week".</x-avian::tabs.panel>
            <x-avian::tabs.panel name="month">Monthly figures.</x-avian::tabs.panel>
        </x-avian::tabs>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>All panels are rendered up front and hidden with <code>x-show</code>; the tab key links a button to its panel.</li>
        <li>The tab list uses <code>role="tablist"</code> / <code>role="tab"</code> and <code>aria-selected</code>; panels use <code>role="tabpanel"</code>.</li>
        <li>The selected tab isn't remembered across page loads — use <code>active</code> with a query string for that.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
