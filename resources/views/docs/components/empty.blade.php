@php
    $props = [
        ['title', 'string|null', 'null', 'Main message, e.g. "No records yet".'],
        ['text', 'string|null', 'null', 'Explanation or next step under the title.'],
        ['icon', 'string|null', "'fas fa-inbox'", 'Icon class above the title. Pass an empty string to hide it.'],
        ['default slot', 'slot', '—', 'Anything under the text — usually a button that fixes the empty state.'],
    ];

    $examples = [
        [
            'title' => 'With a call to action',
            'code' => <<<'BLADE'
                <x-avian::empty icon="fas fa-folder-open" title="No records yet" text="Create your first record to get started.">
                    <x-avian::button href="{{ route('records.create') }}" icon="fas fa-plus" style="margin-top: 14px">
                        New record
                    </x-avian::button>
                </x-avian::empty>
                BLADE,
        ],
        [
            'title' => 'Empty list with @forelse',
            'code' => <<<'BLADE'
                @forelse ($comments as $comment)
                    ...
                @empty
                    <x-avian::empty icon="fas fa-comments" title="No comments" text="Be the first to comment." />
                @endforelse
                BLADE,
        ],
        [
            'title' => 'No search results',
            'code' => <<<'BLADE'
                <x-avian::empty icon="fas fa-magnifying-glass" title="No results for “{{ $search }}”" text="Try a different keyword." />
                BLADE,
        ],
        [
            'title' => 'Inside a table',
            'text' => 'You usually don\'t need it there: <x-avian::table> shows an empty state by itself when its body is empty. See the Table page.',
            'code' => <<<'BLADE'
                <x-avian::table :headers="['Name']" empty="No users found" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Empty state', 'subtitle' => 'What to show when there is nothing to show'])
<p class="aui-showcase-lead">
    A centred icon, title and short text for empty lists, tabs and search results. A good empty state
    tells the user why it is empty and what to do next — add a button in the slot for that.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid" style="gap: 20px">
        <x-avian::card :padded="false">
            <x-avian::empty icon="fas fa-folder-open" title="No records yet" text="Create your first record to get started.">
                <x-avian::button icon="fas fa-plus" size="sm" style="margin-top: 14px">New record</x-avian::button>
            </x-avian::empty>
        </x-avian::card>

        <x-avian::card :padded="false">
            <x-avian::empty icon="fas fa-magnifying-glass" title="No results for “paint”" text="Try a different keyword." />
        </x-avian::card>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Every part is optional — title, text and icon only render when set.</li>
        <li>The component adds generous padding, so it fills a card or tab panel nicely on its own.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
