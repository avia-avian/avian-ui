{{--
    A keyboard key, or a shortcut of several keys:

        Press <x-avian::kbd>Esc</x-avian::kbd> to close.
        <x-avian::kbd keys="Ctrl+K" />
        <x-avian::kbd :keys="['⌘', 'Shift', 'P']" size="sm" />

    `keys` splits on "+" and joins the keys back with `separator`.
--}}
@props([
    'keys' => null,
    'size' => null,
    'separator' => '+',
])

@php
    $list = is_array($keys)
        ? array_values(array_filter($keys, fn (mixed $key): bool => filled($key)))
        : (filled($keys) ? array_values(array_filter(array_map('trim', explode('+', (string) $keys)), 'filled')) : []);
@endphp

@if ($list !== [])
    <span {{ $attributes->class(['aui-kbd-group', 'aui-kbd-'.$size => filled($size)]) }}>
        @foreach ($list as $key)
            <kbd class="aui-kbd">{{ $key }}</kbd>
            @unless ($loop->last)
                <span class="aui-kbd-separator" aria-hidden="true">{{ $separator }}</span>
            @endunless
        @endforeach
    </span>
@else
    <kbd {{ $attributes->class(['aui-kbd', 'aui-kbd-'.$size => filled($size)]) }}>{{ $slot }}</kbd>
@endif
