{{--
    Lays filter chips out in a wrapping row, with an optional label, hint and
    validation message read for `name` (and `name.*`).
--}}
@props([
    'name' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $baseName = filled($name) ? preg_replace('/\[\]$/', '', (string) $name) : null;
    $groupError = $error
        ?? $avianUi->errorFor($baseName, $errorBag)
        ?? ($baseName !== null ? $avianUi->errorFor($baseName.'.*', $errorBag) : null);
@endphp

<x-avian-ui::field :bare="! $field" :label="$label" :hint="$hint" :error="$groupError">
    <div {{ $attributes->class(['aui-filter-chips'])->merge([
        'role' => 'group',
        'aria-label' => $label,
    ]) }}>
        {{ $slot }}
    </div>
</x-avian-ui::field>
