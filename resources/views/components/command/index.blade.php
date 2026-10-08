{{--
    A command palette: a keyboard shortcut opens a search box over the page
    to jump to a page, find a record or run an action. Place it once in the
    layout:

        <x-avian::command>
            <x-avian::command.group label="Pages">
                <x-avian::command.item icon="fas fa-box" :href="route('orders.index')" keywords="sales">Orders</x-avian::command.item>
            </x-avian::command.group>
            <x-avian::command.group label="Actions">
                <x-avian::command.item icon="fas fa-plus" modal="create-order">New order</x-avian::command.item>
            </x-avian::command.group>
        </x-avian::command>

    Items are filtered in the browser by their text and `keywords`. With
    `search-model` the query goes to a Livewire property instead, and the
    server renders the matching items:

        <x-avian::command search-model="query">
            @foreach ($this->results as $result)
                <x-avian::command.item :href="$result->url" :hint="$result->type" wire:key="cmd-{{ $result->id }}">{{ $result->title }}</x-avian::command.item>
            @endforeach
        </x-avian::command>

    `shortcut` is "mod+k" by default (Cmd on a Mac, Ctrl elsewhere); set any
    combination, or :shortcut="false" to only open it from code:
    `$dispatch('aui-command-open')`, `window.AvianUI.openCommand()`, or
    `$this->dispatch('aui-command-open')` from Livewire. Give several palettes
    a `name` and pass it along to open a specific one.
--}}
@props([
    'name' => null,
    'shortcut' => 'mod+k',
    'placeholder' => null,
    'emptyText' => null,
    'searchModel' => null,
    'searchDebounce' => '250ms',
    'label' => null,
    'footer' => true,
])

@php
    $listId = 'aui-command-list-'.substr(md5((string) $name.$placeholder), 0, 8);
@endphp

<div
    {{ $attributes->class(['aui-command-root']) }}
    x-data="auiCommand({ name: @js($name), shortcut: @js($shortcut ?: null), server: @js(filled($searchModel)) })"
    x-on:keydown.window="shortcutPressed($event)"
    x-on:aui-command-open.window="openFrom($event)"
    x-on:aui-command-close.window="hide()"
>
    <div class="aui-command-overlay" x-show="open" x-cloak x-on:click.self="hide()">
        <div
            class="aui-command"
            role="dialog"
            aria-modal="true"
            aria-label="{{ $label ?? __('avian-ui::messages.command') }}"
            x-ref="dialog"
            x-on:keydown.escape.prevent.stop="hide()"
            x-on:keydown.tab.prevent="$refs.input.focus()"
        >
            <div class="aui-command-search">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <input
                    type="text"
                    x-ref="input"
                    class="aui-command-input"
                    placeholder="{{ $placeholder ?? __('avian-ui::messages.command_placeholder') }}"
                    role="combobox"
                    aria-expanded="true"
                    aria-controls="{{ $listId }}"
                    aria-autocomplete="list"
                    autocomplete="off"
                    spellcheck="false"
                    @if (filled($searchModel))
                        wire:model.live.debounce.{{ $searchDebounce }}="{{ $searchModel }}"
                    @endif
                    x-on:input="search($event.target.value)"
                    x-on:keydown.down.prevent="move(1)"
                    x-on:keydown.up.prevent="move(-1)"
                    x-on:keydown.home.prevent="moveTo('first')"
                    x-on:keydown.end.prevent="moveTo('last')"
                    x-on:keydown.enter.prevent="chooseActive()"
                >
                <kbd class="aui-kbd aui-kbd-sm">Esc</kbd>
            </div>

            <div class="aui-command-list" id="{{ $listId }}" role="listbox" x-ref="list">
                {{ $slot }}

                <p class="aui-command-empty" x-show="empty" x-cloak role="presentation">
                    {{ $emptyText ?? __('avian-ui::messages.no_results') }}
                </p>
            </div>

            @if ($footer)
                <div class="aui-command-footer" aria-hidden="true">
                    <span><kbd class="aui-kbd aui-kbd-sm">↑</kbd> <kbd class="aui-kbd aui-kbd-sm">↓</kbd> {{ __('avian-ui::messages.command_move') }}</span>
                    <span><kbd class="aui-kbd aui-kbd-sm">Enter</kbd> {{ __('avian-ui::messages.command_choose') }}</span>
                    <span><kbd class="aui-kbd aui-kbd-sm">Esc</kbd> {{ __('avian-ui::messages.close') }}</span>
                </div>
            @endif
        </div>
    </div>
</div>
