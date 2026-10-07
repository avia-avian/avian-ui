{{--
    Renders a plain text input carrying the `flatpickr-input` hook class and
    `data-fp-*` attributes. Flatpickr itself is not bundled by this package —
    the host application loads it and upgrades the hook class on page load
    (and again after `livewire:navigated`, for a Livewire SPA-style page):

        document.querySelectorAll('.flatpickr-input').forEach((input) => {
            flatpickr(input, {
                mode: input.dataset.fpMode,
                dateFormat: input.dataset.fpDateFormat,
                enableTime: input.dataset.fpEnableTime === 'true',
                noCalendar: input.dataset.fpNoCalendar === 'true',
                time_24hr: input.dataset.fpTime24hr === 'true',
                minDate: input.dataset.fpMinDate || null,
                maxDate: input.dataset.fpMaxDate || null,
                minTime: input.dataset.fpMinTime || null,
                maxTime: input.dataset.fpMaxTime || null,
            });
        });

    Flatpickr marks the input `readonly` by default (typing is disabled in
    favor of the calendar popup), which is why it renders with the same
    dimmed `.aui-input[readonly]` styling as a disabled field — pass
    `allowInput: true` to flatpickr's config if the host app wants a typable
    field instead.

    Usage:
        <x-avian::datepicker name="start_date" label="Start date" />
        <x-avian::datepicker name="range" label="Date range" mode="range" />
        <x-avian::datepicker name="datetime" label="Appointment" enable-time date-format="Y-m-d H:i" />
        <x-avian::datepicker name="opens_at" label="Opens at" mode="time" min-time="08:00" max-time="17:00" />

    `mode="time"` is a time-only picker (flatpickr's `noCalendar`): the
    format defaults to `H:i`, and `time-24hr` (on by default) switches the
    clock between 24-hour and AM/PM. `min-time` / `max-time` bound the time
    of any picker with a clock (`mode="time"` or `enable-time`).
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
    'mode' => 'single',
    'enableTime' => false,
    'dateFormat' => null,
    'time24hr' => true,
    'minDate' => null,
    'maxDate' => null,
    'minTime' => null,
    'maxTime' => null,
    'field' => true,
    'disabled' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error ?? $avianUi->errorFor($fieldName, $errorBag);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $wired = $attributes->whereStartsWith('wire:model')->isNotEmpty();
    $inputValue = $value;

    if (! $wired) {
        $inputValue = $avianUi->old($name, $inputValue);
    }

    // `time` is not a flatpickr mode: it is a single picker without the calendar.
    $timeOnly = $mode === 'time';
    $withTime = $timeOnly || $enableTime;
    $format = $dateFormat ?? ($timeOnly ? 'H:i' : 'd/m/Y');

    // A Carbon value (an Eloquent date cast, say) is written in the picker's
    // own format so flatpickr can parse it back; a range joins its two dates
    // with flatpickr's " to " separator.
    $inputValue = $avianUi->formatDate($inputValue, $format, $mode === 'range' ? ' to ' : ', ');
    $minDate = $avianUi->formatDate($minDate, $format);
    $maxDate = $avianUi->formatDate($maxDate, $format);
@endphp

<x-avian-ui::field
    :bare="! $field"
    :label="$label"
    :for="$inputId"
    :hint="$hint"
    :error="$inputError"
    :required="$required"
>
    <input
        data-fp-mode="{{ $timeOnly ? 'single' : $mode }}"
        data-fp-date-format="{{ $format }}"
        @if ($withTime) data-fp-enable-time="true" @endif
        @if ($timeOnly) data-fp-no-calendar="true" @endif
        @if ($withTime && $time24hr) data-fp-time-24hr="true" @endif
        @if ($minDate) data-fp-min-date="{{ $minDate }}" @endif
        @if ($maxDate) data-fp-max-date="{{ $maxDate }}" @endif
        @if ($withTime && $minTime) data-fp-min-time="{{ $minTime }}" @endif
        @if ($withTime && $maxTime) data-fp-max-time="{{ $maxTime }}" @endif
        {{ $attributes->class([
            'aui-input',
            'aui-input-'.$size => filled($size),
            'aui-input-invalid' => filled($inputError),
            'flatpickr-input',
            'aui-timepicker' => $timeOnly,
        ])->merge([
            'type' => 'text',
            'autocomplete' => 'off',
            'name' => $name,
            'id' => $inputId,
            'value' => $inputValue,
            'placeholder' => $placeholder,
            'required' => $required,
            'disabled' => $disabled,
            'aria-invalid' => filled($inputError) ? 'true' : null,
        ]) }}
    >
</x-avian-ui::field>
