{{--
    One label / value pair of <x-avian::description-list>. The value is the
    slot or `value`: a date is formatted with `date-format`, an enum shows its
    label() (or its value), and a blank value shows `empty` instead.
    `copyable` adds a copy button (copying `copy`, or the value's text);
    `full` spans the whole row.
--}}
@props([
    'label' => null,
    'value' => null,
    'dateFormat' => 'M j, Y g:i A',
    'empty' => '—',
    'copyable' => false,
    'copy' => null,
    'full' => false,
])

@php
    $display = match (true) {
        $value instanceof \DateTimeInterface => $value->format($dateFormat),
        $value instanceof \UnitEnum && method_exists($value, 'label') => $value->label(),
        $value instanceof \BackedEnum => $value->value,
        $value instanceof \UnitEnum => $value->name,
        is_bool($value) => $value ? __('avian-ui::messages.yes') : __('avian-ui::messages.no'),
        default => $value,
    };

    $hasSlot = $slot->isNotEmpty();
    $blank = ! $hasSlot && blank($display);

    $copyText = $copy ?? ($hasSlot ? trim(preg_replace('/\s+/', ' ', strip_tags((string) $slot))) : (string) $display);
@endphp

<div {{ $attributes->class(['aui-dl-item', 'aui-dl-full' => $full]) }}>
    <dt class="aui-dl-label">{{ $label }}</dt>
    <dd @class(['aui-dl-value', 'is-empty' => $blank])>
        @if ($blank)
            {{ $empty }}
        @else
            <span class="aui-dl-content">{{ $hasSlot ? $slot : $display }}</span>

            @if ($copyable && $copyText !== '')
                <x-avian-ui::copy-button :text="$copyText" :label="__('avian-ui::messages.copy').(filled($label) ? ' '.$label : '')" size="sm" />
            @endif
        @endif
    </dd>
</div>
