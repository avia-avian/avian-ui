{{--
    A short label shown on hover and on keyboard focus:

        <x-avian::tooltip text="Edit order">
            <x-avian::button variant="light" icon="fas fa-pen" icon-only label="Edit order" />
        </x-avian::tooltip>

        <x-avian::tooltip text="Paid on 4 March" placement="right">
            <x-avian::badge variant="success" dot>Paid</x-avian::badge>
        </x-avian::tooltip>

    `placement` is top, bottom, left or right; the tooltip flips to the other
    side when there is no room. It describes the first focusable element in
    the slot (aria-describedby). With nothing focusable inside, the wrapper
    itself is made focusable so keyboard users can reach it. Rich content
    goes in a `content` slot, but keep it short: use a popover for anything
    interactive.
--}}
@props([
    'text' => null,
    'placement' => 'top',
    'delay' => 150,
    'id' => null,
])

@php
    $placement = in_array($placement, ['top', 'bottom', 'left', 'right'], true) ? $placement : 'top';

    // Derived from the content rather than random, so a Livewire re-render
    // keeps the id that aria-describedby already points at.
    $panelId = $id ?? 'aui-tooltip-'.substr(md5($text.($content ?? '').$slot), 0, 10);
@endphp

<span
    {{ $attributes->class(['aui-tooltip-trigger']) }}
    x-data="auiTooltip({ placement: @js($placement), delay: @js((int) $delay) })"
    x-on:mouseenter="show()"
    x-on:mouseleave="hide()"
    x-on:focusin="show()"
    x-on:focusout="hide()"
    x-on:keydown.escape="hide()"
>
    {{ $slot }}

    <span
        x-ref="panel"
        id="{{ $panelId }}"
        role="tooltip"
        class="aui-tooltip"
        x-cloak
        x-show="open"
        x-bind:class="'aui-tooltip-' + resolved"
        x-bind:style="{ top: top + 'px', left: left + 'px', '--aui-floating-arrow': arrow + 'px' }"
    >{{ $content ?? $text }}</span>
</span>
