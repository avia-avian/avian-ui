@php
    $props = [
        ['name', 'string|null', 'null', 'Name of the hidden input that carries the selected value.'],
        ['options', 'array|Collection|null', 'null', 'value => label pairs. Leave it out (null) to write the options yourself in the slot.'],
        ['value', 'mixed', 'null', 'Selected value. Falls back to old input when wire:model is not used.'],
        ['placeholder', 'string', "'Select an option'", 'Trigger text when nothing is selected.'],
        ['search-placeholder', 'string', "'Search...'", 'Placeholder of the search box inside the dropdown.'],
        ['empty-text', 'string', "'No results found.'", 'Shown when the search matches nothing.'],
        ['search-model', 'string|null', 'null', 'Livewire only: property that receives the search term, so the server filters `options`.'],
        ['search-debounce', 'string', "'250ms'", 'Debounce for the search-model request.'],
        ['clearable', 'bool', 'false', 'Adds a × button (and Backspace / Delete on the trigger) that resets the value.'],
        ['taggable', 'bool', 'false', 'Lets the user pick a typed value that is not in the list; reopening prefills the search box with the current label so it can be edited.'],
        ['create-text', 'string', "'Add \":term\"'", 'Row text offering the typed value in taggable mode; `:term` is replaced.'],
        ['label', 'string|null', 'null', 'Label shown above the control.'],
        ['hint', 'string|null', 'null', 'Helper text under the control.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label. (A hidden input cannot be natively required — validate on the server.)'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height.'],
        ['disabled', 'bool', 'false', 'Disables the trigger so the dropdown cannot open.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the control.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'text' => 'Same API as the native select. Filtering happens in the browser against the option labels.',
            'code' => <<<'BLADE'
                <x-avian::searchable-select
                    name="country"
                    label="Country"
                    placeholder="Choose a country"
                    :options="['us' => 'United States', 'id' => 'Indonesia', 'jp' => 'Japan']"
                />
                BLADE,
        ],
        [
            'title' => 'Options from the database',
            'text' => 'A Collection is accepted as-is, no ->all() needed.',
            'code' => <<<'BLADE'
                <x-avian::searchable-select
                    name="customer_id"
                    label="Customer"
                    :options="$customers->pluck('name', 'id')"
                    :value="old('customer_id', $order->customer_id)"
                />
                BLADE,
        ],
        [
            'title' => 'Custom option markup',
            'text' => 'Drop `options` and render <x-avian::searchable-select.option> rows yourself. `label` is the plain text shown in the trigger and used for searching; the slot is what the row looks like. Pass the current value to `selected` so the right row is highlighted on first paint.',
            'code' => <<<'BLADE'
                <x-avian::searchable-select name="item_no" label="Item" :value="$itemNo">
                    @foreach ($items as $item)
                        <x-avian::searchable-select.option :value="$item->id" :label="$item->name" :selected="$itemNo">
                            <strong>{{ $item->code }}</strong> <small>{{ $item->name }}</small>
                        </x-avian::searchable-select.option>
                    @endforeach
                </x-avian::searchable-select>
                BLADE,
        ],
        [
            'title' => 'Clearable and taggable',
            'text' => '`clearable` adds a × button that empties the value. `taggable` offers whatever the user typed as a new value when no option label matches it; a tagged value is its own label. Reopening prefills the search box with the current label, selected, so it can be edited or typed over.',
            'code' => <<<'BLADE'
                <x-avian::searchable-select
                    name="city"
                    label="City"
                    :options="$cities"
                    :value="old('city', $address->city)"
                    clearable
                    taggable
                    create-text="Use &quot;:term&quot;"
                />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'Bind with wire:model — any modifier works. Also pass :value so the trigger label is correct on the first render.',
            'code' => <<<'BLADE'
                <x-avian::searchable-select
                    wire:model.live="filter.status"
                    :value="$filter['status']"
                    :options="$statusOptions"
                    label="Status"
                />
                BLADE,
        ],
        [
            'title' => 'Server-side search (Livewire, large tables)',
            'text' => 'For thousands of rows, don\'t render them all. Give `search-model` a property; the search box writes to it (debounced) and your component returns only the matching options. The selected label stays visible even when the search filters it out.',
            'code' => <<<'BLADE'
                {{-- Blade --}}
                <x-avian::searchable-select
                    wire:model.live="customerId"
                    :value="$customerId"
                    :options="$this->customerOptions"
                    search-model="customerSearch"
                    label="Customer"
                />

                // Livewire component
                public ?int $customerId = null;
                public string $customerSearch = '';

                #[Computed]
                public function customerOptions(): array
                {
                    return Customer::where('name', 'like', "%{$this->customerSearch}%")
                        ->limit(50)
                        ->pluck('name', 'id')
                        ->all();
                }
                BLADE,
        ],
        [
            'title' => 'Default selection with server-side search',
            'text' => 'The trigger label comes from `options`, so a default (or saved) value that your query does not return, because it is past the limit or not the first match, shows the placeholder. Labels are cached in the browser, so the option only needs to appear once: add it to the front while the search term is blank. Merge with `+`, not array_merge(), which renumbers integer ids. When the user types, the pinned row goes away but the trigger keeps its label.',
            'code' => <<<'BLADE'
                {{-- Blade --}}
                <x-avian::searchable-select
                    wire:model.live="customerId"
                    :value="$customerId"
                    :options="$this->customerOptions"
                    search-model="customerSearch"
                    label="Customer"
                />

                // Livewire component
                public ?int $customerId = null;
                public string $customerSearch = '';

                public function mount(): void
                {
                    $this->customerId ??= auth()->user()->default_customer_id;
                }

                #[Computed]
                public function customerOptions(): array
                {
                    $options = Customer::query()
                        ->when($this->customerSearch, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
                        ->orderBy('name')
                        ->limit(20)
                        ->pluck('name', 'id')
                        ->all();

                    if ($this->customerId && blank($this->customerSearch) && ! array_key_exists($this->customerId, $options)) {
                        $options = [$this->customerId => Customer::find($this->customerId)?->name] + $options;
                    }

                    return $options;
                }
                BLADE,
        ],
        [
            'title' => 'Default selection in slot mode (custom rows)',
            'text' => 'For custom row markup, or when you would rather not mix the default into the query results, render the selected row yourself ahead of the loop and skip it inside the loop. Each row\'s wire:key is derived from its value, so rendering the same value twice confuses Livewire. Without `options`, the server cannot name the selection, so on a full page load the trigger shows the placeholder until Alpine starts and the rows register their labels. If you want the label in the server-rendered HTML, use the `options` version above.',
            'code' => <<<'BLADE'
                {{-- Blade --}}
                <x-avian::searchable-select
                    wire:model.live="customerId"
                    :value="$customerId"
                    search-model="customerSearch"
                    label="Customer"
                >
                    @if ($this->selectedCustomer && blank($customerSearch))
                        <x-avian::searchable-select.option :value="$customerId" :label="$this->selectedCustomer->name" :selected="$customerId">
                            <strong>{{ $this->selectedCustomer->code }}</strong> <small>{{ $this->selectedCustomer->name }}</small>
                        </x-avian::searchable-select.option>
                    @endif

                    @foreach ($this->customers as $customer)
                        @continue($customer->id == $customerId && blank($customerSearch))
                        <x-avian::searchable-select.option :value="$customer->id" :label="$customer->name" :selected="$customerId">
                            <strong>{{ $customer->code }}</strong> <small>{{ $customer->name }}</small>
                        </x-avian::searchable-select.option>
                    @endforeach
                </x-avian::searchable-select>

                // Livewire component
                public ?int $customerId = null;
                public string $customerSearch = '';

                public function mount(): void
                {
                    $this->customerId ??= auth()->user()->default_customer_id;
                }

                #[Computed]
                public function selectedCustomer(): ?Customer
                {
                    return $this->customerId ? Customer::find($this->customerId) : null;
                }

                #[Computed]
                public function customers(): Collection
                {
                    return Customer::query()
                        ->when($this->customerSearch, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
                        ->orderBy('name')
                        ->limit(20)
                        ->get();
                }
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Searchable select', 'subtitle' => 'Dropdown with a search box'])
<p class="aui-showcase-lead">
    A replacement for the native select when the list is too long to scroll through: countries,
    customers, products. It behaves like <code>&lt;x-avian::select&gt;</code> — same props, same
    validation and old input — and submits a single value through a hidden input.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::searchable-select
            name="searchable_country"
            label="Country"
            placeholder="Choose a country"
            :options="['us' => 'United States', 'id' => 'Indonesia', 'jp' => 'Japan', 'de' => 'Germany', 'fr' => 'France', 'br' => 'Brazil', 'au' => 'Australia', 'ca' => 'Canada']"
        />
        <x-avian::searchable-select
            name="searchable_city"
            label="City"
            value="sby"
            hint="Pre-selected through the value prop."
            :options="['jkt' => 'Jakarta', 'sby' => 'Surabaya', 'bdg' => 'Bandung', 'mdn' => 'Medan']"
        />
        <x-avian::searchable-select name="searchable_item" label="Item (custom rows)" placeholder="Choose an item">
            @foreach (['A-100' => 'Wall paint 5L', 'A-200' => 'Wood varnish 1L', 'A-300' => 'Primer 2.5L'] as $code => $itemName)
                <x-avian::searchable-select.option :value="$code" :label="$itemName">
                    <strong>{{ $code }}</strong>&nbsp;<small>{{ $itemName }}</small>
                </x-avian::searchable-select.option>
            @endforeach
        </x-avian::searchable-select>
        <x-avian::searchable-select
            name="searchable_district"
            label="District (clearable, taggable)"
            value="mgl"
            hint="Type a district that is not listed to add it."
            clearable
            taggable
            :options="['mgl' => 'Menteng', 'kby' => 'Kebayoran Baru', 'tbt' => 'Tebet', 'cpt' => 'Cempaka Putih']"
        />
        <x-avian::searchable-select
            name="searchable_owner"
            label="Owner"
            required
            error="Please choose an owner."
            :options="['ada' => 'Ada Lovelace', 'grace' => 'Grace Hopper']"
        />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Click the trigger (or focus it and press Enter) to open. Type to filter, use ↑ / ↓ to move, Enter to pick and Esc to close.</li>
        <li>With <code>clearable</code>, the × button or Backspace / Delete on the focused trigger clears the value.</li>
        <li>With <code>taggable</code>, a term that matches no label shows an <em>Add "…"</em> row (↓ then Enter, or click). The typed text is submitted as the value.</li>
        <li>The chosen value is written to a hidden <code>&lt;input name="…"&gt;</code>, so it submits like a normal field.</li>
        <li>The dropdown is teleported to <code>&lt;body&gt;</code>, so it is never clipped by a card, modal or scrolling table.</li>
        <li>Requires Alpine and the package script (<code>&lt;x-avian::scripts /&gt;</code>) loaded before Alpine.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
