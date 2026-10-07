@php
    $props = [
        ['title', 'string|null', 'null', 'Heading in the card header.'],
        ['subtitle', 'string|null', 'null', 'Muted line under the title.'],
        ['padded', 'bool', 'true', 'Wraps the slot in a padded body. Set :padded="false" for edge-to-edge content like tables.'],
        ['collapsible', 'bool', 'false', 'Adds a chevron to the header that folds the body and footer away, leaving only the header. Clicking the header outside its actions does the same.'],
        ['collapsed', 'bool', 'false', 'Starts a collapsible card folded.'],
        ['persist', 'string|null', 'null', 'localStorage key that remembers whether the viewer folded the card.'],
        ['flush', 'bool', 'false', 'Keeps the body wrapper but removes its padding. Use it when the content brings its own spacing.'],
        ['actions (slot)', 'slot', '—', 'Buttons shown on the right side of the header.'],
        ['header (slot)', 'slot', '—', 'Replaces the title/subtitle block with your own markup.'],
        ['footer (slot)', 'slot', '—', 'Bar at the bottom of the card, e.g. for form buttons.'],
    ];

    $examples = [
        [
            'title' => 'Title and content',
            'code' => <<<'BLADE'
                <x-avian::card title="Profile" subtitle="Your public information">
                    ...
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Header actions and footer',
            'code' => <<<'BLADE'
                <x-avian::card title="Team members">
                    <x-slot:actions>
                        <x-avian::button size="sm" icon="fas fa-plus">Invite</x-avian::button>
                    </x-slot:actions>

                    ...

                    <x-slot:footer>
                        <x-avian::button variant="light">Cancel</x-avian::button>
                        <x-avian::button type="submit">Save</x-avian::button>
                    </x-slot:footer>
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Table inside a card',
            'text' => 'Turn padding off so the table runs edge to edge and its rows line up with the card border.',
            'code' => <<<'BLADE'
                <x-avian::card title="Users" :padded="false">
                    <x-avian::table :headers="['Name', 'Email']" :paginator="$users">
                        ...
                    </x-avian::table>
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Collapsible',
            'text' => 'The chevron and the header both toggle the card. Clicks on buttons or links in the header (like the actions slot) do not.',
            'code' => <<<'BLADE'
                <x-avian::card title="Filters" collapsible>
                    ...
                </x-avian::card>

                {{-- Start folded, and remember the viewer's choice across visits --}}
                <x-avian::card title="Advanced settings" collapsible collapsed persist="advanced-settings">
                    ...
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Reacting to a collapse',
            'code' => <<<'BLADE'
                <x-avian::card title="Chart" collapsible x-on:aui-card-toggled="$event.detail.collapsed || chart.resize()">
                    ...
                </x-avian::card>
                BLADE,
        ],
        [
            'title' => 'Custom header',
            'code' => <<<'BLADE'
                <x-avian::card>
                    <x-slot:header>
                        <div class="aui-row">
                            <x-avian::avatar :name="$user->name" />
                            <strong>{{ $user->name }}</strong>
                        </div>
                    </x-slot:header>
                    ...
                </x-avian::card>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Card', 'subtitle' => 'Container for a block of content'])
<p class="aui-showcase-lead">
    The white panel most page content lives in. It has an optional header (title, subtitle and action
    buttons), a padded body and an optional footer. Every page in this showcase is a card.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid" style="gap: 20px">
        <x-avian::card title="Team members" subtitle="3 people">
            <x-slot:actions>
                <x-avian::button size="sm" icon="fas fa-plus">Invite</x-avian::button>
            </x-slot:actions>

            Cards with a header, action buttons and a footer.

            <x-slot:footer>
                <x-avian::button variant="light" size="sm">Cancel</x-avian::button>
                <x-avian::button size="sm">Save</x-avian::button>
            </x-slot:footer>
        </x-avian::card>

        <x-avian::card>
            A card without a header is just a padded panel.
        </x-avian::card>

        <x-avian::card title="Flush card" flush>
            <x-avian::alert variant="info" :icon="false" style="margin: 0; border-radius: 0">The body has no padding.</x-avian::alert>
        </x-avian::card>

        <x-avian::card title="Collapsible" subtitle="Click the header or the chevron" collapsible>
            <x-slot:actions>
                <x-avian::button variant="light" size="sm">Reset</x-avian::button>
            </x-slot:actions>

            The body and footer fold into the header.

            <x-slot:footer>
                <x-avian::button size="sm">Apply</x-avian::button>
            </x-slot:footer>
        </x-avian::card>

        <x-avian::card title="Starts collapsed" subtitle="Remembered in localStorage" collapsible collapsed persist="showcase-card">
            Opened cards stay open after a reload.
        </x-avian::card>

        <x-avian::card title="Unpadded" :padded="false">
            <x-avian::table :headers="['Name', 'Role']">
                <tr><td>Ada Lovelace</td><td>Admin</td></tr>
                <tr><td>Grace Hopper</td><td>Editor</td></tr>
            </x-avian::table>
        </x-avian::card>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The header only renders when there is a title, subtitle, <code>actions</code> or <code>header</code> slot.</li>
        <li>Slots use Laravel's named-slot syntax: <code>&lt;x-slot:actions&gt;</code> … <code>&lt;/x-slot:actions&gt;</code>.</li>
        <li><code>:padded="false"</code> drops the body wrapper completely; <code>flush</code> keeps it but with zero padding. Both are for content that brings its own spacing, like tables.</li>
        <li><code>collapsible</code> folds everything under the header. The open/closed state lives in Alpine, so a Livewire re-render keeps it, and the card dispatches <code>aui-card-toggled</code> with <code>{ collapsed }</code> when it changes.</li>
        <li>The card clips its content to its rounded corners — dropdowns and selects inside it are positioned so they are not cut off.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
