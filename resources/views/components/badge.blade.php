@props([
    'variant' => 'neutral',
    'size' => null,
    'icon' => null,
    'dot' => false,
    'uppercase' => false,
])

<span {{ $attributes->class([
    'aui-badge',
    'aui-tone-'.$variant,
    'aui-badge-'.$size => filled($size),
    'aui-badge-dot' => $dot,
    'aui-badge-uppercase' => $uppercase,
]) }}>
    @if (filled($icon))
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif

    {{ $slot }}
</span>
