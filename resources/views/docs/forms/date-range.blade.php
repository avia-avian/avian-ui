@php
    $props = [
        ['name', 'string|null', 'null', 'Submits name[from] and name[to]. Errors are read from name, name.from and name.to.'],
        ['value', 'array|null', 'null', "['from' => ..., 'to' => ...] as dates or strings in value-format. Falls back to old input."],
        ['date-format', 'string', "'d/m/Y'", 'Flatpickr format shown in the field.'],
        ['value-format', 'string', "'Y-m-d'", 'Format of the submitted values. Use Y, m, d, H and i.'],
        ['presets', 'bool|array', 'false', 'Quick picks: true for the common ones, or keys out of today, yesterday, last_7_days, last_30_days, this_month, last_month and this_year.'],
        ['clearable', 'bool', 'false', 'Adds a Clear button once a range is picked.'],
        ['min-date', 'string|null', 'null', 'Earliest selectable date.'],
        ['max-date', 'string|null', 'null', 'Latest selectable date.'],
        ['placeholder', 'string|null', 'null', 'Placeholder text.'],
        ['label', 'string|null', 'null', 'Label shown above the field.'],
        ['hint', 'string|null', 'null', 'Helper text under the field.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height.'],
        ['disabled', 'bool', 'false', 'Disables the field and the presets.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the control.'],
    ];

    $examples = [
        [
            'title' => 'A report filter',
            'text' => 'Flatpickr is loaded by the host app, exactly as for the datepicker.',
            'code' => <<<'BLADE'
                <x-avian::date-range name="period" label="Period" presets clearable />

                // The request receives period[from] and period[to] as Y-m-d
                $request->validate([
                    'period.from' => ['nullable', 'date'],
                    'period.to' => ['nullable', 'date', 'after_or_equal:period.from'],
                ]);

                Order::whereBetween('created_at', [
                    $request->date('period.from')->startOfDay(),
                    $request->date('period.to')->endOfDay(),
                ]);
                BLADE,
        ],
        [
            'title' => 'Editing a saved range',
            'code' => <<<'BLADE'
                <x-avian::date-range
                    name="period"
                    label="Campaign dates"
                    :value="['from' => $campaign->starts_on, 'to' => $campaign->ends_on]"
                    :presets="['this_month', 'last_month', 'this_year']"
                    required
                />
                BLADE,
        ],
        [
            'title' => 'With Livewire',
            'code' => <<<'BLADE'
                <x-avian::date-range wire:model.live="period" label="Period" presets />

                // In the component
                public array $period = ['from' => '', 'to' => ''];
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Date range', 'subtitle' => 'Pick a from–to range, submitted as two fields'])
<p class="aui-showcase-lead">
    A range picker for reports and filters. It shows one field but submits <code>from</code> and
    <code>to</code> separately, in a fixed format, so the request validates and queries without parsing.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::date-range name="docs_period" label="Period" presets clearable placeholder="Pick a range" />
        <x-avian::date-range name="docs_campaign" label="Campaign dates" :value="['from' => now()->startOfMonth(), 'to' => now()->endOfMonth()]" :presets="['this_month', 'last_month', 'this_year']" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>It renders a <code>flatpickr-input</code> in range mode, which the host app upgrades with flatpickr like the datepicker, plus two hidden inputs, <code>name[from]</code> and <code>name[to]</code>.</li>
        <li>The visible field uses <code>date-format</code>; the hidden ones always use <code>value-format</code>.</li>
        <li>A preset fills both dates. The matching preset is highlighted while the range is unchanged.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
