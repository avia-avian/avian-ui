@props([
    'name' => null,
    'title' => null,
    'size' => null,
    'open' => false,
    'closeOnEscape' => true,
    'closeOnOverlay' => true,
    'closeable' => true,
    'label' => null,
])

@php
    $config = [
        'name' => $name,
        'open' => (bool) $open,
        'closeOnEscape' => (bool) $closeOnEscape,
        'closeOnOverlay' => (bool) $closeOnOverlay,
    ];

    // The dialog is named by its title; `label` names one whose header slot
    // replaces the title.
    $titleId = filled($title) && ! isset($header) ? 'aui-modal-'.\Illuminate\Support\Str::slug($name ?? \Illuminate\Support\Str::random(8)).'-title' : null;
@endphp

<div
    x-data="auiModal({{ Illuminate\Support\Js::from($config) }})"
    x-on:keydown.escape.window="escape()"
    x-on:keydown.tab.window="trap($event)"
    x-cloak
    x-show="open"
    class="aui-modal-overlay"
    x-on:click="overlay($event)"
    role="dialog"
    aria-modal="true"
    @if ($titleId) aria-labelledby="{{ $titleId }}" @elseif (filled($label)) aria-label="{{ $label }}" @endif
    @if (filled($name)) data-modal="{{ $name }}" @endif
>
    <div x-ref="panel" tabindex="-1" {{ $attributes->class(['aui-modal', 'aui-modal-'.$size => filled($size)]) }}>
        @if (filled($title) || isset($header) || $closeable)
            <div class="aui-modal-header">
                @isset($header)
                    {{ $header }}
                @else
                    <h2 class="aui-modal-title" @if ($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h2>
                @endisset

                @if ($closeable)
                    <button type="button" class="aui-modal-close" x-on:click="hide()" aria-label="{{ __('avian-ui::messages.close') }}">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        @endif

        <div class="aui-modal-body">{{ $slot }}</div>

        @isset($footer)
            <div class="aui-modal-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>
