{{--
    One row of `<x-avian::searchable-select>`'s dropdown. Rendered
    automatically for every entry of the parent's `options`, or by hand when
    custom markup is needed inside the row:

        <x-avian::searchable-select.option :value="$item->id" :label="$item->name" :selected="$selectedId">
            <strong>{{ $item->id }}</strong> <small>{{ $item->name }}</small>
        </x-avian::searchable-select.option>

    `label` is the plain text the trigger shows once this row is picked — the
    slot is only what the dropdown draws. On init the row registers its label
    with the parent (`remember`), which is how the trigger keeps naming the
    current selection even after a search filters this row out of the list.
    `data-label` is what the parent's client-side filter (no `search-model`
    given) matches against; it is unused, harmless, when filtering is handed
    to the server instead.

    `wire:key` gives Livewire's morph a stable identity per value, so a row
    for a value that was not on screen before arrives as a new element and
    gets initialised instead of being patched in place — this is what keeps
    `remember()` accurate for a server-filtered (`search-model`) list.

    `selected` is the currently selected value, and only drives the
    server-rendered `active` class — Alpine keeps the class honest between
    interactions on its own.
--}}
@props([
    'value' => null,
    'label' => null,
    'selected' => null,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    // Enums (`:value="$status"`, `:selected="$order->status"`) compare by value.
    $value = $avianUi->scalar($value);
    $selected = $avianUi->scalar($selected);
@endphp

<button
    type="button"
    wire:key="aui-combobox-option-{{ $value }}"
    class="aui-combobox-item{{ $selected !== null && (string) $selected === (string) $value ? ' active' : '' }}"
    data-label="{{ $label }}"
    :class="{ active: isSelected(@js((string) $value)) }"
    x-init="remember(@js((string) $value), @js((string) $label))"
    x-on:click="choose(@js((string) $value), @js((string) $label))"
    role="option"
    {{ $attributes }}
>
    <span>{{ $slot->isNotEmpty() ? $slot : $label }}</span>
    <i class="fas fa-check aui-combobox-item-check" aria-hidden="true"></i>
</button>
