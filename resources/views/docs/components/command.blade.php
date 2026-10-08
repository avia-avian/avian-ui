@php
    $props = [
        ['shortcut', 'string|false', "'mod+k'", 'Opens and closes it. mod is Cmd on a Mac and Ctrl elsewhere; e.g. "ctrl+shift+p". false for none.'],
        ['name', 'string|null', 'null', 'Open a specific palette when a page has several.'],
        ['placeholder', 'string|null', "'Search or jump to…'", 'Search box placeholder.'],
        ['empty-text', 'string|null', "'No data found'", 'Shown when nothing matches.'],
        ['search-model', 'string|null', 'null', 'Livewire property the query is bound to; the server then renders the items.'],
        ['search-debounce', 'string', "'250ms'", 'Debounce for search-model.'],
        ['label', 'string|null', "'Command palette'", 'Accessible name of the dialog.'],
        ['footer', 'bool', 'true', 'Keyboard hints under the list.'],
        ['item: href', 'string|null', 'null', 'Makes the item a link.'],
        ['item: navigate', 'bool', 'false', 'Adds wire:navigate to the link.'],
        ['item: modal', 'string|null', 'null', 'Opens that named modal.'],
        ['item: icon', 'string|null', 'null', 'Icon class.'],
        ['item: hint', 'string|null', 'null', 'Muted text on the right: a record type, a shortcut.'],
        ['item: keywords', 'string|array|null', 'null', 'Extra words the search matches.'],
        ['item: value', 'string|null', 'null', 'Sent with aui-command-select.'],
    ];

    $examples = [
        [
            'title' => 'Pages and actions in the layout',
            'code' => <<<'BLADE'
                {{-- resources/views/layouts/app.blade.php --}}
                <x-avian::command>
                    <x-avian::command.group label="Pages">
                        <x-avian::command.item icon="fas fa-gauge" :href="route('dashboard')" navigate>Dashboard</x-avian::command.item>
                        <x-avian::command.item icon="fas fa-box" :href="route('orders.index')" keywords="sales invoices" navigate>Orders</x-avian::command.item>
                    </x-avian::command.group>

                    <x-avian::command.group label="Actions">
                        <x-avian::command.item icon="fas fa-plus" modal="create-order">New order</x-avian::command.item>
                        <x-avian::command.item icon="fas fa-right-from-bracket" x-on:click="$refs.logout.submit()">Sign out</x-avian::command.item>
                    </x-avian::command.group>
                </x-avian::command>
                BLADE,
        ],
        [
            'title' => 'Searching records with Livewire',
            'code' => <<<'BLADE'
                {{-- livewire/global-search.blade.php --}}
                <div>
                    <x-avian::command search-model="query" placeholder="Search orders and customers">
                        @foreach ($this->results as $result)
                            <x-avian::command.item :href="$result['url']" :hint="$result['type']" :icon="$result['icon']" wire:key="cmd-{{ $result['key'] }}">
                                {{ $result['title'] }}
                            </x-avian::command.item>
                        @endforeach
                    </x-avian::command>
                </div>

                // GlobalSearch.php
                public string $query = '';

                #[Computed]
                public function results(): array
                {
                    if (strlen($this->query) < 2) {
                        return [];
                    }

                    return Order::search($this->query)->take(5)->get()->map(fn ($order) => [...])->all();
                }
                BLADE,
        ],
        [
            'title' => 'Opening it from a button',
            'code' => <<<'BLADE'
                <x-avian::button variant="light" icon="fas fa-magnifying-glass" x-on:click="$dispatch('aui-command-open')">
                    Search <x-avian::kbd keys="Ctrl+K" size="sm" />
                </x-avian::button>

                // Livewire
                $this->dispatch('aui-command-open');

                // Plain JavaScript
                window.AvianUI.openCommand();
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Command palette', 'subtitle' => 'Jump anywhere from the keyboard'])
<p class="aui-showcase-lead">
    A search box over the page, opened with a shortcut, to jump to a page, find a record or run an
    action. Put it once in the layout.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap; gap: 12px; align-items: center">
        <x-avian::button variant="light" icon="fas fa-magnifying-glass" x-data x-on:click="$dispatch('aui-command-open', { name: 'docs-demo' })">
            Open the palette
        </x-avian::button>
        <span>or press <x-avian::kbd keys="Ctrl+K" /> (<x-avian::kbd keys="⌘+K" /> on a Mac)</span>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li><kbd class="aui-kbd">↑</kbd> <kbd class="aui-kbd">↓</kbd> move, <kbd class="aui-kbd">Enter</kbd> chooses, <kbd class="aui-kbd">Esc</kbd> closes. Focus stays in the search box and returns to where it was on close.</li>
        <li>Choosing an item clicks it: links navigate, <code>wire:click</code> and <code>x-on:click</code> run, <code>modal</code> opens that modal. It then dispatches <code>aui-command-select</code> and closes.</li>
        <li>Without <code>search-model</code>, items are filtered in the browser: every word typed must appear in the item's text or <code>keywords</code>. Empty groups hide.</li>
        <li>Open it from code with <code>$dispatch('aui-command-open')</code>, <code>window.AvianUI.openCommand()</code> or a Livewire <code>dispatch('aui-command-open')</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
