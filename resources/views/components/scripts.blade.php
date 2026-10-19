{{--
    Alpine is not shipped by this package; this script only registers components
    on it, so it must run before Alpine starts. Livewire loads Alpine at the end
    of the page, so this tag in <head> is early enough on its own. Without
    Livewire, keep your own Alpine tag below this one.

    `editor` also loads the bundled Tiptap that <x-avian::editor> runs on. It is
    a separate file, so pages without an editor never download it. Name the
    add-ons an editor on the page needs, `code` (syntax highlighting) and
    `emoji`, to load those after it:

        <x-avian::scripts editor="code emoji" />
--}}
@props([
    'editor' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $addons = is_string($editor) ? (preg_split('/[\s,]+/', trim($editor), -1, PREG_SPLIT_NO_EMPTY) ?: []) : (is_array($editor) ? $editor : []);
    $addons = array_values(array_intersect($avianUi::EDITOR_ADDONS, $addons));
@endphp

<script src="{{ $avianUi->scriptUrl() }}" {{ $attributes->merge(['defer' => true]) }}></script>
@if ($editor)
    <script src="{{ $avianUi->editorScriptUrl() }}" {{ $attributes->merge(['defer' => true]) }}></script>
    @foreach ($addons as $addon)
        <script src="{{ $avianUi->editorScriptUrl($addon) }}" {{ $attributes->merge(['defer' => true]) }}></script>
    @endforeach
@endif
