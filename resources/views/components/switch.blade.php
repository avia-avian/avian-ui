@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'checked' => false,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error ?? $avianUi->errorFor($fieldName, $errorBag);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $wired = $attributes->whereStartsWith('wire:model')->isNotEmpty();
    $isChecked = $wired ? (bool) $checked : $avianUi->oldChecked($name, $value, (bool) $checked);
@endphp

<x-avian-ui::field :bare="! $field" :hint="$hint" :error="$inputError">
    <label class="aui-switch" @if (filled($inputId)) for="{{ $inputId }}" @endif>
        <input
            {{ $attributes->class(['aui-switch-input'])->merge([
                'type' => 'checkbox',
                'name' => $name,
                'id' => $inputId,
                'value' => $value,
                'checked' => $isChecked,
                'role' => 'switch',
            ]) }}
        >

        <span class="aui-switch-track" aria-hidden="true"></span>

        @if (filled($label) || $slot->isNotEmpty())
            <span class="aui-switch-label">{{ $label ?? $slot }}</span>
        @endif
    </label>
</x-avian-ui::field>
