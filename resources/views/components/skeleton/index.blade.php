{{--
    A grey placeholder shape shown while content loads — the natural
    companion of a Livewire `placeholder()` for lazy components.

        <x-avian::skeleton />                                  one line of text
        <x-avian::skeleton :lines="3" />                       a paragraph
        <x-avian::skeleton variant="circle" size="40" />       an avatar
        <x-avian::skeleton variant="rect" height="160" />      an image or chart
        <x-avian::skeleton variant="button" />                 a button

    Numbers are pixels; any CSS length works too ("60%", "12rem"). Skeletons
    are hidden from screen readers: wrap a loading region in an element with
    aria-busy="true", or use <x-avian::skeleton.table>, which announces itself.
--}}
@props([
    'variant' => 'text',
    'lines' => 1,
    'width' => null,
    'height' => null,
    'size' => null,
    'animate' => true,
])

@php
    $length = fn (mixed $value): ?string => blank($value) ? null : (is_numeric($value) ? $value.'px' : (string) $value);

    $variant = in_array($variant, ['text', 'circle', 'rect', 'button'], true) ? $variant : 'text';
    $lines = max(1, (int) $lines);

    if ($variant === 'circle') {
        $width = $height = $length($size ?? $width ?? 40);
    } else {
        $width = $length($width);
        $height = $length($height);
    }

    $style = collect(['width' => $width, 'height' => $height])
        ->filter()
        ->map(fn (string $value, string $property): string => $property.': '.$value)
        ->implode('; ');

    $classes = ['aui-skeleton', 'aui-skeleton-'.$variant, 'aui-skeleton-static' => ! $animate];
@endphp

@if ($variant === 'text' && $lines > 1)
    <div {{ $attributes->class(['aui-skeleton-lines'])->merge(['aria-hidden' => 'true']) }} @if ($width) style="width: {{ $width }}" @endif>
        @for ($line = 1; $line <= $lines; $line++)
            {{-- The last line is shorter, the way a paragraph ends. --}}
            <span @class($classes) @if ($height || $line === $lines) style="{{ $line === $lines ? 'width: 60%;' : '' }}{{ $height ? ' height: '.$height : '' }}" @endif></span>
        @endfor
    </div>
@else
    <span {{ $attributes->class($classes)->merge(['aria-hidden' => 'true']) }} @if ($style !== '') style="{{ $style }}" @endif></span>
@endif
