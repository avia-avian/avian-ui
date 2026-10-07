{{--
    A toggleable pill for narrowing a list: "Active", "Overdue", "Mine".
    It is a checkbox underneath, so a group of them submits like one:

        <x-avian::filter-chip.group label="Status">
            <x-avian::filter-chip name="status[]" value="active" label="Active" :count="12" />
            <x-avian::filter-chip name="status[]" value="archived" label="Archived" />
        </x-avian::filter-chip.group>

    `type="radio"` makes the chips in a group exclusive. `wire:model.live`
    is forwarded to the input, so Livewire filters as the chips are toggled.

    Given an `href` it renders a link instead — for filters that live in the
    query string — and `active` marks the current one:

        <x-avian::filter-chip href="?status=active" :active="request('status') === 'active'" label="Active" />
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => '1',
    'type' => 'checkbox',
    'label' => null,
    'checked' => false,
    'icon' => null,
    'count' => null,
    'size' => null,
    'href' => null,
    'active' => false,
    'navigate' => false,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $classes = [
        'aui-filter-chip',
        'aui-filter-chip-'.$size => filled($size),
    ];
@endphp

@if (filled($href))
    <a
        {{ $attributes->class([...$classes, 'is-active' => $active])->merge([
            'href' => $href,
            'aria-current' => $active ? 'true' : null,
            'wire:navigate' => $navigate ? true : null,
        ]) }}
    >
@else
    @php
        $type = $type === 'radio' ? 'radio' : 'checkbox';
        $fieldName = $avianUi->fieldName($name, $attributes);
        $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')).'-'.$value : null);

        $wired = $attributes->whereStartsWith('wire:model')->isNotEmpty();
        $isChecked = $wired ? (bool) $checked : $avianUi->oldChecked($name, $value, (bool) $checked);
    @endphp

    <label @class($classes) @if (filled($inputId)) for="{{ $inputId }}" @endif>
        <input
            {{ $attributes->class(['aui-filter-chip-input'])->merge([
                'type' => $type,
                'name' => $name,
                'id' => $inputId,
                'value' => $value,
                'checked' => $isChecked,
            ]) }}
        >
@endif

        <span class="aui-filter-chip-body">
            <i class="fas fa-check aui-filter-chip-check" aria-hidden="true"></i>

            @if (filled($icon))
                <i class="{{ $icon }} aui-filter-chip-icon" aria-hidden="true"></i>
            @endif

            <span class="aui-filter-chip-label">{{ $label ?? $slot }}</span>

            @if (filled($count))
                <span class="aui-filter-chip-count">{{ $count }}</span>
            @endif
        </span>

@if (filled($href))
    </a>
@else
    </label>
@endif
