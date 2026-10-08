{{--
    An `icon-only` button renders as a square button with no visible text —
    pass `label` so it still gets an accessible name (there is no visible
    text for assistive tech to read otherwise):

        <x-avian::button icon="fas fa-pen" icon-only label="Edit" />

    The label doubles as the native `title` tooltip unless you pass your own.
    Add `rounded` for a circular icon button (or a pill-shaped text one).

    `active` marks the selected button in a segmented
    `<x-avian::button-group attached>` (a view switcher, a filter):

        <x-avian::button variant="light" active>List</x-avian::button>

    `navigate` adds `wire:navigate` to an `href` button for Livewire's
    SPA-style page swap. It is opt-in rather than automatic whenever `href`
    is set — an external link, a `mailto:`/`tel:` link or an on-page `#anchor`
    would break under `wire:navigate`, so only ask for it on same-app links:

        <x-avian::button href="{{ route('dashboard') }}" navigate>Dashboard</x-avian::button>

    `outline` and `ghost` are shapes, not colors on their own — pair either
    with `color` (any shared color: `primary`, `secondary`, `success`,
    `warning`, `danger`, `info`, `neutral`, `dark`, `purple`, `indigo`,
    `teal`, `orange`, `pink`) to pick one. Every color also works as a solid
    variant. Without `color` they fall back to `primary` (outline) or
    `secondary` (ghost); `color` is ignored on every other variant, which is
    already a color (`primary`, `success`, ...) or `light` / `link`:

        <x-avian::button variant="outline" color="danger">Remove</x-avian::button>
        <x-avian::button variant="ghost" color="success">Approve</x-avian::button>

    `confirm` holds the click back behind `<x-avian::confirm>` (placed once
    in the layout) and replays it once the user agrees, so `wire:click`,
    `href` and form submits behave as usual after a yes. Tune the dialog with
    `data-aui-confirm-title`, `data-aui-confirm-text` (the yes button),
    `data-aui-cancel-text` and `data-aui-confirm-variant`:

        <x-avian::button variant="danger" wire:click="delete({{ $id }})" confirm="Delete this order?">Delete</x-avian::button>
--}}
@props([
    'variant' => 'primary',
    'color' => null,
    'size' => null,
    'type' => 'button',
    'href' => null,
    'navigate' => false,
    'icon' => null,
    'iconRight' => null,
    'iconOnly' => false,
    'label' => null,
    'loading' => false,
    'block' => false,
    'rounded' => false,
    'active' => false,
    'disabled' => false,
    'modal' => null,
    'confirm' => null,
])

@php
    $tag = filled($href) ? 'a' : 'button';

    // A link cannot be disabled natively, so a disabled one drops its href and
    // leaves the tab order instead.
    $inert = $tag === 'a' && ($disabled || $loading);

    /*
     * Alpine only initialises trees rooted at x-data, so a trigger that lives
     * outside every component needs its own empty scope before $dispatch works.
     */
    $opens = filled($modal)
        ? '$dispatch(\'aui-modal-open\', { name: '.Illuminate\Support\Js::from($modal).' })'
        : null;

    // `outline` and `ghost` are shapes painted with `color`; `light` and `link`
    // are styles of their own; any other variant is a color for a solid button.
    [$shape, $tone] = match ($variant) {
        'outline' => ['outline', $color ?? 'primary'],
        'ghost' => ['ghost', $color ?? 'secondary'],
        'light', 'link' => [$variant, null],
        default => ['solid', $variant],
    };

    $classes = [
        'aui-btn',
        'aui-btn-'.$shape,
        'aui-tone-'.$tone => filled($tone),
        'aui-btn-'.$size => filled($size),
        'aui-btn-icon' => $iconOnly,
        'aui-btn-block' => $block,
        'aui-btn-rounded' => $rounded,
        'aui-btn-active' => $active,
        'aui-btn-loading' => $loading,
    ];
@endphp

<{{ $tag }}
    {{ $attributes->class($classes)->merge([
        'type' => $tag === 'button' ? $type : null,
        'href' => $inert ? null : $href,
        'wire:navigate' => $tag === 'a' && ! $inert && $navigate,
        'disabled' => $tag === 'button' && ($disabled || $loading),
        'aria-disabled' => $inert ? 'true' : null,
        'tabindex' => $inert ? '-1' : null,
        'aria-label' => $iconOnly ? $label : null,
        'title' => $iconOnly ? $label : null,
        'x-data' => $opens === null ? null : '{}',
        'x-on:click' => $opens,
        'data-aui-confirm' => filled($confirm) ? $confirm : null,
    ]) }}
>
    @if ($loading)
        <span class="aui-spinner aui-spinner-sm" aria-hidden="true"></span>
    @elseif (filled($icon))
        <i class="{{ $icon }}" aria-hidden="true"></i>
    @endif

    @unless ($iconOnly)
        {{ $slot }}

        @if (filled($iconRight))
            <i class="{{ $iconRight }}" aria-hidden="true"></i>
        @endif
    @endunless
</{{ $tag }}>
