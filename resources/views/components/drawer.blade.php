{{--
    A panel that slides in from the side of the screen, for filters, a quick
    edit form or a record's details without leaving the page behind it.

        <x-avian::drawer name="filters" title="Filters">
            ...
            <x-slot:footer>
                <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
                <x-avian::button wire:click="applyFilters">Apply</x-avian::button>
            </x-slot:footer>
        </x-avian::drawer>

    It is driven by the same Alpine component and browser events as
    `<x-avian::modal>`, so everything that opens a modal opens a drawer:

        <x-avian::button modal="filters">Filters</x-avian::button>
        $this->dispatch('aui-modal-open', name: 'filters');
        window.AvianUI.openDrawer('filters');

    `position` is `right` (default) or `left`; `size` is `sm`, `md`
    (default), `lg` or `xl`. Below 640px the panel takes the full width.
--}}
@props([
    'name' => null,
    'title' => null,
    'subtitle' => null,
    'position' => 'right',
    'size' => 'md',
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

    $side = $position === 'left' ? 'left' : 'right';

    // The dialog is named by its title (and described by its subtitle);
    // `label` names one whose header slot replaces them.
    $idBase = isset($header) ? null : 'aui-drawer-'.\Illuminate\Support\Str::slug($name ?? \Illuminate\Support\Str::random(8));
    $titleId = $idBase !== null && filled($title) ? $idBase.'-title' : null;
    $subtitleId = $idBase !== null && filled($subtitle) ? $idBase.'-subtitle' : null;
@endphp

<div
    x-data="auiModal({{ Illuminate\Support\Js::from($config) }})"
    x-on:keydown.escape.window="escape()"
    x-on:keydown.tab.window="trap($event)"
    x-cloak
    x-show="open"
    {{-- One x-show drives both the backdrop fade and the panel slide (in CSS,
         off these classes): two separate transitions of different lengths
         let the panel snap back into view for a frame on close. --}}
    x-transition:enter="aui-drawer-transition"
    x-transition:enter-start="aui-drawer-closed"
    x-transition:leave="aui-drawer-transition"
    x-transition:leave-end="aui-drawer-closed"
    class="aui-drawer-overlay"
    x-on:click="overlay($event)"
    role="dialog"
    aria-modal="true"
    @if ($titleId) aria-labelledby="{{ $titleId }}" @elseif (filled($label)) aria-label="{{ $label }}" @endif
    @if ($subtitleId) aria-describedby="{{ $subtitleId }}" @endif
    @if (filled($name)) data-modal="{{ $name }}" @endif
>
    <div
        x-ref="panel"
        tabindex="-1"
        {{ $attributes->class(['aui-drawer', 'aui-drawer-'.$side, 'aui-drawer-'.$size => filled($size)]) }}
    >
        @if (filled($title) || filled($subtitle) || isset($header) || $closeable)
            <div class="aui-drawer-header">
                @isset($header)
                    {{ $header }}
                @else
                    <div>
                        @if (filled($title))
                            <h2 class="aui-modal-title" @if ($titleId) id="{{ $titleId }}" @endif>{{ $title }}</h2>
                        @endif

                        @if (filled($subtitle))
                            <p class="aui-drawer-subtitle" @if ($subtitleId) id="{{ $subtitleId }}" @endif>{{ $subtitle }}</p>
                        @endif
                    </div>
                @endisset

                @if ($closeable)
                    <button type="button" class="aui-modal-close" x-on:click="hide()" aria-label="{{ __('avian-ui::messages.close') }}">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                @endif
            </div>
        @endif

        <div class="aui-drawer-body">{{ $slot }}</div>

        @isset($footer)
            <div class="aui-drawer-footer">{{ $footer }}</div>
        @endisset
    </div>
</div>
