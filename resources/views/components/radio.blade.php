@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'checked' => false,
    'inline' => false,
    'field' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error ?? ($field ? $avianUi->errorFor($fieldName, $errorBag) : null);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')).'-'.trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $value), '-') : null);

    $wired = $attributes->whereStartsWith('wire:model')->isNotEmpty();
    $isChecked = $wired ? (bool) $checked : $avianUi->oldChecked($name, $value, (bool) $checked);
@endphp

<x-avian-ui::field :bare="! $field" :error="$inputError">
    <label @class(['aui-check', 'aui-check-inline' => $inline]) @if (filled($inputId)) for="{{ $inputId }}" @endif>
        <input
            {{ $attributes->class(['aui-check-input'])->merge([
                'type' => 'radio',
                'name' => $name,
                'id' => $inputId,
                'value' => $value,
                'checked' => $isChecked,
            ]) }}
        >

        @if (filled($label) || filled($hint) || $slot->isNotEmpty())
            <span class="aui-check-body">
                <span class="aui-check-label">{{ $label ?? $slot }}</span>

                @if (filled($hint))
                    <span class="aui-check-hint">{{ $hint }}</span>
                @endif
            </span>
        @endif
    </label>
</x-avian-ui::field>
