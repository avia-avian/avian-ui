{{--
    A styled range input for picking a number between `min` and `max`.

        <x-avian::slider name="volume" label="Volume" :value="40" show-value suffix="%" />

    `range` adds a second thumb for picking a low and a high bound — a price
    filter, an age bracket. It submits `name[min]` and `name[max]`, so the
    request receives an array and validates as `price.min` / `price.max`:

        <x-avian::slider name="price" :min="0" :max="1000" :step="10" :value="[100, 500]" range show-value prefix="$" />

    With Livewire, `wire:model` binds through Alpine's `x-modelable`: a number
    for a single slider, `['min' => ..., 'max' => ...]` for a range. Use
    `.live.debounce` rather than `.live`, or every pixel of a drag is a request.
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'range' => false,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'showValue' => false,
    'prefix' => null,
    'suffix' => null,
    'disabled' => false,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error
        ?? $avianUi->errorFor($fieldName, $errorBag)
        ?? ($range && $fieldName !== null ? ($avianUi->errorFor($fieldName.'.min', $errorBag) ?? $avianUi->errorFor($fieldName.'.max', $errorBag)) : null);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $rootAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));
    $wired = $modelAttributes->isNotEmpty();

    $min = (float) $min;
    $max = (float) $max > $min ? (float) $max : $min + 100;
    $step = (float) $step > 0 ? (float) $step : 1.0;

    $current = $wired ? $value : $avianUi->old($name, $value);

    $clamp = fn (mixed $number, float $fallback): float => is_numeric($number) ? max($min, min($max, (float) $number)) : $fallback;

    // Whole numbers print without a trailing ".0", so `40` stays `40`.
    $number = fn (float $number): string => rtrim(rtrim(number_format($number, 6, '.', ''), '0'), '.');

    if ($range) {
        $current = is_array($current) ? $current : [];
        $low = $clamp($current['min'] ?? $current[0] ?? null, $min);
        $high = $clamp($current['max'] ?? $current[1] ?? null, $max);

        if ($low > $high) {
            [$low, $high] = [$high, $low];
        }
    } else {
        $low = $min;
        $high = $clamp($avianUi->scalar($current), $min);
    }

    $percent = fn (float $number): float => round(($number - $min) / ($max - $min) * 100, 2);

    $output = $range
        ? $prefix.$number($low).$suffix.' – '.$prefix.$number($high).$suffix
        : $prefix.$number($high).$suffix;
@endphp

<x-avian-ui::field :bare="! $field" :label="$label" :for="$inputId" :hint="$hint" :error="$inputError" :required="$required">
    {{-- Per-render state goes through `data-*` attributes so the `x-data`
         expression stays constant across Livewire morphs. --}}
    <div
        x-data="auiSlider({ range: @js((bool) $range), min: @js($min), max: @js($max), step: @js($step) })"
        x-modelable="value"
        {{ $modelAttributes }}
        data-aui-value="{{ json_encode($range ? ['min' => $low, 'max' => $high] : $high) }}"
        {{ $rootAttributes->class([
            'aui-slider',
            'aui-slider-range' => $range,
            'aui-slider-invalid' => filled($inputError),
            'is-disabled' => $disabled,
        ]) }}
        style="--aui-slider-start: {{ $range ? $percent($low) : 0 }}%; --aui-slider-end: {{ $percent($high) }}%"
        x-bind:style="{ '--aui-slider-start': start + '%', '--aui-slider-end': end + '%' }"
    >
        <div class="aui-slider-control">
            <div class="aui-slider-track" aria-hidden="true"></div>

            @if ($range)
                <input
                    type="range"
                    class="aui-slider-input aui-slider-input-low"
                    @if (filled($name)) name="{{ $name }}[min]" @endif
                    @if (filled($inputId)) id="{{ $inputId }}" @endif
                    min="{{ $number($min) }}"
                    max="{{ $number($max) }}"
                    step="{{ $number($step) }}"
                    value="{{ $number($low) }}"
                    x-bind:value="low"
                    x-on:input="setLow($event.target.value)"
                    x-bind:class="{ 'is-top': lowOnTop }"
                    aria-label="{{ filled($label) ? $label.' minimum' : 'Minimum' }}"
                    @if (filled($inputError)) aria-invalid="true" @endif
                    @disabled($disabled)
                >

                <input
                    type="range"
                    class="aui-slider-input aui-slider-input-high"
                    @if (filled($name)) name="{{ $name }}[max]" @endif
                    min="{{ $number($min) }}"
                    max="{{ $number($max) }}"
                    step="{{ $number($step) }}"
                    value="{{ $number($high) }}"
                    x-bind:value="high"
                    x-on:input="setHigh($event.target.value)"
                    aria-label="{{ filled($label) ? $label.' maximum' : 'Maximum' }}"
                    @if (filled($inputError)) aria-invalid="true" @endif
                    @disabled($disabled)
                >
            @else
                <input
                    type="range"
                    class="aui-slider-input"
                    @if (filled($name)) name="{{ $name }}" @endif
                    @if (filled($inputId)) id="{{ $inputId }}" @endif
                    min="{{ $number($min) }}"
                    max="{{ $number($max) }}"
                    step="{{ $number($step) }}"
                    value="{{ $number($high) }}"
                    x-bind:value="high"
                    x-on:input="setHigh($event.target.value)"
                    @if (filled($inputError)) aria-invalid="true" @endif
                    @required($required)
                    @disabled($disabled)
                >
            @endif
        </div>

        @if ($showValue)
            <output
                class="aui-slider-value"
                @if (filled($inputId)) for="{{ $inputId }}" @endif
                data-prefix="{{ $prefix }}"
                data-suffix="{{ $suffix }}"
                x-text="display($el.dataset.prefix, $el.dataset.suffix)"
            >{{ $output }}</output>
        @endif
    </div>
</x-avian-ui::field>
