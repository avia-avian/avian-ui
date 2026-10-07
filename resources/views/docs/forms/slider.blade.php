@php
    $props = [
        ['name', 'string|null', 'null', 'Input name. In range mode the thumbs submit name[min] and name[max].'],
        ['value', 'int|float|array|null', 'null', 'Starting value. In range mode, [low, high] or [\'min\' => …, \'max\' => …]. Old input wins over it.'],
        ['min', 'int|float', '0', 'Lowest value on the scale.'],
        ['max', 'int|float', '100', 'Highest value on the scale.'],
        ['step', 'int|float', '1', 'Increment the thumb snaps to. Decimal steps such as 0.5 work.'],
        ['range', 'bool', 'false', 'Adds a second thumb to pick a low and a high bound.'],
        ['label', 'string|null', 'null', 'Field label.'],
        ['hint', 'string|null', 'null', 'Helper text under the slider.'],
        ['show-value', 'bool', 'false', 'Shows the current value (or "low – high") next to the track, updated as it is dragged.'],
        ['prefix', 'string|null', 'null', 'Text before the shown value, such as $.'],
        ['suffix', 'string|null', 'null', 'Text after the shown value, such as % or kg.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors (and name.min / name.max in range mode).'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Marks the label as required.'],
        ['disabled', 'bool', 'false', 'Greys the slider out and stops it from moving.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the slider, without label, hint or error.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::slider name="volume" label="Volume" :value="old('volume', $settings->volume)" show-value suffix="%" />
                BLADE,
        ],
        [
            'title' => 'Price range filter',
            'text' => 'Range mode submits an array, so validate each bound on its own.',
            'code' => <<<'BLADE'
                <x-avian::slider
                    name="price"
                    label="Price"
                    :min="0"
                    :max="1000"
                    :step="10"
                    :value="[100, 500]"
                    range
                    show-value
                    prefix="$"
                />

                $request->validate([
                    'price.min' => ['required', 'numeric', 'min:0'],
                    'price.max' => ['required', 'numeric', 'gte:price.min'],
                ]);
                BLADE,
        ],
        [
            'title' => 'Decimal steps',
            'code' => <<<'BLADE'
                <x-avian::slider name="rating" label="Minimum rating" :min="0" :max="5" :step="0.5" :value="3.5" show-value />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'A range binds to an array with min and max keys. Debounce it: every pixel of a drag is an input event.',
            'code' => <<<'BLADE'
                public int $volume = 40;
                public array $price = ['min' => 100, 'max' => 500];

                <x-avian::slider wire:model.live.debounce.300ms="volume" show-value suffix="%" />
                <x-avian::slider wire:model.live.debounce.300ms="price" :max="1000" :step="10" range show-value prefix="$" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Slider', 'subtitle' => 'Pick a number by dragging'])
<p class="aui-showcase-lead">
    A styled range input for values where the rough position matters more than the exact digit: a volume, a
    discount, a price bracket. Add <code>range</code> for a two-thumb slider that picks a low and a high bound.
    Use a number input instead when people already know the exact value they want to type.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <div class="aui-stack">
            <x-avian::slider name="slider_volume" label="Volume" :value="40" show-value suffix="%" />
            <x-avian::slider name="slider_rating" label="Minimum rating" :min="0" :max="5" :step="0.5" :value="3.5" show-value />
            <x-avian::slider name="slider_locked" label="Locked (disabled)" :value="70" show-value suffix="%" disabled />
        </div>

        <div class="aui-stack">
            <x-avian::slider name="slider_price" label="Price" :min="0" :max="1000" :step="10" :value="[100, 500]" range show-value prefix="$" />
            <x-avian::slider name="slider_age" label="Age" :min="18" :max="99" :value="[25, 40]" range show-value hint="Both ends are inclusive." />
            <x-avian::slider name="slider_discount" label="Discount" :value="85" show-value suffix="%" error="Discounts above 50% need approval." />
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>It is a native <code>&lt;input type="range"&gt;</code>, so arrow keys, Page Up/Down, Home and End all work from the keyboard.</li>
        <li>Values are clamped to <code>min</code>–<code>max</code> and snapped to <code>step</code>; in range mode the thumbs can meet but never cross.</li>
        <li>A range submits <code>name[min]</code> and <code>name[max]</code>, and its error is read from <code>name</code>, <code>name.min</code> or <code>name.max</code>.</li>
        <li>Old input is restored automatically after a failed validation.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
