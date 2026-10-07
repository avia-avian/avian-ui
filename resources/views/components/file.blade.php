@props([
    'name' => null,
    'id' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'trigger' => 'Choose file',
    'placeholder' => 'No file chosen',
    'icon' => 'fas fa-paperclip',
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $error ?? $avianUi->errorFor($fieldName, $errorBag);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);
@endphp

<x-avian-ui::field
    :bare="! $field"
    :label="$label"
    :for="$inputId"
    :hint="$hint"
    :error="$inputError"
    :required="$required"
>
    <div class="aui-file" x-data="auiFile({ placeholder: @js($placeholder) })">
        <input
            x-ref="input"
            x-on:change="update($event)"
            {{ $attributes->class(['aui-file-input'])->merge([
                'type' => 'file',
                'name' => $name,
                'id' => $inputId,
                'required' => $required,
                'aria-invalid' => filled($inputError) ? 'true' : null,
            ]) }}
        >

        <button type="button" class="aui-file-trigger" x-on:click="browse()">
            @if (filled($icon))
                <i class="{{ $icon }}" aria-hidden="true"></i>
            @endif
            {{ $trigger }}
        </button>

        <span class="aui-file-name" x-text="label">{{ $placeholder }}</span>
    </div>
</x-avian-ui::field>
