@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => [],
    'placeholder' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'size' => null,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error ?? $avianUi->errorFor($fieldName, $errorBag);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $wired = $attributes->whereStartsWith('wire:model')->isNotEmpty();
    $selected = $value;

    if (! $wired) {
        $selected = $avianUi->old($name, $selected);
    }

    $selected = is_array($selected)
        ? array_map(fn ($item) => (string) $avianUi->scalar($item), $selected)
        : $avianUi->scalar($selected);

    $isSelected = function ($option) use ($selected): bool {
        if (is_array($selected)) {
            return in_array((string) $option, $selected, true);
        }

        return $selected !== null && (string) $option === (string) $selected;
    };
@endphp

<x-avian-ui::field
    :bare="! $field"
    :label="$label"
    :for="$inputId"
    :hint="$hint"
    :error="$inputError"
    :required="$required"
>
    <select
        {{ $attributes->class([
            'aui-select',
            'aui-select-'.$size => filled($size),
            'aui-select-invalid' => filled($inputError),
        ])->merge([
            'name' => $name,
            'id' => $inputId,
            'required' => $required,
            'aria-invalid' => filled($inputError) ? 'true' : null,
        ]) }}
    >
        @if (filled($placeholder))
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($isSelected($optionValue))>{{ $optionLabel }}</option>
        @endforeach

        {{ $slot }}
    </select>
</x-avian-ui::field>
