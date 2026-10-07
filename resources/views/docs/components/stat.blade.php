@php
    $props = [
        ['label', 'string|null', 'null', 'What is measured.'],
        ['value', 'string|null', 'null', 'The headline number, already formatted.'],
        ['change', 'string|null', 'null', 'How it moved, e.g. "+12.5%". Its sign picks the trend when `trend` is not given.'],
        ['trend', "'up'|'down'|'flat'|null", 'null', 'Force the arrow direction.'],
        ['invert', 'bool', 'false', 'Down is good (costs, returns, overdue): flips the green / red.'],
        ['description', 'string|null', 'null', 'Muted text after the change, e.g. "vs last month".'],
        ['icon', 'string|null', 'null', 'Icon class shown in a tinted square.'],
        ['color', "'primary'|'success'|'warning'|'danger'|'info'|'neutral'", "'primary'", 'Tint of the icon.'],
        ['href', 'string|null', 'null', 'Makes the whole tile a link.'],
        ['navigate', 'bool', 'false', 'Adds wire:navigate to an href tile.'],
        ['default slot', 'slot', '—', 'Extra content under the numbers: a progress bar, a small chart.'],
    ];

    $examples = [
        [
            'title' => 'A row of KPIs',
            'code' => <<<'BLADE'
                <div class="aui-grid aui-grid-4">
                    <x-avian::stat label="Revenue" :value="Number::currency($revenue, 'IDR')" change="+12.5%" description="vs last month" icon="fas fa-wallet" />
                    <x-avian::stat label="Orders" value="1,284" change="-3.1%" description="vs last month" icon="fas fa-cart-shopping" color="info" />
                    <x-avian::stat label="Returns" value="18" change="-22%" invert description="fewer is better" icon="fas fa-rotate-left" color="warning" />
                    <x-avian::stat label="Overdue invoices" value="7" :href="route('invoices.index', ['overdue' => 1])" navigate icon="fas fa-file-invoice" color="danger" />
                </div>
                BLADE,
        ],
        [
            'title' => 'With a progress bar',
            'code' => <<<'BLADE'
                <x-avian::stat label="Monthly target" value="72%" description="Rp 720 jt of Rp 1 M">
                    <x-avian::progress :value="72" />
                </x-avian::stat>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Stat', 'subtitle' => 'KPI tile for dashboards'])
<p class="aui-showcase-lead">
    One headline number with a label, an optional icon and how it moved since last period.
    Put several in <code>aui-grid aui-grid-4</code> for a dashboard row.
</p>

<div class="aui-showcase-demo">
    <div class="aui-grid aui-grid-4">
        <x-avian::stat label="Revenue" value="Rp 1,28 M" change="+12.5%" description="vs last month" icon="fas fa-wallet" />
        <x-avian::stat label="Orders" value="1,284" change="-3.1%" description="vs last month" icon="fas fa-cart-shopping" color="info" />
        <x-avian::stat label="Returns" value="18" change="-22%" invert description="fewer is better" icon="fas fa-rotate-left" color="warning" />
        <x-avian::stat label="Overdue invoices" value="7" href="#" icon="fas fa-file-invoice" color="danger" description="Click to review" />
    </div>

    <div class="aui-grid" style="margin-top: 20px">
        <x-avian::stat label="Monthly target" value="72%" description="Rp 720 jt of Rp 1 M" icon="fas fa-bullseye" color="success">
            <x-avian::progress :value="72" />
        </x-avian::stat>
        <x-avian::stat label="Active stores" value="342" change="0%" description="no change" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>A change starting with <code>-</code> shows a down arrow, one with any non-zero digit an up arrow, and <code>0%</code> is flat.</li>
        <li>Up is green and down is red; <code>invert</code> swaps the colors for numbers where less is better.</li>
        <li>Pass <code>value</code> already formatted — the component never formats numbers itself.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
