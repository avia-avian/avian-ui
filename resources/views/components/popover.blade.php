{{--
    A small floating panel of rich content, opened by clicking its trigger:

        <x-avian::popover title="Filters" width="280px">
            <x-slot:trigger>
                <x-avian::button variant="light" icon="fas fa-filter">Filters</x-avian::button>
            </x-slot:trigger>

            <x-avian::select name="status" label="Status" :options="$statuses" />

            <x-slot:footer>
                <x-avian::button size="sm" x-on:click="hide()">Apply</x-avian::button>
            </x-slot:footer>
        </x-avian::popover>

    A click outside or Esc closes it (Esc hands focus back to the trigger);
    `hide()` closes it from anything inside. Opened with the keyboard, focus
    moves to the first control in the panel. It dispatches `aui-popover-open`
    and `aui-popover-close`. Use a dropdown for a list of actions and a modal
    for anything that needs the user's full attention.
--}}
@props([
    'placement' => 'bottom',
    'title' => null,
    'width' => null,
    'open' => false,
    'id' => null,
])

@php
    $placement = in_array($placement, ['top', 'bottom', 'left', 'right'], true) ? $placement : 'bottom';
    $panelId = $id ?? 'aui-popover-'.substr(md5($title.($trigger ?? '').$slot), 0, 10);
@endphp

<div
    {{ $attributes->class(['aui-popover-wrap']) }}
    x-data="auiPopover({ placement: @js($placement), open: @js((bool) $open) })"
    x-on:click.outside="hide()"
    x-on:keydown.escape="if (open) { $event.stopPropagation(); hide(true) }"
>
    <div
        x-ref="trigger"
        class="aui-popover-trigger"
        x-on:click="toggle($event)"
        data-aui-controls="{{ $panelId }}"
    >{{ $trigger ?? '' }}</div>

    <div
        x-ref="panel"
        id="{{ $panelId }}"
        role="dialog"
        tabindex="-1"
        @if (filled($title)) aria-labelledby="{{ $panelId }}-title" @endif
        class="aui-popover"
        x-cloak
        x-show="open"
        x-bind:class="'aui-popover-' + resolved"
        x-bind:style="{ top: top + 'px', left: left + 'px', '--aui-floating-arrow': arrow + 'px' }"
        @if (filled($width)) style="width: {{ $width }}" @endif
    >
        @if (filled($title))
            <div class="aui-popover-header">
                <strong id="{{ $panelId }}-title">{{ $title }}</strong>
                <button type="button" class="aui-popover-close" x-on:click="hide(true)" aria-label="{{ __('avian-ui::messages.close') }}">
                    <i class="fas fa-xmark" aria-hidden="true"></i>
                </button>
            </div>
        @endif

        <div class="aui-popover-body">{{ $slot }}</div>

        @isset($footer)
            <div class="aui-popover-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>
