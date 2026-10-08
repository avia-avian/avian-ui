{{--
    Shows where the user is in a multi-step process:

        <x-avian::stepper :steps="['Cart', 'Shipping', 'Payment', 'Review']" :current="2" />

        <x-avian::stepper :current="3" vertical :steps="[
            ['title' => 'Ordered', 'description' => 'Mar 4, 10:30'],
            ['title' => 'Packed', 'description' => 'Mar 4, 15:02', 'href' => route('orders.packing', $order)],
            ['title' => 'Shipped'],
        ]" />

    A step is a title or an array of title, description, icon, href and
    status. Steps before `current` (1-based) are complete, after it upcoming;
    a step's own `status` (complete, current, upcoming, error) wins. Only
    complete steps follow their `href`. On phones a horizontal stepper keeps
    just the current step's title, so it fits any number of steps.
--}}
@props([
    'steps' => [],
    'current' => 1,
    'vertical' => false,
    'size' => null,
    'label' => null,
])

@php
    $current = (int) $current;

    $items = collect($steps)->values()->map(function (mixed $step, int $index) use ($current): array {
        $step = is_array($step) ? $step : ['title' => $step];
        $number = $index + 1;

        $status = $step['status'] ?? match (true) {
            $number < $current => 'complete',
            $number === $current => 'current',
            default => 'upcoming',
        };

        $status = in_array($status, ['complete', 'current', 'upcoming', 'error'], true) ? $status : 'upcoming';

        return [
            'number' => $number,
            'title' => $step['title'] ?? null,
            'description' => $step['description'] ?? null,
            'icon' => $step['icon'] ?? null,
            'href' => $status === 'complete' ? ($step['href'] ?? null) : null,
            'status' => $status,
        ];
    });
@endphp

<ol {{ $attributes->class([
    'aui-stepper',
    'aui-stepper-vertical' => $vertical,
    'aui-stepper-'.$size => filled($size),
])->merge(['aria-label' => $label ?? __('avian-ui::messages.progress')]) }}>
    @foreach ($items as $step)
        <li class="aui-step is-{{ $step['status'] }}" @if ($step['status'] === 'current') aria-current="step" @endif>
            @if (filled($step['href']))
                <a class="aui-step-inner" href="{{ $step['href'] }}">
            @else
                <div class="aui-step-inner">
            @endif
                <span class="aui-step-marker" aria-hidden="true">
                    @if ($step['status'] === 'complete')
                        <i class="fas fa-check"></i>
                    @elseif ($step['status'] === 'error')
                        <i class="fas fa-xmark"></i>
                    @elseif (filled($step['icon']))
                        <i class="{{ $step['icon'] }}"></i>
                    @else
                        {{ $step['number'] }}
                    @endif
                </span>

                <span class="aui-step-text">
                    <span class="aui-step-title">{{ $step['title'] }}</span>
                    @if (filled($step['description']))
                        <span class="aui-step-description">{{ $step['description'] }}</span>
                    @endif
                </span>
            @if (filled($step['href']))
                </a>
            @else
                </div>
            @endif
        </li>
    @endforeach
</ol>
