@php
    $themes = ['emerald-green', 'navy-blue', 'sky-blue', 'steel-blue', 'teal-maroon', 'brown-gold', 'golden-yellow'];

    $examples = [
        [
            'title' => '1. Install',
            'code' => <<<'BLADE'
                composer require avia-avian/avian-ui:dev-master
                BLADE,
        ],
        [
            'title' => '2. Add the assets to your layout',
            'text' => 'The CSS and JS are served straight from the package, so there is nothing to publish or build. Alpine is not bundled: <x-avian::scripts /> must load before Alpine starts. With Livewire that is automatic; without it, put your Alpine tag below.',
            'code' => <<<'BLADE'
                <head>
                    <x-avian::styles />
                    <x-avian::scripts />

                    {{-- Only without Livewire: --}}
                    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
                </head>
                BLADE,
        ],
        [
            'title' => '3. Load icons (and optional plugins)',
            'text' => 'Components take Font Awesome class names (icon="fas fa-plus"). Load Font Awesome yourself. The numeric input needs @alpinejs/mask, and the datepicker needs flatpickr — see their pages.',
            'code' => <<<'BLADE'
                <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
                <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
                BLADE,
        ],
        [
            'title' => '4. Pick a theme',
            'text' => 'Set data-theme on <html>. Every colour in the library is a CSS variable, so the whole UI repaints.',
            'code' => <<<'BLADE'
                <html lang="en" data-theme="navy-blue">
                BLADE,
        ],
        [
            'title' => 'Use the components',
            'text' => 'All components use the <x-avian::…> prefix (changeable in config/avian-ui.php).',
            'code' => <<<'BLADE'
                <x-avian::page-header title="Records">
                    <x-slot:actions>
                        <x-avian::button icon="fas fa-plus" modal="create">New record</x-avian::button>
                    </x-slot:actions>
                </x-avian::page-header>
                BLADE,
        ],
        [
            'title' => 'Optional: publish and customise',
            'code' => <<<'BLADE'
                php artisan vendor:publish --tag="avian-ui-config"   # prefix, asset URLs, themes on/off
                php artisan vendor:publish --tag="avian-ui-views"    # override component markup
                php artisan vendor:publish --tag="avian-ui-lang"     # translate built-in texts
                php artisan vendor:publish --tag="avian-ui-assets"   # serve CSS/JS from public/
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Getting started', 'subtitle' => 'Blade components for Laravel, built on Alpine. Works with and without Livewire.'])

<p class="aui-showcase-lead">
    Avian UI is a set of anonymous Blade components (<code>&lt;x-avian::button&gt;</code>,
    <code>&lt;x-avian::input&gt;</code>, …) plus one stylesheet and one small script. Pick a component
    in the sidebar to see a live demo, what each prop does and copy-ready examples.
</p>

<div class="aui-showcase-demo">
    <x-avian::label>Try a theme — this changes <code>data-theme</code> on <code>&lt;html&gt;</code></x-avian::label>
    <div class="aui-row" style="flex-wrap: wrap" x-data="{ theme: document.documentElement.dataset.theme }">
        @foreach ($themes as $theme)
            <x-avian::button
                size="sm"
                variant="outline"
                x-on:click="theme = '{{ $theme }}'; document.documentElement.dataset.theme = theme"
                class="aui-showcase-theme"
                x-bind:class="theme === '{{ $theme }}' ? 'is-active' : ''"
            >{{ $theme }}</x-avian::button>
        @endforeach
    </div>

    <div class="aui-row" style="flex-wrap: wrap; margin-top: 18px">
        <x-avian::button icon="fas fa-check">Primary</x-avian::button>
        <x-avian::badge variant="primary">Badge</x-avian::badge>
        <x-avian::badge variant="success" dot>Active</x-avian::badge>
        <div style="flex: 1 1 200px"><x-avian::progress :value="64" /></div>
        <x-avian::button variant="light" icon="fas fa-window-restore" modal="demo">Open a modal</x-avian::button>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Things to know</h2>
    <ul class="aui-showcase-list">
        <li><strong>Form controls</strong> read validation errors and old input by <code>name</code> automatically — see <em>Form &amp; layout</em>.</li>
        <li><strong>Interactive components</strong> (modal, dropdown, tabs, searchable/multi select, file, dismissible alert) need Alpine and <code>&lt;x-avian::scripts /&gt;</code>.</li>
        <li><strong>Livewire</strong>: <code>wire:model</code>, <code>wire:click</code> and friends can be put on any component; they are forwarded to the right element.</li>
        <li><strong>Extra attributes</strong> such as <code>class</code>, <code>id</code> or <code>data-*</code> are always passed through, so you can style or hook into any component.</li>
        <li><strong>No remote requests</strong>: the package serves its own CSS and JS and never loads anything from a CDN; fonts, icons and Alpine are yours to choose.</li>
    </ul>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Setup</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
