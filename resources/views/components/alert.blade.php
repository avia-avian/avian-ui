@props([
    'variant' => 'info',
    'title' => null,
    'icon' => null,
    'dismissible' => false,
])

@php
    $defaultIcons = [
        'success' => 'fas fa-circle-check',
        'warning' => 'fas fa-triangle-exclamation',
        'danger' => 'fas fa-circle-exclamation',
        'info' => 'fas fa-circle-info',
        'neutral' => 'fas fa-circle-info',
    ];

    $alertIcon = $icon === false ? null : ($icon ?? ($defaultIcons[$variant] ?? null));
@endphp

<div
    {{ $attributes->class(['aui-alert', 'aui-tone-'.$variant])->merge(['role' => 'alert']) }}
    @if ($dismissible) x-data="auiDismiss()" x-show="visible" @endif
>
    @if (filled($alertIcon))
        <i class="aui-alert-icon {{ $alertIcon }}" aria-hidden="true"></i>
    @endif

    <div class="aui-alert-content">
        @if (filled($title))
            <p class="aui-alert-title">{{ $title }}</p>
        @endif

        {{ $slot }}
    </div>

    @if ($dismissible)
        <button type="button" class="aui-alert-close" x-on:click="dismiss()" aria-label="{{ __('avian-ui::messages.dismiss') }}">
            <i class="fas fa-xmark" aria-hidden="true"></i>
        </button>
    @endif
</div>
