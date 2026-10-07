@php
    $props = [
        ['title', 'string|null', "'Are you sure?'", 'Default heading, used when a request does not pass one (translated).'],
        ['confirm-text', 'string|null', "'Confirm'", 'Default label of the yes button.'],
        ['cancel-text', 'string|null', "'Cancel'", 'Default label of the no button.'],
        ['variant', "'danger'|'primary'|'warning'|'success'|'info'", "'danger'", 'Default color of the icon and the yes button.'],
        ['loading', 'bool', 'false', 'Keep the dialog open with a spinner after a yes, until the work it started is done. Per request: data-aui-confirm-loading, or `loading` / `action` from JS and Livewire.'],
        ['confirm (button prop)', 'string|null', 'null', 'On <x-avian::button>: the message. The click only goes through after a yes.'],
        ['data-aui-confirm', 'attribute', '—', 'On any element or <form>: the message. Tune it with data-aui-confirm-title, data-aui-confirm-text, data-aui-cancel-text and data-aui-confirm-variant.'],
    ];

    $examples = [
        [
            'title' => '1. Place it once in the layout',
            'text' => 'One dialog serves the whole page, next to the package script.',
            'code' => <<<'BLADE'
                <x-avian::scripts />
                ...
                <x-avian::confirm />
                BLADE,
        ],
        [
            'title' => 'Confirm a button (Livewire, link or submit)',
            'text' => 'The click is held back and replayed after a yes, so wire:click, href and type="submit" keep working as they are.',
            'code' => <<<'BLADE'
                <x-avian::button variant="danger" icon="fas fa-trash" wire:click="delete({{ $order->id }})"
                    confirm="Order {{ $order->number }} will be removed for good."
                    data-aui-confirm-title="Delete order?"
                    data-aui-confirm-text="Yes, delete">
                    Delete
                </x-avian::button>
                BLADE,
        ],
        [
            'title' => 'Loading state',
            'text' => 'With data-aui-confirm-loading the dialog stays open after a yes, spinner on the confirm button, until the Livewire request finishes (or the page navigates away).',
            'code' => <<<'BLADE'
                <x-avian::button variant="danger" wire:click="delete({{ $order->id }})"
                    confirm="Order {{ $order->number }} will be removed for good."
                    data-aui-confirm-loading>
                    Delete
                </x-avian::button>

                // Or from JavaScript: the dialog waits for the promise
                AvianUI.confirm({ message: 'Delete this order?', action: () => $wire.delete(5) });
                BLADE,
        ],
        [
            'title' => 'Confirm a form',
            'code' => <<<'BLADE'
                <form method="POST" action="{{ route('orders.destroy', $order) }}"
                    data-aui-confirm="This cannot be undone." data-aui-confirm-title="Delete order?">
                    @csrf
                    @method('DELETE')
                    <x-avian::button type="submit" variant="danger">Delete</x-avian::button>
                </form>
                BLADE,
        ],
        [
            'title' => 'From Livewire',
            'text' => 'Pass `event` and the dialog dispatches it back on a yes, with `params` as the payload.',
            'code' => <<<'BLADE'
                // Ask
                $this->dispatch('aui-confirm',
                    title: 'Close period?',
                    message: 'Journals in March will be locked.',
                    variant: 'warning',
                    event: 'period-close',
                    params: ['period' => '2026-03'],
                );

                // Act on a yes
                #[On('period-close')]
                public function closePeriod(string $period): void { ... }
                BLADE,
        ],
        [
            'title' => 'From JavaScript',
            'text' => 'A promise that resolves to true or false. Without <x-avian::confirm> on the page it falls back to the native confirm().',
            'code' => <<<'BLADE'
                <button x-data x-on:click="AvianUI.confirm({ message: 'Discard the draft?' }).then(ok => ok && $wire.discard())">
                    Discard
                </button>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Confirm dialog', 'subtitle' => 'Ask before a destructive action'])
<p class="aui-showcase-lead">
    One shared "Are you sure?" dialog. Add <code>confirm="…"</code> to a button (or
    <code>data-aui-confirm</code> to any element or form) and the action only runs after the user agrees.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap" x-data="{ result: 'Nothing done yet.' }">
        <x-avian::button variant="danger" icon="fas fa-trash"
            x-on:click="result = 'Deleted at ' + new Date().toLocaleTimeString()"
            confirm="Order ORD-2026-0042 will be removed for good."
            data-aui-confirm-title="Delete order?"
            data-aui-confirm-text="Yes, delete">Delete order</x-avian::button>

        <x-avian::button variant="light" icon="fas fa-lock"
            x-on:click="AvianUI.confirm({ title: 'Close period?', message: 'Journals in March will be locked.', variant: 'warning', confirmText: 'Close period' }).then(ok => result = ok ? 'Period closed.' : 'Period kept open.')">
            Close period
        </x-avian::button>

        <x-avian::button variant="light" icon="fas fa-paper-plane"
            x-on:click="AvianUI.confirm({ title: 'Send invoice?', message: 'The customer gets an email right away.', variant: 'primary', confirmText: 'Send', action: () => new Promise(done => setTimeout(done, 1500)) }).then(ok => result = ok ? 'Invoice sent.' : 'Invoice not sent.')">
            Send invoice
        </x-avian::button>

        <span x-text="result" style="font-size: 13px; color: var(--aui-text-muted)"></span>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Clicks on <code>[data-aui-confirm]</code> and submits of <code>form[data-aui-confirm]</code> are caught before any other handler, then replayed after a yes.</li>
        <li>The Cancel button gets focus, so a stray Enter never confirms. Esc and a click on the backdrop mean no.</li>
        <li>It sits above modals and drawers, so it can confirm an action inside one without closing it.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
