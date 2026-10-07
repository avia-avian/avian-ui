@php
    $props = [
        ['name', 'string|null', 'null', 'Input name.'],
        ['value', 'string', "'1'", 'Value sent when the switch is on. Nothing is sent when it is off.'],
        ['checked', 'bool', 'false', 'Initial on/off state. (Not read from old input — pass it yourself.)'],
        ['label', 'string|null', 'null', 'Text next to the switch. The slot is used when label is omitted.'],
        ['hint', 'string|null', 'null', 'Helper text under the switch.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the switch — handy in table rows.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'text' => 'A switch is a checkbox underneath: when off it sends nothing, so read it with $request->boolean().',
            'code' => <<<'BLADE'
                <x-avian::switch name="active" label="Active" :checked="old('active', $product->active)" />

                $product->active = $request->boolean('active');
                BLADE,
        ],
        [
            'title' => 'With a hint',
            'code' => <<<'BLADE'
                <x-avian::switch name="notify" label="Email notifications" hint="We only email about your own records." checked />
                BLADE,
        ],
        [
            'title' => 'In a table row (Livewire, saves instantly)',
            'code' => <<<'BLADE'
                <td>
                    <x-avian::switch
                        wire:click="toggleActive({{ $product->id }})"
                        :checked="$product->active"
                        :field="false"
                        label="Active"
                    />
                </td>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'code' => <<<'BLADE'
                <x-avian::switch wire:model.live="darkMode" label="Dark mode" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Switch', 'subtitle' => 'Toggle a setting on or off'])
<p class="aui-showcase-lead">
    A toggle for settings that take effect as a state — "active", "published", "notifications". It is a
    checkbox underneath (with <code>role="switch"</code> for screen readers), so it submits and validates
    exactly like one. Prefer a checkbox for agreements such as "I accept the terms".
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <div class="aui-stack">
            <x-avian::switch name="switch_active" label="Active" checked />
            <x-avian::switch name="switch_published" label="Published" />
            <x-avian::switch name="switch_locked" label="Locked (disabled)" checked disabled />
        </div>

        <div class="aui-stack">
            <x-avian::switch name="switch_notify" label="Email notifications" hint="We only email about your own records." checked />
            <x-avian::switch name="switch_public" label="Public profile" error="Verify your email before going public." />
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Clicking the track or the label text toggles it; Space toggles it from the keyboard.</li>
        <li>When off, nothing is submitted — use <code>$request-&gt;boolean('name')</code> to get a clean <code>true</code>/<code>false</code>.</li>
        <li><code>checked</code> is not restored from old input automatically — bind it with <code>:checked="old('name', $model-&gt;name)"</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
