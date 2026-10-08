{{--
    A KPI tile: a label, one headline number and, optionally, how it moved.

        <x-avian::stat label="Revenue" value="Rp 128,4 jt" change="+12.5%" description="vs last month" icon="fas fa-wallet" />

    `trend` (`up`, `down` or `flat`) picks the arrow; left out, it is read
    from the sign of `change`. Up is shown as good (green) and down as bad
    (red) — set `invert` for numbers where less is better (costs, returns,
    overdue invoices). `color` tints the icon (`primary` by default, or
    any other shared color: `success`, `warning`, `danger`, `info`, ...).

    `href` turns the whole tile into a link (with `navigate` for
    `wire:navigate`). The default slot renders under the numbers, for a
    progress bar or a small chart.

    Lay several out with the grid helper:

        <div class="aui-grid aui-grid-4">
            <x-avian::stat ... />
        </div>
--}}
@props([
    'label' => null,
    'value' => null,
    'change' => null,
    'trend' => null,
    'invert' => false,
    'description' => null,
    'icon' => null,
    'color' => 'primary',
    'href' => null,
    'navigate' => false,
])

@php
    $direction = $trend;

    if ($direction === null && filled($change)) {
        $changeText = trim((string) $change);
        $direction = match (true) {
            ! preg_match('/[1-9]/', $changeText) => 'flat',
            str_starts_with($changeText, '-'), str_starts_with($changeText, '−') => 'down',
            default => 'up',
        };
    }

    $tone = match ($direction) {
        'up' => $invert ? 'bad' : 'good',
        'down' => $invert ? 'good' : 'bad',
        default => 'flat',
    };

    $arrow = match ($direction) {
        'up' => 'fas fa-arrow-trend-up',
        'down' => 'fas fa-arrow-trend-down',
        default => 'fas fa-minus',
    };

    $tag = filled($href) ? 'a' : 'div';
@endphp

<{{ $tag }}
    {{ $attributes->class(['aui-stat', 'aui-stat-link' => $tag === 'a'])->merge([
        'href' => $href,
        'wire:navigate' => $tag === 'a' && $navigate,
    ]) }}
>
    <div class="aui-stat-main">
        <div class="aui-stat-text">
            @if (filled($label))
                <p class="aui-stat-label">{{ $label }}</p>
            @endif

            <p class="aui-stat-value">{{ $value }}</p>
        </div>

        @if (filled($icon))
            <span class="aui-stat-icon aui-tone-{{ $color }}" aria-hidden="true">
                <i class="{{ $icon }}"></i>
            </span>
        @endif
    </div>

    @if (filled($change) || filled($description) || $trend !== null)
        <p class="aui-stat-meta">
            @if (filled($change) || $trend !== null)
                <span class="aui-stat-change aui-stat-change-{{ $tone }}">
                    <i class="{{ $arrow }}" aria-hidden="true"></i>
                    {{ $change }}
                </span>
            @endif

            @if (filled($description))
                <span class="aui-stat-description">{{ $description }}</span>
            @endif
        </p>
    @endif

    @if ($slot->isNotEmpty())
        <div class="aui-stat-extra">{{ $slot }}</div>
    @endif
</{{ $tag }}>
