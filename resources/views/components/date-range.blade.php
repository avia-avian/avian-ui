{{--
    A date range picker that submits two fields, `name[from]` and `name[to]`:

        <x-avian::date-range name="period" label="Period" presets />

        <x-avian::date-range
            name="period"
            :value="['from' => $report->starts_on, 'to' => $report->ends_on]"
            :presets="['this_month', 'last_month', 'this_year']"
            clearable
        />

    The request receives `period.from` / `period.to` in `value-format`
    (Y-m-d by default) whatever `date-format` the field shows, so it
    validates and queries as is. Like the datepicker it renders a
    `flatpickr-input` the host app upgrades with flatpickr (range mode).

    `presets` adds quick picks: true for today, last 7 days, last 30 days,
    this month and last month, or a list of keys out of today, yesterday,
    last_7_days, last_30_days, this_month, last_month and this_year.

    With Livewire, `wire:model` binds through Alpine's `x-modelable` to an
    array with `from` and `to` keys.
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'size' => null,
    'placeholder' => null,
    'dateFormat' => 'd/m/Y',
    'valueFormat' => 'Y-m-d',
    'minDate' => null,
    'maxDate' => null,
    'presets' => false,
    'clearable' => false,
    'field' => true,
    'disabled' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error
        ?? $avianUi->errorFor($fieldName, $errorBag)
        ?? ($fieldName !== null ? ($avianUi->errorFor($fieldName.'.from', $errorBag) ?? $avianUi->errorFor($fieldName.'.to', $errorBag)) : null);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $inputAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));
    $wired = $modelAttributes->isNotEmpty();

    $current = $wired ? $value : $avianUi->old($name, $value);
    $current = is_array($current) ? $current : [];

    // A value is a date object or a string in the value format; either way
    // it comes out as a Carbon instance, or null when it does not parse.
    // The value format's tokens (Y, m, d, H, i) read the same in PHP.
    $toDate = function (mixed $date) use ($valueFormat): ?\Illuminate\Support\Carbon {
        if ($date instanceof \DateTimeInterface) {
            return \Illuminate\Support\Carbon::instance($date);
        }

        if (! is_string($date) || trim($date) === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::createFromFormat('!'.$valueFormat, trim($date)) ?: null;
        } catch (\Throwable) {
            try {
                return \Illuminate\Support\Carbon::parse($date);
            } catch (\Throwable) {
                return null;
            }
        }
    };

    $from = $toDate($current['from'] ?? $current[0] ?? null);
    $to = $toDate($current['to'] ?? $current[1] ?? null);

    if ($from && $to && $from->greaterThan($to)) {
        [$from, $to] = [$to, $from];
    }

    $values = [
        'from' => $from ? (string) $avianUi->formatDate($from, $valueFormat) : '',
        'to' => $to ? (string) $avianUi->formatDate($to, $valueFormat) : '',
    ];

    $display = $avianUi->formatDate(array_values(array_filter([$from, $to])), $dateFormat, ' to ');

    $presetKeys = ['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year'];
    $presets = match (true) {
        $presets === true => ['today', 'last_7_days', 'last_30_days', 'this_month', 'last_month'],
        is_array($presets) => array_values(array_intersect($presets, $presetKeys)),
        is_string($presets) && filled($presets) => array_values(array_intersect(array_map('trim', explode(',', $presets)), $presetKeys)),
        default => [],
    };

    $minDate = $avianUi->formatDate($minDate, $dateFormat);
    $maxDate = $avianUi->formatDate($maxDate, $dateFormat);
@endphp

<x-avian-ui::field
    :bare="! $field"
    :label="$label"
    :for="$inputId"
    :hint="$hint"
    :error="$inputError"
    :required="$required"
>
    {{-- Per-render state goes through `data-*` attributes so the `x-data`
         expression stays constant across Livewire morphs. --}}
    <div
        x-data="auiDateRange({ format: @js($valueFormat) })"
        x-modelable="value"
        {{ $modelAttributes }}
        data-aui-value="{{ json_encode($values) }}"
        @class(['aui-date-range', 'is-disabled' => $disabled])
    >
        <input
            x-ref="input"
            x-on:change="changed()"
            data-fp-mode="range"
            data-fp-date-format="{{ $dateFormat }}"
            @if ($minDate) data-fp-min-date="{{ $minDate }}" @endif
            @if ($maxDate) data-fp-max-date="{{ $maxDate }}" @endif
            {{ $inputAttributes->class([
                'aui-input',
                'aui-input-'.$size => filled($size),
                'aui-input-invalid' => filled($inputError),
                'flatpickr-input',
            ])->merge([
                'type' => 'text',
                'autocomplete' => 'off',
                'id' => $inputId,
                'value' => $display,
                'placeholder' => $placeholder,
                'required' => $required,
                'disabled' => $disabled,
                'aria-invalid' => filled($inputError) ? 'true' : null,
            ]) }}
        >

        @if (filled($name))
            <input type="hidden" name="{{ $name }}[from]" value="{{ $values['from'] }}" x-bind:value="value.from">
            <input type="hidden" name="{{ $name }}[to]" value="{{ $values['to'] }}" x-bind:value="value.to">
        @endif

        @if ($presets !== [] || $clearable)
            <div class="aui-date-range-presets" role="group" aria-label="{{ __('avian-ui::messages.date_presets') }}">
                @foreach ($presets as $preset)
                    <button
                        type="button"
                        class="aui-date-range-preset"
                        x-on:click="preset(@js($preset))"
                        x-bind:class="{ 'is-active': isPreset(@js($preset)) }"
                        x-bind:aria-pressed="isPreset(@js($preset)) ? 'true' : 'false'"
                        @disabled($disabled)
                    >{{ __('avian-ui::messages.presets.'.$preset) }}</button>
                @endforeach

                @if ($clearable)
                    <button
                        type="button"
                        class="aui-date-range-preset aui-date-range-clear"
                        x-on:click="clear()"
                        x-show="value.from"
                        @disabled($disabled)
                    >{{ __('avian-ui::messages.clear') }}</button>
                @endif
            </div>
        @endif
    </div>
</x-avian-ui::field>
