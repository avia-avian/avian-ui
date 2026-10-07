@php
    $products = new \Illuminate\Pagination\LengthAwarePaginator(
        items: [
            ['Wall paint 5L', 'A-100 · Exterior', 'fas fa-paint-roller', 'Weather-proof acrylic paint for walls and facades. Dries in 2 hours.', 'Rp 450.000', 'success', 'In stock'],
            ['Wood varnish 1L', 'A-200 · Wood care', 'fas fa-brush', 'Clear gloss varnish that protects doors, tables and floors.', 'Rp 120.000', 'warning', 'Low stock'],
            ['Primer 2.5L', 'A-300 · Preparation', 'fas fa-fill-drip', 'Seals new plaster so the top coat covers evenly.', 'Rp 270.000', 'success', 'In stock'],
            ['Roof coating 20L', 'A-400 · Exterior', 'fas fa-house-chimney', 'Heat-reflective coating that keeps roofs cooler.', 'Rp 1.250.000', 'danger', 'Out of stock'],
            ['Anti-rust enamel 1L', 'A-500 · Metal', 'fas fa-shield-halved', 'Rust-stopping enamel for gates, fences and railings.', 'Rp 95.000', 'success', 'In stock'],
            ['Tile grout 2kg', 'A-600 · Interior', 'fas fa-border-all', 'Mould-resistant grout for bathrooms and kitchens.', 'Rp 60.000', 'success', 'In stock'],
        ],
        total: 48,
        perPage: 6,
        currentPage: 1,
        options: ['path' => '/', 'pageName' => 'page'],
    );

    $props = [
        ['paginator', 'Paginator|null', 'null', '<x-avian::datalist>: a paginate() / simplePaginate() result. Renders <x-avian::pagination> under the items.'],
        ['view', "'list'|'grid'", "'list'", '<x-avian::datalist>: layout shown on first load.'],
        ['columns', 'int', '3', '<x-avian::datalist>: cards per row in grid view, 1 to 4. Drops to 2 below 1024px and 1 below 640px.'],
        ['toggle', 'bool', 'true', '<x-avian::datalist>: show the list/grid switch. Set :toggle="false" for a fixed layout.'],
        ['persist', 'string|null', 'null', '<x-avian::datalist>: localStorage key that remembers the viewer\'s choice across page loads.'],
        ['empty', 'string|false|slot|null', 'null', '<x-avian::datalist>: empty-state title when there are no items. false disables it; a named slot replaces it.'],
        ['empty-text', 'string|null', 'null', '<x-avian::datalist>: line under the empty-state title.'],
        ['empty-icon', 'string', "'fas fa-inbox'", '<x-avian::datalist>: icon of the empty state.'],
        ['toolbar (slot)', 'slot', '—', '<x-avian::datalist>: content on the left of the toggle — a search box, filters, a count.'],
        ['title', 'string|null', 'null', '<x-avian::datalist.item>: main text of the item.'],
        ['subtitle', 'string|null', 'null', '<x-avian::datalist.item>: muted line under the title.'],
        ['image', 'string|null', 'null', '<x-avian::datalist.item>: image URL — a thumbnail in list view, the card cover in grid view.'],
        ['icon', 'string|null', 'null', '<x-avian::datalist.item>: icon shown in the media area when there is no image.'],
        ['href', 'string|null', 'null', '<x-avian::datalist.item>: makes the whole item clickable (actions stay separately clickable).'],
        ['navigate', 'bool', 'false', '<x-avian::datalist.item>: adds wire:navigate to the href link.'],
        ['media / meta / actions (slots)', 'slot', '—', '<x-avian::datalist.item>: custom media (e.g. an avatar), details such as price or status, and buttons.'],
    ];

    $examples = [
        [
            'title' => 'Basic paginated datalist',
            'text' => 'Same pattern as the table: pass the paginator, loop over it, render one item per record.',
            'code' => <<<'BLADE'
                {{-- Controller: $products = Product::latest()->paginate(12); --}}

                <x-avian::datalist :paginator="$products">
                    @foreach ($products as $product)
                        <x-avian::datalist.item
                            :title="$product->name"
                            :subtitle="$product->sku"
                            :image="$product->image_url"
                            :href="route('products.show', $product)"
                        >
                            {{ $product->summary }}
                        </x-avian::datalist.item>
                    @endforeach
                </x-avian::datalist>
                BLADE,
        ],
        [
            'title' => 'Start in grid view and remember the choice',
            'text' => 'persist stores the last picked layout in the browser under the key you give, so each list can remember its own.',
            'code' => <<<'BLADE'
                <x-avian::datalist :paginator="$products" view="grid" :columns="4" persist="products">
                    ...
                </x-avian::datalist>
                BLADE,
        ],
        [
            'title' => 'Meta and actions',
            'text' => 'meta holds small details (price, status, date); actions holds buttons or a dropdown. In list view they sit on the right, in grid view in the card footer.',
            'code' => <<<'BLADE'
                <x-avian::datalist.item :title="$product->name" :subtitle="$product->sku" icon="fas fa-box">
                    <x-slot:meta>
                        <strong>{{ Number::currency($product->price, 'IDR') }}</strong>
                        <x-avian::badge :variant="$product->stock_variant" dot size="sm">{{ $product->stock_label }}</x-avian::badge>
                    </x-slot:meta>

                    <x-slot:actions>
                        <x-avian::dropdown align="right" size="sm" label="Actions">
                            <x-avian::dropdown.item :href="route('products.edit', $product)" icon="fas fa-pen">Edit</x-avian::dropdown.item>
                        </x-avian::dropdown>
                    </x-slot:actions>
                </x-avian::datalist.item>
                BLADE,
        ],
        [
            'title' => 'People list with avatars',
            'text' => 'Use the media slot for anything other than an image or icon.',
            'code' => <<<'BLADE'
                <x-avian::datalist :paginator="$users" :columns="4">
                    @foreach ($users as $user)
                        <x-avian::datalist.item :title="$user->name" :subtitle="$user->email">
                            <x-slot:media>
                                <x-avian::avatar :name="$user->name" :src="$user->avatar_url" size="lg" />
                            </x-slot:media>
                        </x-avian::datalist.item>
                    @endforeach
                </x-avian::datalist>
                BLADE,
        ],
        [
            'title' => 'Toolbar with a search box',
            'code' => <<<'BLADE'
                <x-avian::datalist :paginator="$products">
                    <x-slot:toolbar>
                        <x-avian::form method="GET" action="{{ route('products.index') }}">
                            <x-avian::input name="q" :value="request('q')" icon="fas fa-search" placeholder="Search products" :field="false" />
                        </x-avian::form>
                        <span class="aui-hint">{{ $products->total() }} products</span>
                    </x-slot:toolbar>
                    ...
                </x-avian::datalist>

                // Keep the search term in the page links
                $products = Product::search($request->q)->paginate(12)->withQueryString();
                BLADE,
        ],
        [
            'title' => 'Empty state',
            'text' => 'When the loop renders no items, an empty state appears automatically. Customise it like the table\'s.',
            'code' => <<<'BLADE'
                <x-avian::datalist :paginator="$products" empty="No products found" empty-text="Try a different search." empty-icon="fas fa-box-open">
                    @foreach ($products as $product) ... @endforeach
                </x-avian::datalist>

                <x-avian::datalist>
                    <x-slot:empty>
                        <x-avian::empty title="No products yet">
                            <x-avian::button :href="route('products.create')" icon="fas fa-plus">Add product</x-avian::button>
                        </x-avian::empty>
                    </x-slot:empty>
                </x-avian::datalist>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'Bind the layout to a property with wire:model; the server can read it or change it. Pagination works with the WithPagination trait as usual.',
            'code' => <<<'BLADE'
                <x-avian::datalist wire:model.live="view" :paginator="$products">...</x-avian::datalist>

                // Livewire component
                use WithPagination;

                #[Url]
                public string $view = 'grid';
                BLADE,
        ],
        [
            'title' => 'Reacting to a layout change',
            'code' => <<<'BLADE'
                <x-avian::datalist x-on:aui-view-changed="console.log($event.detail.view)">...</x-avian::datalist>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Datalist', 'subtitle' => 'Paginated items as a list or a grid of cards'])
<p class="aui-showcase-lead">
    The table's sibling for records that read better as cards than as rows: products, files, people,
    projects. It takes a paginator like the table does, shows an empty state when there is nothing to
    show, and lets the viewer switch between a compact <strong>list</strong> and a visual
    <strong>grid</strong> — no page reload.
</p>

<div class="aui-showcase-demo">
    <x-avian::datalist :paginator="$products" view="grid" persist="showcase-products">
        <x-slot:toolbar>
            <x-avian::input name="datalist_search" icon="fas fa-search" placeholder="Search products" :field="false" size="sm" />
            <span class="aui-hint">48 products</span>
        </x-slot:toolbar>

        @foreach ($products as [$name, $sku, $icon, $summary, $price, $stockVariant, $stockLabel])
            <x-avian::datalist.item :title="$name" :subtitle="$sku" :icon="$icon" href="#">
                {{ $summary }}

                <x-slot:meta>
                    <strong style="color: var(--aui-heading)">{{ $price }}</strong>
                    <x-avian::badge :variant="$stockVariant" dot size="sm">{{ $stockLabel }}</x-avian::badge>
                </x-slot:meta>

                <x-slot:actions>
                    <x-avian::button icon="fas fa-pen" icon-only label="Edit {{ $name }}" size="sm" variant="light" />
                </x-slot:actions>
            </x-avian::datalist.item>
        @endforeach
    </x-avian::datalist>
</div>

<div class="aui-showcase-demo" style="margin-top: 16px">
    <x-avian::label>People, list view, avatars in the media slot</x-avian::label>
    <x-avian::datalist :columns="4">
        @foreach (['Ada Lovelace' => 'Administrator', 'Grace Hopper' => 'Editor', 'Alan Turing' => 'Viewer', 'Linus Torvalds' => 'Editor'] as $person => $role)
            <x-avian::datalist.item :title="$person" :subtitle="$role">
                <x-slot:media><x-avian::avatar :name="$person" /></x-slot:media>
            </x-avian::datalist.item>
        @endforeach
    </x-avian::datalist>
</div>

<div class="aui-showcase-demo" style="margin-top: 16px">
    <x-avian::label>No items</x-avian::label>
    <x-avian::datalist empty="No products found" empty-text="Try a different search." empty-icon="fas fa-box-open" />
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Every item is rendered once; the toggle only switches a class on the container, so changing the layout is instant and makes no request.</li>
        <li>The same <code>&lt;x-avian::datalist.item&gt;</code> becomes a row in list view and a card in grid view — media, text, meta and actions move into place by themselves.</li>
        <li>With <code>href</code> the whole row or card is clickable, while buttons in the <code>actions</code> slot still work on their own.</li>
        <li>The empty state appears whenever the loop renders nothing, and the pagination behaves exactly like the table's (add <code>-&gt;withQueryString()</code> to keep filters).</li>
        <li>The toggle needs Alpine and <code>&lt;x-avian::scripts /&gt;</code>. Without them the list still renders, in its initial <code>view</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
