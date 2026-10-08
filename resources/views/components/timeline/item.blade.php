{{--
    One event inside `<x-avian::timeline>`. The slot holds its details.

    `time` takes a string or a date; a date is printed with `time-format`
    (PHP date format), or as "3 hours ago" with `relative`, and is always
    written into the `<time datetime>` attribute for machines to read.
--}}
@props([
    'title' => null,
    'time' => null,
    'timeFormat' => 'M j, Y g:i A',
    'relative' => false,
    'icon' => null,
    'variant' => null,
])

@php
    $datetime = null;
    $timeText = $time;
    $timeTitle = null;

    if ($time instanceof \DateTimeInterface) {
        $date = \Illuminate\Support\Carbon::instance($time);
        $datetime = $date->toIso8601String();
        $timeText = $relative ? $date->diffForHumans() : $date->format($timeFormat);

        // A relative time keeps the exact one a hover away.
        $timeTitle = $relative ? $date->format($timeFormat) : null;
    }
@endphp

<li {{ $attributes->class(['aui-timeline-item', 'aui-tone-'.($variant ?? 'primary')]) }}>
    <span @class(['aui-timeline-marker', 'aui-timeline-marker-icon' => filled($icon)]) aria-hidden="true">
        @if (filled($icon))
            <i class="{{ $icon }}"></i>
        @endif
    </span>

    <div class="aui-timeline-content">
        @if (filled($title) || filled($timeText))
            <div class="aui-timeline-header">
                @if (filled($title))
                    <span class="aui-timeline-title">{{ $title }}</span>
                @endif

                @if (filled($timeText))
                    <time class="aui-timeline-time" @if ($datetime) datetime="{{ $datetime }}" @endif @if ($timeTitle) title="{{ $timeTitle }}" @endif>{{ $timeText }}</time>
                @endif
            </div>
        @endif

        @if ($slot->isNotEmpty())
            <div class="aui-timeline-body">{{ $slot }}</div>
        @endif
    </div>
</li>
