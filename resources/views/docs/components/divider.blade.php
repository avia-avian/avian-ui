@php
    $props = [
        ['label', 'string|null', 'null', 'Text in the middle of the line; the default slot works too.'],
        ['align', "'center'|'left'|'right'", "'center'", 'Where the label sits.'],
        ['vertical', 'bool', 'false', 'A thin upright line for a flex row (toolbars).'],
    ];

    $examples = [
        [
            'title' => 'Plain and labelled',
            'code' => <<<'BLADE'
                <x-avian::divider />
                <x-avian::divider label="or" />
                <x-avian::divider align="left">Shipping details</x-avian::divider>
                BLADE,
        ],
        [
            'title' => 'Between toolbar buttons',
            'code' => <<<'BLADE'
                <div class="aui-row">
                    <x-avian::button variant="light" size="sm">Bold</x-avian::button>
                    <x-avian::divider vertical />
                    <x-avian::button variant="light" size="sm">Link</x-avian::button>
                </div>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Divider', 'subtitle' => 'Separate blocks of content'])
<p class="aui-showcase-lead">
    A thin rule between sections of a form or a card, with an optional label.
</p>

<div class="aui-showcase-demo">
    <p style="margin: 0">Customer details</p>
    <x-avian::divider />
    <x-avian::button block icon="fas fa-right-to-bracket">Sign in with SSO</x-avian::button>
    <x-avian::divider label="or" />
    <x-avian::input name="divider_email" label="Email" placeholder="you@company.com" />
    <x-avian::divider align="left">Shipping details</x-avian::divider>
    <div class="aui-row">
        <x-avian::button variant="light" size="sm" icon="fas fa-copy">Copy</x-avian::button>
        <x-avian::divider vertical />
        <x-avian::button variant="light" size="sm" icon="fas fa-print">Print</x-avian::button>
        <x-avian::divider vertical />
        <x-avian::button variant="light" size="sm" icon="fas fa-download">Export</x-avian::button>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>A plain divider is an <code>&lt;hr&gt;</code>; a labelled or vertical one carries <code>role="separator"</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
