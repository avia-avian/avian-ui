{{--
    One step of <x-avian::wizard>. The title and description label the step
    in the wizard's header; the slot is the step's content.
--}}
@props([
    'title' => null,
    'description' => null,
])

<section
    {{ $attributes->class(['aui-wizard-step']) }}
    data-aui-wizard-step
    data-title="{{ $title }}"
    @if (filled($description)) data-description="{{ $description }}" @endif
    tabindex="-1"
    @if (filled($title)) aria-label="{{ $title }}" @endif
    x-show="isActive($el)"
>
    {{ $slot }}
</section>
