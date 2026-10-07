@php
    $pageDemo = new \Illuminate\Pagination\LengthAwarePaginator(
        items: range(1, 10),
        total: 240,
        perPage: 10,
        currentPage: 7,
        options: ['path' => '/', 'pageName' => 'page'],
    );

    $simpleDemo = new \Illuminate\Pagination\Paginator(
        items: range(1, 11),
        perPage: 10,
        currentPage: 2,
        options: ['path' => '/', 'pageName' => 'page'],
    );

    $props = [
        ['paginator', 'Paginator|null', 'null', 'Result of paginate() or simplePaginate(). Nothing renders when there is only one page.'],
        ['on-each-side', 'int', '1', 'How many page numbers to show on each side of the current page.'],
    ];

    $examples = [
        [
            'title' => 'Standalone',
            'text' => 'Use it under any list — cards, a grid, a table you built yourself.',
            'code' => <<<'BLADE'
                {{-- Controller: $posts = Post::latest()->paginate(12); --}}

                <div class="aui-grid-3">
                    @foreach ($posts as $post) ... @endforeach
                </div>

                <x-avian::pagination :paginator="$posts" />
                BLADE,
        ],
        [
            'title' => 'Keeping filters in the links',
            'text' => 'Page links are built by the paginator, so query strings (search, filters, sort) are only kept when you ask for it.',
            'code' => <<<'BLADE'
                $users = User::filter($request->only('search', 'role'))
                    ->paginate(15)
                    ->withQueryString();
                BLADE,
        ],
        [
            'title' => 'Simple paginator',
            'text' => 'simplePaginate() skips the COUNT query; you get only Previous / Next, no page numbers or summary.',
            'code' => <<<'BLADE'
                $logs = ActivityLog::latest()->simplePaginate(50);

                <x-avian::pagination :paginator="$logs" />
                BLADE,
        ],
        [
            'title' => 'More page numbers',
            'code' => <<<'BLADE'
                <x-avian::pagination :paginator="$users" :on-each-side="2" />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'With the WithPagination trait, the links are plain URLs that Livewire intercepts; no extra setup is needed.',
            'code' => <<<'BLADE'
                use WithPagination;

                public function render()
                {
                    return view('livewire.users', ['users' => User::paginate(15)]);
                }
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Pagination', 'subtitle' => 'Page links for a Laravel paginator'])
<p class="aui-showcase-lead">
    Renders the page links of a Laravel paginator in Avian's style. Tables do this for you via
    <code>:paginator</code>; use this component directly for any other paginated list.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <div>
            <x-avian::label>Length-aware — paginate()</x-avian::label>
            <x-avian::pagination :paginator="$pageDemo" />
        </div>
        <div>
            <x-avian::label>Two numbers each side — :on-each-side="2"</x-avian::label>
            <x-avian::pagination :paginator="$pageDemo" :on-each-side="2" />
        </div>
        <div>
            <x-avian::label>Simple — simplePaginate()</x-avian::label>
            <x-avian::pagination :paginator="$simpleDemo" />
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li><code>paginate()</code> results get a "Showing X to Y of Z results" summary, the first and last page, the pages around the current one and <code>…</code> for the gaps.</li>
        <li><code>simplePaginate()</code> results only get Previous / Next arrows.</li>
        <li>When everything fits on one page, the component renders nothing at all.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
