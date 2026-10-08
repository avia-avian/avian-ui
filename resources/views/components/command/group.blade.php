{{--
    A labelled group of <x-avian::command.item>s. It hides itself while a
    search leaves none of its items.
--}}
@props([
    'label' => null,
])

@php
    $groupId = 'aui-command-group-'.substr(md5((string) $label), 0, 8);
@endphp

<div {{ $attributes->class(['aui-command-group']) }} role="group" data-aui-command-group @if (filled($label)) aria-labelledby="{{ $groupId }}" @endif>
    @if (filled($label))
        <div class="aui-command-group-label" id="{{ $groupId }}" role="presentation">{{ $label }}</div>
    @endif

    {{ $slot }}
</div>
