@php
    $props = [
        ['variant', 'string', "'info'", 'primary, secondary, success, warning, danger, info, neutral, dark, purple, indigo, teal, orange or pink. Sets the colour; info, success, warning, danger and neutral also pick a default icon.'],
        ['title', 'string|null', 'null', 'Bold first line above the message.'],
        ['icon', 'string|false|null', 'null', 'Override the default icon with another class, or pass :icon="false" to hide it.'],
        ['dismissible', 'bool', 'false', 'Adds a close button. Closing only hides it on this page view.'],
    ];

    $examples = [
        [
            'title' => 'Variants',
            'code' => <<<'BLADE'
                <x-avian::alert variant="success">Your changes have been saved.</x-avian::alert>
                <x-avian::alert variant="warning" title="Heads up">Your trial ends in 3 days.</x-avian::alert>
                <x-avian::alert variant="danger">We couldn't reach the payment provider.</x-avian::alert>
                BLADE,
        ],
        [
            'title' => 'Flash messages after a redirect',
            'text' => 'The most common use: set a flash message in the controller and show it at the top of your layout.',
            'code' => <<<'BLADE'
                // Controller
                return redirect()->route('records.index')->with('success', 'Record created.');

                {{-- Layout --}}
                @if (session('success'))
                    <x-avian::alert variant="success" dismissible>{{ session('success') }}</x-avian::alert>
                @endif
                BLADE,
        ],
        [
            'title' => 'Validation summary',
            'text' => 'Fields already show their own message; a summary at the top helps on long forms.',
            'code' => <<<'BLADE'
                @if ($errors->any())
                    <x-avian::alert variant="danger" title="Please fix the following:">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </x-avian::alert>
                @endif
                BLADE,
        ],
        [
            'title' => 'Custom icon or no icon',
            'code' => <<<'BLADE'
                <x-avian::alert variant="info" icon="fas fa-lightbulb">Tip: press / to search.</x-avian::alert>
                <x-avian::alert variant="neutral" :icon="false">Plain message without an icon.</x-avian::alert>
                BLADE,
        ],
        [
            'title' => 'Reacting to dismissal',
            'text' => 'A dismissed alert dispatches the aui-dismissed browser event — for example to remember it server-side.',
            'code' => <<<'BLADE'
                <x-avian::alert dismissible x-on:aui-dismissed="$wire.hideAnnouncement()">
                    New: export to Excel is here.
                </x-avian::alert>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Alert', 'subtitle' => 'Inline messages for the user'])
<p class="aui-showcase-lead">
    A coloured message box for feedback that should stay on screen: success after saving, warnings,
    errors and general information. Each variant has a matching icon.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 12px">
        <x-avian::alert variant="info">A new version of the report is available.</x-avian::alert>
        <x-avian::alert variant="success" dismissible>Your changes have been saved. (Dismiss me.)</x-avian::alert>
        <x-avian::alert variant="warning" title="Heads up">Your trial ends in 3 days.</x-avian::alert>
        <x-avian::alert variant="danger" title="Payment failed">We couldn't reach the payment provider. Try again in a few minutes.</x-avian::alert>
        <x-avian::alert variant="neutral" icon="fas fa-lightbulb">Tip: custom icons work on every variant.</x-avian::alert>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The alert has <code>role="alert"</code>, so screen readers announce it.</li>
        <li><code>dismissible</code> uses a small Alpine component; the alert is hidden (not removed) and comes back on the next page load.</li>
        <li>The slot can hold any markup — links, lists, buttons.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
