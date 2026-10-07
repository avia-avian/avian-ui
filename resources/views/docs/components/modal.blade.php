@php
    $props = [
        ['name', 'string|null', 'null', 'Unique name used to open and close this modal from anywhere.'],
        ['title', 'string|null', 'null', 'Heading in the modal header.'],
        ['size', "'sm'|'lg'|'xl'|null", 'null', 'Modal width. Omit for the default (medium) width.'],
        ['open', 'bool', 'false', 'Render it already open — e.g. to reopen a form after a failed validation.'],
        ['closeable', 'bool', 'true', 'Show the × button in the header.'],
        ['close-on-escape', 'bool', 'true', 'Close when the user presses Esc.'],
        ['close-on-overlay', 'bool', 'true', 'Close when the user clicks the dark backdrop.'],
        ['header (slot)', 'slot', '—', 'Replace the title with your own header markup.'],
        ['footer (slot)', 'slot', '—', 'Right-aligned bar at the bottom, usually Cancel + confirm buttons.'],
    ];

    $examples = [
        [
            'title' => 'Open with a button',
            'text' => 'Give the modal a name and point a button\'s `modal` prop at it. Inside the modal, hide() closes it.',
            'code' => <<<'BLADE'
                <x-avian::button icon="fas fa-plus" modal="create-record">New record</x-avian::button>

                <x-avian::modal name="create-record" title="New record" size="lg">
                    <x-avian::input name="title" label="Title" />

                    <x-slot:footer>
                        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
                        <x-avian::button icon="fas fa-check">Create</x-avian::button>
                    </x-slot:footer>
                </x-avian::modal>
                BLADE,
        ],
        [
            'title' => 'A form inside a modal',
            'text' => 'Wrap the whole modal content in the form and use `form="…"` on the footer button, or simply put the submit button inside the form. Reopen it with `open` when validation fails so the user sees the errors.',
            'code' => <<<'BLADE'
                <x-avian::modal name="create-record" title="New record" :open="$errors->any()">
                    <x-avian::form id="create-record-form" action="{{ route('records.store') }}">
                        <x-avian::input name="title" label="Title" required />
                    </x-avian::form>

                    <x-slot:footer>
                        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
                        <x-avian::button type="submit" form="create-record-form">Create</x-avian::button>
                    </x-slot:footer>
                </x-avian::modal>
                BLADE,
        ],
        [
            'title' => 'Open and close from anywhere',
            'text' => 'Modals listen for browser events, so any code on the page can control them by name.',
            'code' => <<<'BLADE'
                {{-- Alpine --}}
                <button x-on:click="$dispatch('aui-modal-open', { name: 'create-record' })">Open</button>
                <button x-on:click="$dispatch('aui-modal-close', { name: 'create-record' })">Close</button>

                // Plain JavaScript
                AvianUI.openModal('create-record');
                AvianUI.closeModal('create-record');
                AvianUI.closeModal(); // closes every open modal

                // Livewire (PHP)
                $this->dispatch('aui-modal-open', name: 'create-record');
                $this->dispatch('aui-modal-close', name: 'create-record');
                BLADE,
        ],
        [
            'title' => 'Confirmation dialog',
            'text' => 'For destructive actions, keep the modal small, and make it harder to dismiss by accident.',
            'code' => <<<'BLADE'
                <x-avian::modal name="delete-record" title="Delete record?" size="sm" :close-on-overlay="false">
                    This cannot be undone.

                    <x-slot:footer>
                        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
                        <x-avian::form action="{{ route('records.destroy', $record) }}" method="DELETE">
                            <x-avian::button type="submit" variant="danger">Delete</x-avian::button>
                        </x-avian::form>
                    </x-slot:footer>
                </x-avian::modal>
                BLADE,
        ],
        [
            'title' => 'Livewire: close after saving',
            'code' => <<<'BLADE'
                public function save(): void
                {
                    $this->validate();
                    Record::create($this->only('title'));

                    $this->dispatch('aui-modal-close', name: 'create-record');
                }
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Modal', 'subtitle' => 'Dialog over the page'])
<p class="aui-showcase-lead">
    A dialog that opens on top of the page and dims everything behind it. Use it for short, focused
    tasks — a quick create form, a confirmation — that shouldn't need a page of their own.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap">
        <x-avian::button icon="fas fa-plus" modal="doc-default">Default modal</x-avian::button>
        <x-avian::button variant="light" modal="doc-large">Large modal</x-avian::button>
        <x-avian::button variant="danger" icon="fas fa-trash" modal="doc-confirm">Confirm dialog</x-avian::button>
    </div>
</div>

<x-avian::modal name="doc-default" title="New record">
    <x-avian::input name="modal_title" label="Title" />

    <x-slot:footer>
        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
        <x-avian::button icon="fas fa-check" x-on:click="hide()">Create</x-avian::button>
    </x-slot:footer>
</x-avian::modal>

<x-avian::modal name="doc-large" title="Large modal" size="lg">
    <div class="aui-form-grid">
        <x-avian::input name="modal_first_name" label="First name" />
        <x-avian::input name="modal_last_name" label="Last name" />
        <x-avian::textarea class="aui-form-full" name="modal_notes" label="Notes" rows="3" />
    </div>

    <x-slot:footer>
        <x-avian::button variant="light" x-on:click="hide()">Close</x-avian::button>
    </x-slot:footer>
</x-avian::modal>

<x-avian::modal name="doc-confirm" title="Delete record?" size="sm" :close-on-overlay="false">
    This cannot be undone. Clicking the backdrop won't close this one.

    <x-slot:footer>
        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
        <x-avian::button variant="danger" x-on:click="hide()">Delete</x-avian::button>
    </x-slot:footer>
</x-avian::modal>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Each modal listens for the <code>aui-modal-open</code> / <code>aui-modal-close</code> browser events with its <code>name</code>. That is how buttons, Alpine, plain JS and Livewire can all control it.</li>
        <li>While a modal is open the page behind it can't scroll.</li>
        <li>Inside the modal you are in its Alpine scope: <code>hide()</code>, <code>show()</code> and <code>toggle()</code> are available on any element.</li>
        <li>The modal markup can live anywhere in the page — at the bottom of the layout is a good spot. Names must be unique per page.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
