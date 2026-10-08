@php
    $props = [
        ['variant', "'text'|'circle'|'rect'|'button'", "'text'", 'Shape of the placeholder.'],
        ['lines', 'int', '1', 'Number of text lines; the last one is shorter, like the end of a paragraph.'],
        ['width', 'int|string|null', 'null', 'Width. Numbers are pixels; any CSS length works ("60%").'],
        ['height', 'int|string|null', 'null', 'Height, same units.'],
        ['size', 'int|string|null', '40', 'Diameter of a circle.'],
        ['animate', 'bool', 'true', 'The shimmer. It is off anyway for users who prefer reduced motion.'],
    ];

    $examples = [
        [
            'title' => 'Livewire lazy loading',
            'text' => 'A lazy component renders its placeholder() until it has loaded. A skeleton shaped like the real content keeps the page from jumping.',
            'code' => <<<'BLADE'
                // app/Livewire/RecentOrders.php
                #[Lazy]
                class RecentOrders extends Component
                {
                    public function placeholder()
                    {
                        return <<<'HTML'
                            <div>
                                <x-avian::skeleton.table :rows="5" :columns="4" label="Loading orders" />
                            </div>
                            HTML;
                    }
                }
                BLADE,
        ],
        [
            'title' => 'A card while it loads',
            'code' => <<<'BLADE'
                <x-avian::card aria-busy="true">
                    <div class="aui-row">
                        <x-avian::skeleton variant="circle" size="44" />
                        <x-avian::skeleton :lines="2" />
                    </div>
                    <x-avian::skeleton variant="rect" height="140" style="margin-top: 16px" />
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Showing it during a request',
            'code' => <<<'BLADE'
                <div wire:loading.delay wire:target="search">
                    <x-avian::skeleton.table :rows="3" :columns="3" />
                </div>
                <div wire:loading.remove wire:target="search">
                    <x-avian::table ...>
                </div>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Skeleton', 'subtitle' => 'Placeholders while content loads'])
<p class="aui-showcase-lead">
    Grey shapes that stand in for content that has not loaded yet. Shaped like the real thing, they
    keep the layout steady and feel faster than a spinner. They pair naturally with Livewire's
    lazy components.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 20px">
        <div class="aui-row" style="align-items: flex-start">
            <x-avian::skeleton variant="circle" size="44" />
            <div style="flex: 1">
                <x-avian::skeleton width="40%" height="14" style="margin-bottom: 10px" />
                <x-avian::skeleton :lines="3" />
            </div>
        </div>

        <div class="aui-row">
            <x-avian::skeleton variant="rect" height="96" />
            <x-avian::skeleton variant="rect" height="96" />
        </div>

        <div class="aui-row">
            <x-avian::skeleton variant="button" />
            <x-avian::skeleton variant="button" width="72" />
        </div>

        <x-avian::skeleton.table :rows="3" :columns="4" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Skeletons are <code>aria-hidden</code>. Mark the region that is loading with <code>aria-busy="true"</code>. <code>&lt;x-avian::skeleton.table&gt;</code> does this for you and announces <code>label</code> as a status.</li>
        <li><code>&lt;x-avian::skeleton.table&gt;</code> takes <code>rows</code>, <code>columns</code>, <code>header</code> (default true), <code>label</code> and <code>animate</code>, and is styled like a real table.</li>
        <li>The shimmer stops for users who ask their system for reduced motion.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
