@props([
    'value' => 0,
    'max' => 100,
    'variant' => null,
    'label' => null,
    'showValue' => false,
])

@php
    $max = (float) $max > 0 ? (float) $max : 100.0;
    $percent = max(0, min(100, round(((float) $value / $max) * 100, 2)));
@endphp

<div {{ $attributes }}>
    @if (filled($label) || $showValue)
        <div class="aui-progress-meta">
            <span>{{ $label }}</span>
            @if ($showValue)
                <span>{{ (int) $percent }}%</span>
            @endif
        </div>
    @endif

    <div class="aui-progress" role="progressbar" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100">
        <div class="aui-progress-bar aui-tone-{{ $variant ?? 'primary' }}" style="width: {{ $percent }}%"></div>
    </div>
</div>
