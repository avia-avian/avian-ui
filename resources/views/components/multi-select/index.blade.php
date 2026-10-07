{{--
    A searchable select that picks several values at once. Shares its look
    with `<x-avian::searchable-select>`; the picked values show as removable
    chips inside the trigger, and the dropdown stays open while picking.

    Usage:
        <x-avian::multi-select
            name="tags"
            label="Tags"
            :options="['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go']"
            :value="['php']"
        />

    Values submit as `name[]` — one hidden input per pick — so the request
    receives an array (`tags` above arrives as `['php', ...]`). Old input and
    validation messages for both `tags` and `tags.*` are picked up.

    With Livewire, `wire:model` binds the whole array through Alpine's
    `x-modelable`, so every modifier (`.live`, `.blur`, ...) behaves as usual
    and a server-side change shows up in the chips:

        <x-avian::multi-select wire:model.live="tags" :options="$tagOptions" />

    Chip labels come from `options`; a pick missing from them shows its raw
    value. When `options` is a limited query, merge the current picks in
    (with `+`, not `array_merge()`) — see the README.

    Custom option markup: drop `options` and pass children instead —

        <x-avian::multi-select name="users" :value="$userIds">
            @foreach ($users as $user)
                <x-avian::multi-select.option :value="$user->id" :label="$user->name">
                    <strong>{{ $user->name }}</strong> <small>{{ $user->email }}</small>
                </x-avian::multi-select.option>
            @endforeach
        </x-avian::multi-select>

    `clearable` adds a × button to the trigger that removes every pick.

    `taggable` lets the user add values that are not in the list: when the
    search term matches no option label, an `Add "…"` row (see
    `create-text`) — or Enter with no row highlighted — adds the typed text as
    both value and chip label. Backspace in an empty search box takes a typed
    tag back into the box so it can be edited and re-added.

        <x-avian::multi-select name="tags" :options="$tags" taggable clearable />

    The dropdown is teleported to <body> and positioned with fixed
    coordinates, so a card or a scrolling table wrapper cannot clip it.
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'options' => null,
    'label' => null,
    'placeholder' => 'Select options',
    'searchPlaceholder' => 'Search...',
    'emptyText' => 'No results found.',
    'max' => null,
    'clearable' => false,
    'taggable' => false,
    'createText' => 'Add ":term"',
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'size' => null,
    'field' => true,
    'disabled' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $baseName = filled($name) ? preg_replace('/\[\]$/', '', (string) $name) : null;
    $inputName = $baseName !== null ? $baseName . '[]' : null;
    $fieldName = $avianUi->fieldName($baseName, $attributes);

    $inputError = $error
        ?? $avianUi->errorFor($fieldName, $errorBag)
        ?? ($fieldName !== null ? $avianUi->errorFor($fieldName . '.*', $errorBag) : null);
    $inputId = $id ?? ($fieldName !== null ? 'aui-' . str_replace(['[', ']', '.', '_'], '-', $fieldName) : null);

    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $rootAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));
    $wired = $modelAttributes->isNotEmpty();

    $selected = $value;

    if (! $wired) {
        $selected = $avianUi->old($baseName, $selected);
    }

    $selected = collect($selected ?? [])
        ->map(fn ($item) => $avianUi->scalar($item))
        ->filter(fn ($item) => $item !== null && $item !== '')
        ->map(fn ($item) => (string) $item)
        ->unique()
        ->values()
        ->all();

    $optionList = $options === null ? null : (is_array($options) ? $options : collect($options)->all());
    $labels = collect($optionList ?? [])->mapWithKeys(fn ($optionLabel, $optionValue) => [(string) $optionValue => (string) $optionLabel])->all();
@endphp

