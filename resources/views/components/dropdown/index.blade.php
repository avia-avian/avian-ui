@props([
    'align' => 'left',
    'label' => null,
    'variant' => 'light',
    'size' => null,
    'icon' => null,
    'width' => null,
])

<div {{ $attributes->class(['aui-dropdown']) }} x-data="auiDropdown({ align: @js($align) })" x-on:click.outside="hide()" x-on:keydown.escape="escape($event)" x-on:keydown.escape.window="hide()">
    @isset($trigger)
        <div x-ref="trigger" x-on:click="toggle()">{{ $trigger }}</div>
    @else
        <x-avian-ui::button
            :variant="$variant"
            :size="$size"
            :icon="$icon"
            icon-right="fas fa-chevron-down"
            x-ref="trigger"
            x-on:click="toggle()"
            x-bind:aria-expanded="open"
            aria-haspopup="true"
        >{{ $label }}</x-avian-ui::button>
    @endisset

    <div
        x-cloak
        x-show="open"
        x-ref="menu"
        x-on:click="select()"
        x-bind:style="{ top: top + 'px', left: left + 'px' }"
        @class(['aui-dropdown-menu', 'aui-dropdown-menu-right' => $align === 'right'])
        @if (filled($width)) style="min-width: {{ $width }}" @endif
    >
        {{ $slot }}
    </div>
</div>
