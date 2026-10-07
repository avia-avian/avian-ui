@php
    $props = [
        ['size', "'sm'|'lg'|null", 'null', 'Spinner size. Omit for the default size.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::spinner size="sm" />
                <x-avian::spinner />
                <x-avian::spinner size="lg" />
                BLADE,
        ],
        [
            'title' => 'Loading message',
            'code' => <<<'BLADE'
                <div class="aui-row">
                    <x-avian::spinner size="sm" />
                    <span>Loading records…</span>
                </div>
                BLADE,
        ],
        [
            'title' => 'While Livewire is working',
            'code' => <<<'BLADE'
                <div wire:loading.flex wire:target="search" class="aui-row">
                    <x-avian::spinner size="sm" /> Searching…
                </div>
                BLADE,
        ],
        [
            'title' => 'Custom colour',
            'code' => <<<'BLADE'
                <x-avian::spinner style="color: var(--aui-primary)" />
                BLADE,
        ],
        [
            'title' => 'Inside a button',
            'text' => 'You don\'t need the spinner component for this — the button\'s `loading` prop draws one and disables the button.',
            'code' => <<<'BLADE'
                <x-avian::button loading>Saving</x-avian::button>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Spinner', 'subtitle' => 'Indeterminate loading indicator'])
<p class="aui-showcase-lead">
    A small rotating ring for when something is loading and you can't tell how long it will take. When
    you do know the progress, use the progress bar instead.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap; gap: 24px">
        <x-avian::spinner size="sm" />
        <x-avian::spinner />
        <x-avian::spinner size="lg" />
        <div class="aui-row">
            <x-avian::spinner size="sm" />
            <span>Loading records…</span>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Pure CSS animation — no JavaScript involved.</li>
        <li>It is decorative (<code>aria-hidden</code>), so always pair it with visible text such as "Loading…" when the wait matters.</li>
        <li>It is drawn in <code>currentColor</code>, so it takes the text colour of whatever it sits in — set <code>style="color: …"</code> on it or its parent to recolour it.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
