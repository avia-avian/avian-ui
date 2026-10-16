{{--
    Alpine is not shipped by this package; this script only registers components
    on it, so it must run before Alpine starts. Livewire loads Alpine at the end
    of the page, so this tag in <head> is early enough on its own. Without
    Livewire, keep your own Alpine tag below this one.

    `editor` also loads the bundled Tiptap that <x-avian::editor> runs on. It is
    a separate file, so pages without an editor never download it.
--}}
@props([
    'editor' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);
@endphp

<script src="{{ $avianUi->scriptUrl() }}" {{ $attributes->merge(['defer' => true]) }}></script>
@if ($editor)
    <script src="{{ $avianUi->editorScriptUrl() }}" {{ $attributes->merge(['defer' => true]) }}></script>
@endif
