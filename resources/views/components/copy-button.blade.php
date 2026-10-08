{{--
    A small button that copies `text` to the clipboard and says so:

        <x-avian::copy-button :text="$order->number" />
        <x-avian::copy-button :text="$apiKey" label="Copy API key" />

    It uses the Clipboard API, falling back to a hidden textarea on plain-http
    hosts other than localhost. It dispatches `aui-copied` with the text.
--}}
@props([
    'text' => '',
    'label' => null,
    'size' => null,
])

@php
    $label ??= __('avian-ui::messages.copy');
@endphp

<button
    type="button"
    {{ $attributes->class(['aui-copy-button', 'aui-copy-button-'.$size => filled($size)]) }}
    x-data="auiCopy"
    x-on:click="copy($el.dataset.copy)"
    x-bind:class="{ 'is-copied': copied }"
    data-copy="{{ $text }}"
    aria-label="{{ $label }}"
    title="{{ $label }}"
>
    <i class="far fa-copy" x-show="! copied" aria-hidden="true"></i>
    <i class="fas fa-check" x-show="copied" x-cloak aria-hidden="true"></i>
    <span class="aui-sr-only" aria-live="polite" x-text="copied ? @js(__('avian-ui::messages.copied')) : ''"></span>
</button>