<x-avian-ui::field :bare="! $field" :label="$label" :for="$inputId" :hint="$hint" :error="$inputError"
    :required="$required">
    {{-- Like searchable-select, per-render state goes through `data-*`
         attributes so the `x-data` expression stays constant across
         Livewire morphs and Alpine never re-initialises the component. --}}
    <div x-data="auiMultiSelect({
        max: @js($max === null ? null : (int) $max),
        taggable: @js((bool) $taggable),
        createText: @js($createText),
    })"
        x-modelable="values"
        {{ $modelAttributes }}
        data-aui-values="{{ json_encode($selected) }}"
        data-aui-labels="{{ json_encode((object) $labels) }}"
        x-ref="wrapper"
        x-on:click.window="if (open && ! $refs.wrapper.contains($event.target) && ! $refs.dropdown.contains($event.target)) close()"
        {{ $rootAttributes->class(['aui-combobox', 'aui-multiselect', 'is-clearable' => $clearable, 'is-disabled' => $disabled]) }}
        :class="{ 'is-open': open }">
        <div id="{{ $inputId }}" x-ref="trigger" role="combobox" tabindex="{{ $disabled ? '-1' : '0' }}"
            class="aui-select aui-combobox-trigger aui-multiselect-trigger{{ $size ? ' aui-select-' . $size : '' }}{{ filled($inputError) ? ' aui-select-invalid' : '' }}"
            x-on:click="toggle()" x-on:keydown.enter.prevent="toggle()" x-on:keydown.space.prevent="toggle()"
            x-on:keydown.down.prevent="if (! open) toggle()"
            @if ($clearable) x-on:keydown.backspace.prevent="clear()" x-on:keydown.delete.prevent="clear()" @endif
            x-bind:aria-expanded="open" aria-haspopup="listbox"
            aria-invalid="{{ filled($inputError) ? 'true' : 'false' }}"
            @if ($disabled) aria-disabled="true" @endif>
            <span class="aui-multiselect-chips">
                <template x-for="item in values" :key="item">
                    <span class="aui-multiselect-chip">
                        <span x-text="labelFor(item)"></span>
                        <button type="button" class="aui-multiselect-chip-remove" x-on:click.stop="remove(item)"
                            x-bind:aria-label="'Remove ' + labelFor(item)">
                            <i class="fas fa-times" aria-hidden="true"></i>
                        </button>
                    </span>
                </template>

                {{-- First-paint fallback, replaced by the chips once Alpine runs. --}}
                <span x-show="false" class="aui-multiselect-fallback">
                    @foreach ($selected as $selectedValue)
                        <span class="aui-multiselect-chip">{{ $labels[$selectedValue] ?? $selectedValue }}</span>
                    @endforeach
                </span>

                <span class="aui-combobox-value is-placeholder" x-show="values.length === 0"
                    @if ($selected !== []) style="display: none" @endif>{{ $placeholder }}</span>
            </span>

            <i class="fas fa-chevron-down aui-combobox-arrow" aria-hidden="true"></i>
        </div>

        @if ($clearable && ! $disabled)
            <button type="button" class="aui-combobox-clear" x-show="values.length" x-cloak
                x-on:click.stop="clear()" aria-label="Clear selection">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        @endif

        @if ($inputName)
            <template x-for="item in values" :key="item">
                <input type="hidden" name="{{ $inputName }}" :value="item">
            </template>
        @endif

        <template x-teleport="body">
            <div x-ref="dropdown" class="aui-combobox-dropdown" x-show="open" x-cloak
                :style="{ top: top + 'px', left: left + 'px', width: width + 'px', maxHeight: maxHeight + 'px' }"
                x-on:keydown.escape.prevent.stop="close(); $refs.trigger.focus()" x-on:keydown.down.prevent="move(1)"
                x-on:keydown.up.prevent="move(-1)" x-on:keydown.enter.prevent="chooseHighlighted()"
                role="listbox" aria-multiselectable="true">
                <div class="aui-combobox-search">
                    <i class="fas fa-search" aria-hidden="true"></i>
                    <input type="text" x-ref="search" class="aui-combobox-search-input"
                        placeholder="{{ $searchPlaceholder }}" x-model="search" x-on:input="filter()"
                        x-on:keydown.backspace="if (search === '' && values.length) { $event.preventDefault(); removeLast(); }">
                    <button type="button" class="aui-multiselect-clear" x-show="values.length" x-on:click="clear()">Clear</button>
                </div>

                <div class="aui-combobox-list" x-ref="list">
                    @if ($optionList)
                        @foreach ($optionList as $optionValue => $optionLabel)
                            <x-avian-ui::multi-select.option :value="$optionValue" :label="$optionLabel" :selected="$selected" />
                        @endforeach
                    @elseif ($slot->isNotEmpty())
                        {{ $slot }}
                    @endif

                    {{-- Kept after the options so arrowing through the list
                         reaches matches first. --}}
                    @if ($taggable)
                        <button type="button" class="aui-combobox-item aui-combobox-create" hidden
                            :hidden="!canCreate" x-on:click="create()" role="option">
                            <span><i class="fas fa-plus" aria-hidden="true"></i> <span x-text="createLabel"></span></span>
                        </button>
                    @endif

                    <p class="aui-combobox-empty" x-ref="empty" @if (filled($optionList) || $slot->isNotEmpty()) hidden @endif>
                        {{ $emptyText }}</p>
                </div>
            </div>
        </template>
    </div>
</x-avian-ui::field>
