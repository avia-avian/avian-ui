@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'rows' => 4,
    'field' => true,
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
@endphp

<x-avian-ui::field
    :bare="! $field"
    :label="$label"
    :for="$inputId"
    :hint="$hint"
    :error="$inputError"
    :required="$required"
>
    <textarea
        {{ $attributes->class([
            'aui-textarea',
            'aui-textarea-invalid' => filled($inputError),
        ])->merge([
            'name' => $name,
            'id' => $inputId,
            'rows' => $rows,
            'required' => $required,
            'aria-invalid' => filled($inputError) ? 'true' : null,
        ]) }}
    >{{ $inputValue ?? $slot }}</textarea>
</x-avian-ui::field>
