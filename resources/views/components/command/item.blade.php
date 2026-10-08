{{--
    One choice in <x-avian::command>. With `href` it is a link (`navigate`
    adds wire:navigate); `modal` opens that named modal; anything else runs
    its own handlers (`wire:click`, `x-on:click`). Choosing it also dispatches
    `aui-command-select` with its `value` and closes the palette.
    `keywords` are extra words the search matches; `hint` is shown on the
    right, e.g. a record type or a shortcut.
--}}
@props([
    'href' => null,
    'navigate' => false,
    'modal' => null,
    'icon' => null,
    'hint' => null,
    'keywords' => null,
    'value' => null,
])

@php
    $tag = filled($href) ? 'a' : 'button';
@endphp

<{{ $tag }}
    {{ $attributes->class(['aui-command-item'])->merge([
        'type' => $tag === 'button' ? 'button' : null,
        'href' => $href,
    ]) }}
    @if ($navigate && $tag === 'a') wire:navigate @endif
    role="option"
    tabindex="-1"
    aria-selected="false"
    data-aui-command-item
    @if (filled($keywords)) data-keywords="{{ is_array($keywords) ? implode(' ', $keywords) : $keywords }}" @endif
    @if (filled($value)) data-value="{{ $value }}" @endif
    @if (filled($modal)) data-modal="{{ $modal }}" @endif
    x-on:click.capture="chosen($el)"
    x-on:mousemove="highlight($el)"
>
    @if (filled($icon))
        <i class="aui-command-icon {{ $icon }}" aria-hidden="true"></i>
    @endif

    <span class="aui-command-text">{{ $slot }}</span>

    @if (filled($hint))
        <span class="aui-command-hint">{{ $hint }}</span>
    @endif
</{{ $tag }}>
