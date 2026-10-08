@php
    $props = [
        ['variant', 'string', "'primary'", 'primary, secondary, success, warning, danger, info, neutral, dark, purple, indigo, teal, orange, pink, light, link — or the shapes outline / ghost.'],
        ['color', 'string|null', 'null', 'Only for outline and ghost: primary, secondary, success, warning, danger, info, neutral, dark, purple, indigo, teal, orange or pink. Ignored on other variants.'],
        ['size', "'xs'|'sm'|'lg'|null", 'null', 'Button size. Omit for the default size; xs suits dense table rows.'],
        ['type', 'string', "'button'", 'Native button type: button, submit or reset. Ignored when href is set.'],
        ['href', 'string|null', 'null', 'Renders an <a> instead of a <button>, styled the same way.'],
        ['navigate', 'bool', 'false', 'Adds wire:navigate to an href button (Livewire SPA navigation). Only for links inside your app.'],
        ['icon', 'string|null', 'null', 'Icon class shown before the text (e.g. "fas fa-plus").'],
        ['icon-right', 'string|null', 'null', 'Icon class shown after the text (e.g. "fas fa-arrow-right").'],
        ['icon-only', 'bool', 'false', 'Square button showing only the icon. Always pass `label` with it.'],
        ['label', 'string|null', 'null', 'Accessible name (aria-label) for an icon-only button; also its native title tooltip unless you pass title.'],
        ['rounded', 'bool', 'false', 'Fully rounded corners — a circle for icon-only buttons, a pill otherwise.'],
        ['active', 'bool', 'false', 'Selected state for toggles and segmented button groups.'],
        ['loading', 'bool', 'false', 'Shows a spinner in place of the icon and disables the button.'],
        ['block', 'bool', 'false', 'Full-width button.'],
        ['disabled', 'bool', 'false', 'Disables a <button>; on a link it sets aria-disabled="true".'],
        ['modal', 'string|null', 'null', 'Name of a <x-avian::modal> to open on click. No JavaScript needed.'],
    ];

    $examples = [
        [
            'title' => 'Variants',
            'text' => 'Use one primary button per area for the main action, light or outline for secondary actions and danger for destructive ones.',
            'code' => <<<'BLADE'
                <x-avian::button>Save</x-avian::button>
                <x-avian::button variant="light">Cancel</x-avian::button>
                <x-avian::button variant="danger" icon="fas fa-trash">Delete</x-avian::button>
                <x-avian::button variant="link">Learn more</x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Accent colours',
            'text' => 'Beyond the status colours, dark, purple, indigo, teal, orange and pink are available as solid variants and as a `color` for outline and ghost — handy for categories that are not a status.',
            'code' => <<<'BLADE'
                <x-avian::button variant="dark">Archive</x-avian::button>
                <x-avian::button variant="purple" icon="fas fa-crown">Upgrade</x-avian::button>
                <x-avian::button variant="ghost" color="teal">Export</x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Outline and ghost',
            'text' => 'outline and ghost are shapes, not colours — pair them with `color`. Without it, outline falls back to primary and ghost to secondary.',
            'code' => <<<'BLADE'
                <x-avian::button variant="outline" color="danger">Remove</x-avian::button>
                <x-avian::button variant="ghost" color="success">Approve</x-avian::button>
                <x-avian::button variant="outline" color="purple">Upgrade</x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Submitting a form',
            'text' => 'Buttons default to type="button" so they never submit by accident. Set type="submit" on the one that should.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('records.store') }}">
                    ...
                    <div class="aui-form-actions">
                        <x-avian::button variant="light" type="reset">Reset</x-avian::button>
                        <x-avian::button type="submit" icon="fas fa-check">Save</x-avian::button>
                    </div>
                </x-avian::form>
                BLADE,
        ],
        [
            'title' => 'Links',
            'text' => 'With href the button becomes an <a>. Add `navigate` for Livewire SPA navigation — leave it off for external, mailto:, tel: and #anchor links.',
            'code' => <<<'BLADE'
                <x-avian::button href="{{ route('records.create') }}" icon="fas fa-plus">New record</x-avian::button>
                <x-avian::button href="{{ route('dashboard') }}" navigate>Dashboard</x-avian::button>
                <x-avian::button href="https://laravel.com" target="_blank" variant="link" icon-right="fas fa-arrow-up-right-from-square">Docs</x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Icon-only buttons',
            'text' => 'There is no visible text, so `label` is required — it becomes the aria-label screen readers announce and the native title tooltip. Add `rounded` for a circle, and use xs/sm in dense rows.',
            'code' => <<<'BLADE'
                <x-avian::button icon="fas fa-pen" icon-only label="Edit" variant="light" />
                <x-avian::button icon="fas fa-trash" icon-only label="Delete" variant="ghost" color="danger" />
                <x-avian::button icon="fas fa-plus" icon-only label="Add" rounded />
                <x-avian::button icon="fas fa-ellipsis" icon-only label="More" variant="ghost" size="xs" />
                BLADE,
        ],
        [
            'title' => 'Loading state',
            'text' => 'Pass `loading` while work is in progress. With Livewire, toggle it with wire:loading or bind it to a property.',
            'code' => <<<'BLADE'
                <x-avian::button loading>Saving</x-avian::button>

                {{-- Livewire: disable + show progress while save() runs --}}
                <x-avian::button type="submit" wire:loading.attr="disabled" wire:target="save">
                    <span wire:loading.remove wire:target="save">Save</span>
                    <span wire:loading wire:target="save">Saving…</span>
                </x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Opening a modal',
            'code' => <<<'BLADE'
                <x-avian::button icon="fas fa-plus" modal="create-record">New record</x-avian::button>

                <x-avian::modal name="create-record" title="New record">...</x-avian::modal>
                BLADE,
        ],
        [
            'title' => 'Grouping buttons',
            'text' => 'See Button group & toolbar for segmented controls and action bars.',
            'code' => <<<'BLADE'
                <x-avian::button-group>
                    <x-avian::button variant="light">Cancel</x-avian::button>
                    <x-avian::button>Save</x-avian::button>
                </x-avian::button-group>

                <x-avian::button block>Full width</x-avian::button>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Button', 'subtitle' => 'Actions, links and modal triggers'])
<p class="aui-showcase-lead">
    The button for every action on a page. It renders a <code>&lt;button&gt;</code>, or an
    <code>&lt;a&gt;</code> when you pass <code>href</code>, and supports colours, outline/ghost shapes,
    sizes, icons, a loading state and opening a modal.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <div class="aui-row" style="flex-wrap: wrap">
            @foreach (['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'dark', 'light', 'link'] as $variant)
                <x-avian::button :variant="$variant">{{ ucfirst($variant) }}</x-avian::button>
            @endforeach
        </div>

        <div class="aui-row" style="flex-wrap: wrap">
            @foreach (['neutral', 'purple', 'indigo', 'teal', 'orange', 'pink'] as $variant)
                <x-avian::button :variant="$variant">{{ ucfirst($variant) }}</x-avian::button>
            @endforeach
        </div>

        <div class="aui-row" style="flex-wrap: wrap">
            @foreach (['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'neutral', 'dark', 'purple', 'indigo', 'teal', 'orange', 'pink'] as $color)
                <x-avian::button variant="outline" :color="$color">{{ ucfirst($color) }}</x-avian::button>
            @endforeach
        </div>

        <div class="aui-row" style="flex-wrap: wrap">
            @foreach (['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'neutral', 'dark', 'purple', 'indigo', 'teal', 'orange', 'pink'] as $color)
                <x-avian::button variant="ghost" :color="$color">{{ ucfirst($color) }}</x-avian::button>
            @endforeach
        </div>

        <div class="aui-row" style="flex-wrap: wrap">
            <x-avian::button size="xs">Extra small</x-avian::button>
            <x-avian::button size="sm">Small</x-avian::button>
            <x-avian::button>Default</x-avian::button>
            <x-avian::button size="lg">Large</x-avian::button>
            <x-avian::button icon="fas fa-plus">Icon</x-avian::button>
            <x-avian::button icon-right="fas fa-arrow-right" variant="light">Next</x-avian::button>
            <x-avian::button loading>Saving</x-avian::button>
            <x-avian::button disabled>Disabled</x-avian::button>
        </div>

        <div class="aui-row" style="flex-wrap: wrap">
            <x-avian::button icon="fas fa-pen" icon-only label="Edit" variant="light" />
            <x-avian::button icon="fas fa-trash" icon-only label="Delete" variant="ghost" color="danger" />
            <x-avian::button icon="fas fa-plus" icon-only label="Add" size="sm" />
            <x-avian::button icon="fas fa-check" icon-only label="Approve" size="lg" variant="success" />
            <x-avian::button icon="fas fa-ellipsis" icon-only label="More" size="xs" variant="ghost" />
            <x-avian::button icon="fas fa-plus" icon-only label="Add" rounded />
            <x-avian::button icon="fas fa-heart" icon-only label="Favourite" rounded variant="outline" color="danger" />
            <x-avian::button variant="light" icon="fas fa-star" active>Starred</x-avian::button>
            <x-avian::button rounded variant="light">Pill</x-avian::button>
        </div>

        <x-avian::button block variant="outline">Block button</x-avian::button>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The default <code>type</code> is <code>button</code>, not <code>submit</code> — a button inside a form only submits it when you say so.</li>
        <li><code>loading</code> also disables the button, which prevents double submits.</li>
        <li><code>modal="name"</code> dispatches the <code>aui-modal-open</code> browser event, so the button can sit anywhere on the page, even outside any Alpine component.</li>
        <li>Every other attribute (<code>wire:click</code>, <code>x-on:click</code>, <code>form</code>, <code>target</code>…) is passed to the element.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
