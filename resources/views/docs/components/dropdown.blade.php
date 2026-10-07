@php
    $props = [
        ['label', 'string|null', 'null', '<x-avian::dropdown>: text of the default trigger button.'],
        ['align', "'left'|'right'", "'left'", '<x-avian::dropdown>: which edge of the trigger the menu lines up with. Use right near the right side of the screen.'],
        ['variant', 'string', "'light'", '<x-avian::dropdown>: button variant of the default trigger.'],
        ['size', "'sm'|'lg'|null", 'null', '<x-avian::dropdown>: size of the default trigger.'],
        ['icon', 'string|null', 'null', '<x-avian::dropdown>: icon of the default trigger.'],
        ['width', 'string|null', 'null', '<x-avian::dropdown>: minimum menu width, any CSS length (e.g. "220px").'],
        ['trigger (slot)', 'slot', '—', '<x-avian::dropdown>: replace the default button with your own trigger (avatar, icon button…).'],
        ['href', 'string|null', 'null', '<x-avian::dropdown.item>: renders a link instead of a button.'],
        ['icon', 'string|null', 'null', '<x-avian::dropdown.item>: icon before the text.'],
        ['danger', 'bool', 'false', '<x-avian::dropdown.item>: red text for destructive actions.'],
        ['type', 'string', "'button'", '<x-avian::dropdown.item>: button type when there is no href (use submit inside a form).'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::dropdown label="Actions">
                    <x-avian::dropdown.item :href="route('records.edit', $record)" icon="fas fa-pen">Edit</x-avian::dropdown.item>
                    <x-avian::dropdown.item icon="fas fa-copy" wire:click="duplicate({{ $record->id }})">Duplicate</x-avian::dropdown.item>
                    <div class="aui-dropdown-divider"></div>
                    <x-avian::dropdown.item icon="fas fa-trash" danger x-on:click="$dispatch('aui-modal-open', { name: 'delete-record' })">
                        Delete
                    </x-avian::dropdown.item>
                </x-avian::dropdown>
                BLADE,
        ],
        [
            'title' => 'Deleting through a form',
            'text' => 'Links can only GET. For a DELETE, wrap the item in a form and make it a submit button.',
            'code' => <<<'BLADE'
                <x-avian::dropdown align="right" size="sm" label="Actions">
                    <x-avian::form action="{{ route('records.destroy', $record) }}" method="DELETE"
                        onsubmit="return confirm('Delete this record?')">
                        <x-avian::dropdown.item type="submit" icon="fas fa-trash" danger>Delete</x-avian::dropdown.item>
                    </x-avian::form>
                </x-avian::dropdown>
                BLADE,
        ],
        [
            'title' => 'Custom trigger (user menu)',
            'code' => <<<'BLADE'
                <x-avian::dropdown align="right" width="220px">
                    <x-slot:trigger>
                        <button type="button" class="aui-row" aria-label="Account menu">
                            <x-avian::avatar :name="auth()->user()->name" size="sm" />
                        </button>
                    </x-slot:trigger>

                    <x-avian::dropdown.item :href="route('profile')" icon="fas fa-user">Profile</x-avian::dropdown.item>
                    <x-avian::form action="{{ route('logout') }}">
                        <x-avian::dropdown.item type="submit" icon="fas fa-right-from-bracket">Log out</x-avian::dropdown.item>
                    </x-avian::form>
                </x-avian::dropdown>
                BLADE,
        ],
        [
            'title' => 'Icon-only trigger in a table row',
            'code' => <<<'BLADE'
                <x-avian::dropdown align="right">
                    <x-slot:trigger>
                        <x-avian::button icon="fas fa-ellipsis" icon-only label="Row actions" variant="ghost" size="sm" />
                    </x-slot:trigger>
                    ...
                </x-avian::dropdown>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Dropdown', 'subtitle' => 'A menu of actions behind one button'])
<p class="aui-showcase-lead">
    A button that opens a small menu. Use it to group secondary actions (Edit, Duplicate, Delete) —
    especially in table rows, where there is no room for several buttons.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap; gap: 16px; min-height: 60px">
        <x-avian::dropdown label="Actions">
            <x-avian::dropdown.item icon="fas fa-pen">Edit</x-avian::dropdown.item>
            <x-avian::dropdown.item icon="fas fa-copy">Duplicate</x-avian::dropdown.item>
            <div class="aui-dropdown-divider"></div>
            <x-avian::dropdown.item icon="fas fa-trash" danger>Delete</x-avian::dropdown.item>
        </x-avian::dropdown>

        <x-avian::dropdown label="Export" variant="primary" icon="fas fa-download">
            <x-avian::dropdown.item href="#" icon="fas fa-file-excel">Excel</x-avian::dropdown.item>
            <x-avian::dropdown.item href="#" icon="fas fa-file-pdf">PDF</x-avian::dropdown.item>
        </x-avian::dropdown>

        <x-avian::dropdown label="Small" size="sm">
            <x-avian::dropdown.item>First</x-avian::dropdown.item>
            <x-avian::dropdown.item>Second</x-avian::dropdown.item>
        </x-avian::dropdown>

        <x-avian::dropdown width="220px">
            <x-slot:trigger>
                <x-avian::button icon="fas fa-ellipsis" icon-only label="More actions" variant="ghost" />
            </x-slot:trigger>
            <x-avian::dropdown.item icon="fas fa-share">Share</x-avian::dropdown.item>
            <x-avian::dropdown.item icon="fas fa-box-archive">Archive</x-avian::dropdown.item>
        </x-avian::dropdown>

        <div style="margin-left: auto">
            <x-avian::dropdown align="right" label="Aligned right">
                <x-avian::dropdown.item icon="fas fa-user">Profile</x-avian::dropdown.item>
                <x-avian::dropdown.item icon="fas fa-right-from-bracket">Log out</x-avian::dropdown.item>
            </x-avian::dropdown>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Click the trigger to open; clicking an item, clicking outside or pressing Esc closes it.</li>
        <li>The menu is positioned with fixed coordinates and follows scrolling, so tables and cards never clip it.</li>
        <li>Items pass extra attributes through, so <code>wire:click</code> and <code>x-on:click</code> work as on any button. To open a modal from an item, dispatch <code>aui-modal-open</code> (see the Basic example).</li>
        <li>Separate groups of items with <code>&lt;div class="aui-dropdown-divider"&gt;&lt;/div&gt;</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
