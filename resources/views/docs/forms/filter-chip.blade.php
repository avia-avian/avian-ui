@php
    $props = [
        ['name', 'string|null', 'null', 'Input name. Use name[] for checkbox chips that pick several values.'],
        ['value', 'string', "'1'", 'Value sent when the chip is selected.'],
        ['type', "'checkbox'|'radio'", "'checkbox'", 'radio makes the chips sharing a name exclusive: one pick at a time.'],
        ['label', 'string|null', 'null', 'Chip text. The slot is used when label is omitted.'],
        ['checked', 'bool', 'false', 'Initial state. Old input wins over it after a failed validation.'],
        ['icon', 'string|null', 'null', 'Icon class shown before the label.'],
        ['count', 'int|string|null', 'null', 'Number shown in a small pill after the label, such as how many rows match.'],
        ['size', "'sm'|null", 'null', 'A smaller chip for dense toolbars.'],
        ['href', 'string|null', 'null', 'Renders a link instead of a checkbox, for filters kept in the query string.'],
        ['active', 'bool', 'false', 'Link chips only: marks the chip as the current filter.'],
        ['navigate', 'bool', 'false', 'Link chips only: adds wire:navigate.'],
    ];

    $groupProps = [
        ['name', 'string|null', 'null', 'Field the group\'s validation message is read for (name and name.*).'],
        ['label', 'string|null', 'null', 'Label above the chips; also the group\'s accessible name.'],
        ['hint', 'string|null', 'null', 'Helper text under the chips.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the row of chips.'],
    ];

    $examples = [
        [
            'title' => 'Several at once',
            'text' => 'Checkbox chips with name[] submit an array of the selected values.',
            'code' => <<<'BLADE'
                <x-avian::filter-chip.group name="status" label="Status">
                    @foreach (['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Closed'] as $value => $label)
                        <x-avian::filter-chip
                            name="status[]"
                            :value="$value"
                            :label="$label"
                            :count="$counts[$value] ?? 0"
                            :checked="in_array($value, request('status', []))"
                        />
                    @endforeach
                </x-avian::filter-chip.group>
                BLADE,
        ],
        [
            'title' => 'One at a time',
            'code' => <<<'BLADE'
                <x-avian::filter-chip.group label="Period">
                    <x-avian::filter-chip type="radio" name="period" value="7d" label="7 days" checked />
                    <x-avian::filter-chip type="radio" name="period" value="30d" label="30 days" />
                    <x-avian::filter-chip type="radio" name="period" value="1y" label="This year" />
                </x-avian::filter-chip.group>
                BLADE,
        ],
        [
            'title' => 'Links in the query string',
            'text' => 'With an href each chip is a plain link, so filters survive a reload and can be bookmarked.',
            'code' => <<<'BLADE'
                <x-avian::filter-chip.group>
                    <x-avian::filter-chip :href="request()->fullUrlWithQuery(['status' => null])" :active="! request('status')" label="All" />
                    <x-avian::filter-chip :href="request()->fullUrlWithQuery(['status' => 'overdue'])" :active="request('status') === 'overdue'" label="Overdue" icon="fas fa-clock" />
                </x-avian::filter-chip.group>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'code' => <<<'BLADE'
                public array $statuses = [];

                <x-avian::filter-chip.group>
                    <x-avian::filter-chip wire:model.live="statuses" value="open" label="Open" />
                    <x-avian::filter-chip wire:model.live="statuses" value="closed" label="Closed" />
                </x-avian::filter-chip.group>
                BLADE,
        ],
    ];
@endphp

<x-avian::card title="Filter chip" subtitle="Toggle a filter on a list">
    <p class="aui-showcase-lead">
        A pill that narrows a list when selected: a status, a period, "only mine". It is a checkbox (or a radio)
        underneath, so a row of them submits like any other field — or give it an <code>href</code> and it becomes
        a link for filters kept in the query string.
    </p>

    <div class="aui-showcase-demo">
        <div class="aui-stack">
            <x-avian::filter-chip.group label="Status (several)">
                <x-avian::filter-chip name="chip_status[]" value="open" label="Open" :count="24" checked />
                <x-avian::filter-chip name="chip_status[]" value="pending" label="Pending" :count="7" />
                <x-avian::filter-chip name="chip_status[]" value="closed" label="Closed" :count="112" />
                <x-avian::filter-chip name="chip_status[]" value="archived" label="Archived" disabled />
            </x-avian::filter-chip.group>

            <x-avian::filter-chip.group label="Period (one at a time)">
                <x-avian::filter-chip type="radio" name="chip_period" value="7d" label="7 days" checked />
                <x-avian::filter-chip type="radio" name="chip_period" value="30d" label="30 days" />
                <x-avian::filter-chip type="radio" name="chip_period" value="1y" label="This year" />
            </x-avian::filter-chip.group>

            <x-avian::filter-chip.group label="With icons, small" error="Pick at least one channel.">
                <x-avian::filter-chip name="chip_channel[]" value="email" label="Email" icon="fas fa-envelope" size="sm" />
                <x-avian::filter-chip name="chip_channel[]" value="sms" label="SMS" icon="fas fa-comment" size="sm" />
                <x-avian::filter-chip name="chip_channel[]" value="push" label="Push" icon="fas fa-bell" size="sm" />
            </x-avian::filter-chip.group>

            <x-avian::filter-chip.group label="Links">
                <x-avian::filter-chip href="#" label="All" active />
                <x-avian::filter-chip href="#" label="Overdue" icon="fas fa-clock" :count="3" />
                <x-avian::filter-chip href="#" label="Mine" icon="fas fa-user" />
            </x-avian::filter-chip.group>
        </div>
    </div>

    <div class="aui-showcase-block">
        <h4 class="aui-showcase-heading">How it works</h4>
        <ul class="aui-showcase-list">
            <li>Clicking a chip toggles it; Tab moves between chips and Space toggles the focused one.</li>
            <li>A selected chip shows a check mark, so the state never relies on colour alone.</li>
            <li>Old input re-selects the chips after a failed validation, including a <code>name[]</code> array.</li>
            <li>Link chips carry <code>aria-current</code> when <code>active</code>.</li>
        </ul>
    </div>

    @include('avian-ui::docs.partials.props')

    <div class="aui-showcase-block">
        <h4 class="aui-showcase-heading">Group props</h4>
        <div class="aui-showcase-props">
            <x-avian::table :headers="['Prop', 'Type', 'Default', 'Description']" :hover="false">
                @foreach ($groupProps as [$prop, $type, $default, $description])
                    <tr data-search-prop="{{ $prop }}">
                        <td><code>{{ $prop }}</code></td>
                        <td><code class="aui-showcase-type">{{ $type }}</code></td>
                        <td><code class="aui-showcase-type">{{ $default }}</code></td>
                        <td>{{ $description }}</td>
                    </tr>
                @endforeach
            </x-avian::table>
        </div>
    </div>

    <div class="aui-showcase-block">
        <h4 class="aui-showcase-heading">Examples</h4>
        @foreach ($examples as $example)
            @include('avian-ui::docs.partials.example', ['example' => $example])
        @endforeach
    </div>
</x-avian::card>
