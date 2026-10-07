@php
    $tableUsers = new \Illuminate\Pagination\LengthAwarePaginator(
        items: [
            ['Ada Lovelace', 'Administrator', 'success', 'Active'],
            ['Grace Hopper', 'Editor', 'warning', 'Pending'],
        ],
        total: 42,
        perPage: 2,
        currentPage: 2,
        options: ['path' => '/', 'pageName' => 'page'],
    );

    $sortColumn = in_array(request('sort'), ['name', 'qty', 'price'], true) ? request('sort') : 'name';
    $sortDirection = request('direction') === 'desc' ? 'desc' : 'asc';
    $sortedItems = collect([
        ['name' => 'Wall paint 5L', 'qty' => 2, 'price' => 450000],
        ['name' => 'Wood varnish 1L', 'qty' => 1, 'price' => 120000],
        ['name' => 'Primer 2.5L', 'qty' => 3, 'price' => 270000],
    ])->sortBy($sortColumn, descending: $sortDirection === 'desc');

    $tableOrders = [
        ['INV-1042', 'Ada Lovelace', 'success', 'Paid', 'Rp 1.250.000', [['Wall paint 5L', 2], ['Roller set', 1]]],
        ['INV-1043', 'Grace Hopper', 'warning', 'Pending', 'Rp 480.000', [['Wood varnish 1L', 4]]],
        ['INV-1044', 'Alan Turing', 'danger', 'Overdue', 'Rp 2.900.000', [['Primer 2.5L', 6], ['Masking tape', 10], ['Drop cloth', 2]]],
    ];

    $rowProps = [
        ['details (slot)', 'slot', '—', 'Content of the hidden detail row. Without it the row is a plain row (with an empty toggle cell).'],
        ['expanded', 'bool', 'false', 'Starts the row open.'],
        ['toggle', "'start'|'end'", "'start'", 'Which end of the row the toggle button sits at. Give the table an empty heading there.'],
        ['clickable', 'bool', 'false', 'Clicking anywhere on the row toggles it too. Clicks on links, buttons and form controls inside it are ignored.'],
        ['colspan', 'int|null', 'null', 'Columns the detail cell spans. Defaults to the number of the table\'s headers (or its columns prop).'],
        ['label', 'string|null', "'Show details'", 'Accessible name of the toggle button.'],
    ];

    $props = [
        ['headers', 'array', '[]', "Column headings, in order. Use an empty string for a column without a heading (e.g. actions). An entry can also be ['label' => ..., 'sort' => ..., 'align' => ...] for a sortable or aligned column."],
        ['sort-by', 'string|null', 'null', 'Column currently sorted by. Defaults to the ?sort= query parameter.'],
        ['sort-direction', "'asc'|'desc'|null", 'null', 'Direction of the current sort. Defaults to the ?direction= query parameter.'],
        ['sort-param', 'string', "'sort'", 'Query parameter sort links set to the column key.'],
        ['direction-param', 'string', "'direction'", 'Query parameter sort links set to asc / desc.'],
        ['livewire', 'bool|null', 'null', 'Render sort headings and page links as Livewire buttons. Detected automatically.'],
        ['paginator', 'Paginator|null', 'null', 'A paginate() / simplePaginate() result. Renders <x-avian::pagination> under the table.'],
        ['hover', 'bool', 'true', 'Highlights the row under the mouse.'],
        ['striped', 'bool', 'false', 'Alternating row backgrounds.'],
        ['size', "'sm'|null", 'null', 'Compact rows for dense data.'],
        ['empty', 'string|false|slot|null', 'null', 'Title of the empty state shown when the body slot is empty. false disables it; a named slot replaces it.'],
        ['empty-text', 'string|null', 'null', 'Extra line under the empty-state title.'],
        ['empty-icon', 'string', "'fas fa-inbox'", 'Icon of the empty state.'],
        ['columns', 'int|null', 'null', 'colspan of the empty row. Defaults to the number of headers — set it when you use the head slot.'],
        ['head (slot)', 'slot', '—', 'Replaces the generated header row with your own <tr>(s).'],
        ['foot (slot)', 'slot', '—', 'Rows for a <tfoot>, e.g. totals.'],
    ];

    $livewireSorting = [
        [
            'title' => '1. Keep the sort in component properties',
            'text' => "Inside a Livewire component, sortable headings render as buttons that call sortBy('<column>') instead of links, so nothing goes into the URL. Add a sortBy() method that flips the direction when the same column is clicked again. The column name comes from the browser, so check it against an allowlist.",
            'code' => <<<'PHP'
                use Livewire\Component;
                use Livewire\WithPagination;
                use Livewire\WithoutUrlPagination;

                class UsersTable extends Component
                {
                    use WithPagination, WithoutUrlPagination;

                    public string $sort = 'name';
                    public string $direction = 'asc';

                    public function sortBy(string $column): void
                    {
                        if (! in_array($column, ['name', 'email', 'created_at'], true)) {
                            return;
                        }

                        $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
                        $this->sort = $column;
                        $this->resetPage();
                    }
                }
                PHP,
        ],
        [
            'title' => '2. Apply the sort to the query',
            'text' => 'The table only draws the sort state. Your query does the ordering.',
            'code' => <<<'PHP'
                public function render()
                {
                    return view('livewire.users-table', [
                        'users' => User::orderBy($this->sort, $this->direction)->paginate(15),
                    ]);
                }
                PHP,
        ],
        [
            'title' => '3. Pass the state to the table',
            'text' => 'Always pass sort-by and sort-direction. Otherwise the headings look for ?sort= in the URL, which is empty during a Livewire update, and no column shows as sorted.',
            'code' => <<<'BLADE'
                {{-- resources/views/livewire/users-table.blade.php --}}
                <div>
                    <x-avian::table
                        :headers="[
                            ['label' => 'Name', 'sort' => 'name'],
                            ['label' => 'Email', 'sort' => 'email'],
                            ['label' => 'Joined', 'sort' => 'created_at', 'align' => 'right'],
                        ]"
                        :sort-by="$sort"
                        :sort-direction="$direction"
                        :paginator="$users"
                    >
                        @foreach ($users as $user)
                            <tr wire:key="user-{{ $user->id }}">
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td class="aui-table-align-right">{{ $user->created_at->toDateString() }}</td>
                            </tr>
                        @endforeach
                    </x-avian::table>
                </div>
                BLADE,
        ],
        [
            'title' => 'Optional: keep the sort in the URL',
            'text' => 'Add the #[Url] attribute if a reload or a shared link should keep the sort. The headings stay Livewire buttons either way.',
            'code' => <<<'PHP'
                use Livewire\Attributes\Url;

                #[Url]
                public string $sort = 'name';

                #[Url]
                public string $direction = 'asc';
                PHP,
        ],
    ];

    $examples = [
        [
            'title' => 'Basic table from a query',
            'text' => 'You write the <tr> rows yourself, so each cell can contain anything: badges, avatars, dropdowns.',
            'code' => <<<'BLADE'
                {{-- Controller: $users = User::latest()->paginate(15); --}}

                <x-avian::table :headers="['Name', 'Email', 'Status']" :paginator="$users">
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><x-avian::badge variant="success" dot>{{ $user->status }}</x-avian::badge></td>
                        </tr>
                    @endforeach
                </x-avian::table>
                BLADE,
        ],
        [
            'title' => 'Row actions',
            'text' => 'Give the actions column an empty heading and align it right with .aui-table-align-right (.aui-table-align-center also exists).',
            'code' => <<<'BLADE'
                <x-avian::table :headers="['Name', '']">
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td class="aui-table-align-right">
                                <x-avian::dropdown align="right" size="sm" label="Actions">
                                    <x-avian::dropdown.item :href="route('users.edit', $user)" icon="fas fa-pen">Edit</x-avian::dropdown.item>
                                </x-avian::dropdown>
                            </td>
                        </tr>
                    @endforeach
                </x-avian::table>
                BLADE,
        ],
        [
            'title' => 'Sortable columns',
            'text' => 'Give a header a sort key and it becomes a link that sets ?sort= and ?direction=, flipping the direction on the next click. Apply the sort to your query yourself, allowing only known columns.',
            'code' => <<<'BLADE'
                {{-- Controller:
                    $sort = in_array($request->query('sort'), ['name', 'created_at'], true) ? $request->query('sort') : 'name';
                    $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
                    $users = User::orderBy($sort, $direction)->paginate(15)->withQueryString();
                --}}

                <x-avian::table
                    :headers="[
                        ['label' => 'Name', 'sort' => 'name'],
                        ['label' => 'Joined', 'sort' => 'created_at', 'align' => 'right'],
                    ]"
                    :paginator="$users"
                >
                    ...
                </x-avian::table>

                {{-- In a head slot --}}
                <x-slot:head>
                    <tr>
                        <x-avian::table.heading sort="name">Name</x-avian::table.heading>
                        <x-avian::table.heading align="right">Total</x-avian::table.heading>
                    </tr>
                </x-slot:head>

                {{-- Using Livewire? See "Sorting with Livewire" below. --}}
                BLADE,
        ],
        [
            'title' => 'Empty state',
            'text' => 'When the loop renders no rows, the table shows an empty state across all columns automatically. Customise it with empty / empty-text / empty-icon, or pass a named slot.',
            'code' => <<<'BLADE'
                <x-avian::table
                    :headers="['Name', 'Email']"
                    empty="No users found"
                    empty-text="Try a different search."
                    empty-icon="fas fa-user-slash"
                >
                    @foreach ($users as $user) ... @endforeach
                </x-avian::table>

                {{-- Full control over the empty row --}}
                <x-avian::table :headers="['Name', 'Email']">
                    <x-slot:empty>
                        <x-avian::empty title="No users yet">
                            <x-avian::button :href="route('users.create')">Invite someone</x-avian::button>
                        </x-avian::empty>
                    </x-slot:empty>
                </x-avian::table>
                BLADE,
        ],
        [
            'title' => 'Custom header and totals footer',
            'code' => <<<'BLADE'
                <x-avian::table :columns="3" striped size="sm">
                    <x-slot:head>
                        <tr>
                            <th>Item</th>
                            <th class="aui-table-align-right">Qty</th>
                            <th class="aui-table-align-right">Total</th>
                        </tr>
                    </x-slot:head>

                    @foreach ($order->lines as $line) ... @endforeach

                    <x-slot:foot>
                        <tr>
                            <th colspan="2">Grand total</th>
                            <th class="aui-table-align-right">{{ number_format($order->total) }}</th>
                        </tr>
                    </x-slot:foot>
                </x-avian::table>
                BLADE,
        ],
        [
            'title' => 'Expandable rows',
            'text' => 'Swap <tr> for <x-avian::table.row> and put the hidden content in a details slot. The row adds its own toggle cell, so give the table an empty heading for it.',
            'code' => <<<'BLADE'
                <x-avian::table :headers="['', 'Invoice', 'Customer', 'Total']">
                    @foreach ($invoices as $invoice)
                        <x-avian::table.row wire:key="invoice-{{ $invoice->id }}" clickable>
                            <td>{{ $invoice->number }}</td>
                            <td>{{ $invoice->customer->name }}</td>
                            <td class="aui-table-align-right">{{ $invoice->total }}</td>

                            <x-slot:details>
                                <ul>
                                    @foreach ($invoice->lines as $line)
                                        <li>{{ $line->quantity }} × {{ $line->name }}</li>
                                    @endforeach
                                </ul>
                            </x-slot:details>
                        </x-avian::table.row>
                    @endforeach
                </x-avian::table>

                {{-- Toggle on the right instead, and open by default --}}
                <x-avian::table :headers="['Invoice', 'Customer', '']">
                    <x-avian::table.row toggle="end" expanded>...</x-avian::table.row>
                </x-avian::table>
                BLADE,
        ],
        [
            'title' => 'Inside a card',
            'code' => <<<'BLADE'
                <x-avian::card title="Users" :padded="false">
                    <x-avian::table :headers="['Name', 'Email']" :paginator="$users">...</x-avian::table>
                </x-avian::card>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Table', 'subtitle' => 'Data rows with pagination and an empty state'])
<p class="aui-showcase-lead">
    A styled <code>&lt;table&gt;</code> that scrolls horizontally on small screens, draws its own header
    from a list of column names, renders pagination when you hand it a paginator and shows an empty
    state when there are no rows.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 20px">
        <x-avian::card :padded="false">
            <x-avian::table :headers="['Name', 'Role', 'Status', '']" :paginator="$tableUsers">
                @foreach ($tableUsers as [$name, $role, $variant, $status])
                    <tr>
                        <td>
                            <div class="aui-row">
                                <x-avian::avatar :name="$name" size="sm" />
                                {{ $name }}
                            </div>
                        </td>
                        <td>{{ $role }}</td>
                        <td><x-avian::badge :variant="$variant" dot>{{ $status }}</x-avian::badge></td>
                        <td class="aui-table-align-right">
                            <x-avian::dropdown align="right" size="sm" label="Actions">
                                <x-avian::dropdown.item icon="fas fa-pen">Edit</x-avian::dropdown.item>
                                <div class="aui-dropdown-divider"></div>
                                <x-avian::dropdown.item icon="fas fa-trash" danger>Delete</x-avian::dropdown.item>
                            </x-avian::dropdown>
                        </td>
                    </tr>
                @endforeach
            </x-avian::table>
        </x-avian::card>

        <x-avian::card :padded="false">
            <x-avian::table
                :headers="[
                    ['label' => 'Item', 'sort' => 'name'],
                    ['label' => 'Qty', 'sort' => 'qty', 'align' => 'right'],
                    ['label' => 'Price', 'sort' => 'price', 'align' => 'right'],
                ]"
                :sort-by="$sortColumn"
                :sort-direction="$sortDirection"
                striped
                size="sm"
            >
                @foreach ($sortedItems as $item)
                    <tr>
                        <td>{{ $item['name'] }}</td>
                        <td class="aui-table-align-right">{{ $item['qty'] }}</td>
                        <td class="aui-table-align-right">Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </x-avian::table>
        </x-avian::card>

        <x-avian::card :padded="false">
            <x-avian::table :headers="['', 'Invoice', 'Customer', 'Status', ['label' => 'Total', 'align' => 'right']]" striped>
                @foreach ($tableOrders as [$invoice, $customer, $variant, $status, $total, $lines])
                    <x-avian::table.row :expanded="$loop->first" clickable>
                        <td><strong>{{ $invoice }}</strong></td>
                        <td>{{ $customer }}</td>
                        <td><x-avian::badge :variant="$variant" dot>{{ $status }}</x-avian::badge></td>
                        <td class="aui-table-align-right">{{ $total }}</td>

                        <x-slot:details>
                            <div class="aui-stack" style="gap: 6px">
                                @foreach ($lines as [$item, $qty])
                                    <div class="aui-row aui-row-between">
                                        <span>{{ $item }}</span>
                                        <span class="aui-hint">× {{ $qty }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </x-slot:details>
                    </x-avian::table.row>
                @endforeach
            </x-avian::table>
        </x-avian::card>

        <x-avian::card :padded="false">
            <x-avian::table :headers="['Name', 'Email']" empty="No users found" empty-text="Try a different search." empty-icon="fas fa-user-slash" />
        </x-avian::card>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The table sits in a <code>.aui-table-wrap</code> that scrolls sideways when the columns don't fit, so the page itself never overflows.</li>
        <li>The empty state appears whenever the body slot renders nothing — an empty <code>@@foreach</code> is enough, no <code>@@forelse</code> needed.</li>
        <li>With <code>:paginator</code>, pagination links and a "Showing X to Y of Z" summary appear under the table. They keep the current page URL, so add <code>-&gt;withQueryString()</code> to keep filters.</li>
        <li>Headers with a <code>sort</code> key become links that set <code>?sort=</code> and <code>?direction=</code>. The table only draws the state; your query does the ordering.</li>
        <li>Dropdowns inside rows are positioned so the scroll wrapper doesn't clip them.</li>
        <li><code>&lt;x-avian::table.row&gt;</code> makes a row expandable: its <code>details</code> slot sits in a hidden row underneath that spans every column. Stripes and hover treat the pair as one row.</li>
    </ul>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Sorting with Livewire</h2>
    <p class="aui-showcase-text">
        The table switches to <code>wire:click</code> buttons on its own when it renders inside a Livewire
        component. Pass <code>:livewire="true"</code> to force it, for example from a partial that Livewire
        doesn't render. The same prop switches the pagination links to <code>gotoPage()</code> buttons.
    </p>
    @foreach ($livewireSorting as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Row props (table.row)</h2>
    <div class="aui-showcase-props">
        <x-avian::table :headers="['Prop', 'Type', 'Default', 'Description']" :hover="false">
            @foreach ($rowProps as [$prop, $type, $default, $description])
                <tr data-search-prop="{{ $prop }}">
                    <td><code>{{ $prop }}</code></td>
                    <td><code class="aui-showcase-type">{{ $type }}</code></td>
                    <td><code class="aui-showcase-type">{{ $default }}</code></td>
                    <td>{{ $description }}</td>
                </tr>
            @endforeach
        </x-avian::table>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
