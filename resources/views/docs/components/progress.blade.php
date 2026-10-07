@php
    $props = [
        ['value', 'int|float', '0', 'Current amount.'],
        ['max', 'int|float', '100', 'Amount that means "complete". The bar shows value / max as a percentage, clamped to 0–100%.'],
        ['variant', "'success'|'warning'|'danger'|null", 'null', 'Bar colour. Omit for the theme\'s primary colour.'],
        ['label', 'string|null', 'null', 'Text shown above the bar on the left.'],
        ['show-value', 'bool', 'false', 'Shows the percentage above the bar on the right.'],
    ];

    $examples = [
        [
            'title' => 'Percentage',
            'code' => <<<'BLADE'
                <x-avian::progress :value="68" label="Completion" show-value />
                BLADE,
        ],
        [
            'title' => 'Counts instead of percentages',
            'text' => 'Pass the raw numbers with `max`; the component does the division and never overflows past 100%.',
            'code' => <<<'BLADE'
                <x-avian::progress
                    :value="$project->tasks()->done()->count()"
                    :max="$project->tasks()->count()"
                    label="Tasks done"
                    show-value
                />
                BLADE,
        ],
        [
            'title' => 'Colour by threshold',
            'code' => <<<'BLADE'
                @php($used = $quota->used / $quota->limit * 100)

                <x-avian::progress
                    :value="$used"
                    :variant="$used >= 90 ? 'danger' : ($used >= 70 ? 'warning' : 'success')"
                    label="Storage"
                    show-value
                />
                BLADE,
        ],
        [
            'title' => 'Live progress (Livewire polling)',
            'code' => <<<'BLADE'
                <div wire:poll.2s>
                    <x-avian::progress :value="$import->processed" :max="$import->total" label="Importing" show-value />
                </div>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Progress', 'subtitle' => 'How far along something is'])
<p class="aui-showcase-lead">
    A horizontal bar showing progress towards a goal: profile completion, an import, a storage quota.
    Give it a value (and optionally a max) and it works out the percentage.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <x-avian::progress :value="68" label="Completion" show-value />
        <x-avian::progress :value="18" :max="24" label="Tasks done (18 of 24)" show-value variant="success" />
        <x-avian::progress :value="75" label="Storage" show-value variant="warning" />
        <x-avian::progress :value="96" label="Monthly quota" show-value variant="danger" />
        <x-avian::progress :value="40" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The percentage is <code>value / max × 100</code>, rounded and clamped between 0 and 100 — bad data never breaks the layout.</li>
        <li>A <code>max</code> of 0 or less is treated as 100, so an empty project doesn't divide by zero.</li>
        <li>The bar carries <code>role="progressbar"</code> and <code>aria-valuenow</code> for screen readers.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
