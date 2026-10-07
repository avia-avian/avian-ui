@php
    $props = [
        ['position', 'string', "'top-right'", 'top-right, top-left, top-center, bottom-right, bottom-left or bottom-center.'],
        ['duration', 'int', '5000', 'Default time in ms before a toast closes itself. 0 keeps toasts until dismissed.'],
        ['max', 'int', '5', 'Most toasts shown at once; the oldest makes room for a new one.'],
        ['flash', 'bool', 'true', 'Show success / error / warning / info / toast messages flashed to the session.'],
    ];

    $examples = [
        [
            'title' => 'Place it once in the layout',
            'code' => <<<'BLADE'
                <body>
                    ...
                    <x-avian::confirm />
                    <x-avian::toasts />
                </body>
                BLADE,
        ],
        [
            'title' => 'After a redirect',
            'text' => 'Flash `success`, `error`, `warning` or `info` and the toast appears on the next page. Use `toast` for a title or another variant.',
            'code' => <<<'BLADE'
                return redirect()->route('orders.index')->with('success', 'Order saved.');

                return back()->with('error', 'The order could not be cancelled.');

                return back()->with('toast', [
                    'variant' => 'warning',
                    'title' => 'Low stock',
                    'message' => 'Only 3 items left.',
                ]);
                BLADE,
        ],
        [
            'title' => 'From Livewire',
            'text' => 'Dispatch the `aui-toast` browser event with named params — no redirect needed.',
            'code' => <<<'BLADE'
                public function save(): void
                {
                    $this->order->save();

                    $this->dispatch('aui-toast', message: 'Order saved.', variant: 'success');
                }
                BLADE,
        ],
        [
            'title' => 'From JavaScript or Alpine',
            'text' => 'AvianUI.toast() takes a message and a variant, or an options object. It returns the toast id.',
            'code' => <<<'BLADE'
                AvianUI.toast('Link copied.', 'info')

                AvianUI.toast({
                    title: 'Export ready',
                    message: 'We emailed you the file.',
                    variant: 'success',
                    duration: 0,        // stays until dismissed
                })

                <x-avian::button x-on:click="$dispatch('aui-toast', { message: 'Copied!' })">Copy</x-avian::button>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Toast', 'subtitle' => 'Short notifications that close themselves'])
<p class="aui-showcase-lead">
    A small notification in the corner of the screen for the result of an action — saved, deleted, failed.
    It picks up flashed session messages automatically and can be raised from Livewire or JavaScript.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap">
        <x-avian::button variant="success" icon="fas fa-check" x-data x-on:click="AvianUI.toast('Order ORD-2026-0042 saved.', 'success')">Success</x-avian::button>
        <x-avian::button variant="danger" icon="fas fa-xmark" x-data x-on:click="AvianUI.toast({ title: 'Payment failed', message: 'The card was declined.', variant: 'danger' })">Error</x-avian::button>
        <x-avian::button variant="warning" icon="fas fa-triangle-exclamation" x-data x-on:click="AvianUI.toast({ title: 'Low stock', message: 'Only 3 items left.', variant: 'warning' })">Warning</x-avian::button>
        <x-avian::button variant="info" icon="fas fa-circle-info" x-data x-on:click="$dispatch('aui-toast', { message: 'A new version is available.', variant: 'info' })">Info (event)</x-avian::button>
        <x-avian::button variant="light" icon="fas fa-thumbtack" x-data x-on:click="AvianUI.toast({ title: 'Export ready', message: 'Stays until you close it.', variant: 'neutral', duration: 0 })">Sticky</x-avian::button>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Each toast closes after <code>duration</code> ms. Hovering or focusing it pauses the timer, and the bar along the bottom shows the time left.</li>
        <li>Error toasts use <code>role="alert"</code> so screen readers announce them straight away; the rest use <code>role="status"</code>.</li>
        <li>Messages are set with <code>x-text</code>, so they are always escaped — never pass HTML.</li>
        <li>Toasts raised before Alpine starts are queued and shown as soon as the stack is ready.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
