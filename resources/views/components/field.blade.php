@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'bare' => false,
])

@if ($bare)
    {{ $slot }}
@else
    <div {{ $attributes->class(['aui-field']) }}>
        @if (filled($label))
            <label @class(['aui-label', 'aui-label-required' => $required])@if (filled($for)) for="{{ $for }}"@endif>{{ $label }}</label>
        @endif

        {{ $slot }}

        @if (filled($hint))
            <span class="aui-hint"@if (filled($for)) id="{{ $for }}-hint"@endif>{{ $hint }}</span>
        @endif

        @if (filled($error))
            <span class="aui-error"@if (filled($for)) id="{{ $for }}-error"@endif>{{ $error }}</span>
        @endif
    </div>
@endif
