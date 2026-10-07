@php
    $props = [
        ['name', 'string|null', 'null', 'Group name — every radio in the group shares it.'],
        ['value', 'string|null', 'null', 'Value submitted when this radio is the selected one.'],
        ['checked', 'bool', 'false', 'Initial selection. (Not read from old input — compare it yourself, see below.)'],
        ['label', 'string|null', 'null', 'Text next to the radio. The slot is used when label is omitted.'],
        ['hint', 'string|null', 'null', 'Muted line under the label text.'],
        ['inline', 'bool', 'false', 'Lay radios out side by side instead of stacked.'],
        ['field', 'bool', 'false', 'Off by default, because one error under every radio would repeat. Show the group error once with <x-avian::error>.'],
        ['error', 'string|null', 'null', 'Only used with :field="true".'],
        ['error-bag', 'string|null', 'null', 'Only used with :field="true".'],
    ];

    $examples = [
        [
            'title' => 'A radio group',
            'text' => 'Radios render without the field wrapper, so build the group yourself: a label on top, the radios, and one error line under them.',
            'code' => <<<'BLADE'
                <div>
                    <x-avian::label required>Plan</x-avian::label>
                    <x-avian::radio name="plan" value="basic" label="Basic" inline checked />
                    <x-avian::radio name="plan" value="pro" label="Pro" inline />
                    <x-avian::error name="plan" />
                </div>
                BLADE,
        ],
        [
            'title' => 'Restoring the selection',
            'text' => 'Compare each value with old input (falling back to the model) to keep the choice after a failed validation.',
            'code' => <<<'BLADE'
                @php($plan = old('plan', $subscription->plan ?? 'basic'))

                @foreach (['basic' => 'Basic', 'pro' => 'Pro', 'team' => 'Team'] as $value => $text)
                    <x-avian::radio name="plan" :value="$value" :label="$text" :checked="$plan === $value" />
                @endforeach

                $request->validate(['plan' => ['required', 'in:basic,pro,team']]);
                BLADE,
        ],
        [
            'title' => 'Options with descriptions',
            'code' => <<<'BLADE'
                <x-avian::radio name="shipping" value="standard" label="Standard" hint="3–5 working days, free" checked />
                <x-avian::radio name="shipping" value="express" label="Express" hint="Next day, Rp 25.000" />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'With wire:model you don\'t need `checked` — Livewire selects the radio matching the property.',
            'code' => <<<'BLADE'
                <x-avian::radio wire:model.live="plan" name="plan" value="basic" label="Basic" inline />
                <x-avian::radio wire:model.live="plan" name="plan" value="pro" label="Pro" inline />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Radio', 'subtitle' => 'Pick exactly one option'])
<p class="aui-showcase-lead">
    Radio buttons for choosing one option out of a few (2–5) that should all be visible at once. For
    longer lists a select is more compact.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <div>
            <x-avian::label required>Plan</x-avian::label>
            <x-avian::radio name="radio_plan" value="basic" label="Basic" inline checked />
            <x-avian::radio name="radio_plan" value="pro" label="Pro" inline />
            <x-avian::radio name="radio_plan" value="team" label="Team" inline />
        </div>

        <div class="aui-stack">
            <x-avian::radio name="radio_shipping" value="standard" label="Standard" hint="3–5 working days, free" checked />
            <x-avian::radio name="radio_shipping" value="express" label="Express" hint="Next day, Rp 25.000" />
            <x-avian::radio name="radio_shipping" value="pickup" label="Pickup" hint="Currently unavailable" disabled />
        </div>

        <div>
            <x-avian::label>Payment</x-avian::label>
            <x-avian::radio name="radio_payment" value="card" label="Card" inline />
            <x-avian::radio name="radio_payment" value="transfer" label="Bank transfer" inline />
            <x-avian::error>Please choose a payment method.</x-avian::error>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Radios with the same <code>name</code> form a group; the browser only allows one of them to be checked.</li>
        <li>Unlike other controls, <code>field</code> defaults to <code>false</code>: put one <code>&lt;x-avian::error name="…" /&gt;</code> under the group.</li>
        <li><code>checked</code> is not restored from old input automatically — compare values with <code>old()</code>.</li>
        <li>Arrow keys move between radios of a group, as in any native radio group.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
