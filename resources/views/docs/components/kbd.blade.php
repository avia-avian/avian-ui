@php
    $props = [
        ['keys', 'string|array|null', 'null', 'A shortcut such as "Ctrl+K", or a list of keys. Without it, the slot is a single key.'],
        ['separator', 'string', "'+'", 'Shown between the keys of a shortcut.'],
        ['size', "'sm'|null", 'null', 'A smaller key for dense text and menus.'],
    ];

    $examples = [
        [
            'title' => 'A single key in a sentence',
            'code' => <<<'BLADE'
                Press <x-avian::kbd>Esc</x-avian::kbd> to close the dialog.
                BLADE,
        ],
        [
            'title' => 'A shortcut',
            'code' => <<<'BLADE'
                <x-avian::kbd keys="Ctrl+K" />
                <x-avian::kbd :keys="['⌘', 'Shift', 'P']" />
                <x-avian::kbd keys="G then I" separator="" />
                BLADE,
        ],
        [
            'title' => 'Next to a menu item',
            'code' => <<<'BLADE'
                <x-avian::dropdown.item icon="fas fa-floppy-disk">
                    Save <x-avian::kbd keys="Ctrl+S" size="sm" style="margin-left: auto" />
                </x-avian::dropdown.item>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Kbd', 'subtitle' => 'Keyboard keys and shortcuts'])
<p class="aui-showcase-lead">
    Shows a key or a keyboard shortcut the way it looks on a keyboard, in help text, tooltips and menus.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack">
        <p style="margin: 0">Press <x-avian::kbd>Esc</x-avian::kbd> to close the dialog, or <x-avian::kbd keys="Ctrl+Enter" /> to save it.</p>
        <div class="aui-row" style="flex-wrap: wrap">
            <x-avian::kbd keys="Ctrl+K" />
            <x-avian::kbd :keys="['⌘', 'Shift', 'P']" />
            <x-avian::kbd keys="Ctrl+S" size="sm" />
            <x-avian::kbd>↑</x-avian::kbd>
            <x-avian::kbd>↓</x-avian::kbd>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Renders a native <code>&lt;kbd&gt;</code>, so screen readers and copy-paste see the key text.</li>
        <li><code>keys</code> splits on <code>+</code> and renders one key per part. The separator is hidden from screen readers.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
