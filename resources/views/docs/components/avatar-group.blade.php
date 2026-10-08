@php
    $props = [
        ['users', 'iterable|null', 'null', 'Models or arrays to draw avatars for.'],
        ['max', 'int|null', 'null', 'Avatars shown before the rest collapse into a "+N" chip. Only applies to users.'],
        ['size', "'sm'|'lg'|null", 'null', 'Avatar size.'],
        ['name-key', 'string', "'name'", 'Attribute holding the name.'],
        ['src-key', 'string', "'avatar_url'", 'Attribute holding the image URL.'],
        ['tooltips', 'bool', 'true', 'Show each name, and the hidden names on the chip, as a tooltip.'],
        ['label', 'string|null', 'null', 'Accessible name of the group, e.g. "Assignees".'],
    ];

    $examples = [
        [
            'title' => 'Assignees in a table row',
            'code' => <<<'BLADE'
                <td>
                    <x-avian::avatar-group :users="$task->assignees" :max="3" size="sm" label="Assignees" />
                </td>
                BLADE,
        ],
        [
            'title' => 'Different attribute names',
            'code' => <<<'BLADE'
                <x-avian::avatar-group :users="$members" name-key="full_name" src-key="photo_url" />
                BLADE,
        ],
        [
            'title' => 'Avatars as children',
            'text' => 'Pass avatars in the slot for full control. Blade cannot count slot children, so max does not apply here.',
            'code' => <<<'BLADE'
                <x-avian::avatar-group>
                    <x-avian::avatar :src="$owner->avatar_url" :name="$owner->name" />
                    <x-avian::avatar name="Guest" initials="?" />
                </x-avian::avatar-group>
                BLADE,
        ],
    ];

    $demoUsers = [
        ['name' => 'Rina Wijaya'],
        ['name' => 'Budi Santoso'],
        ['name' => 'Sari Dewi'],
        ['name' => 'Agus Pratama'],
        ['name' => 'Maya Lestari'],
        ['name' => 'Dimas Saputra'],
        ['name' => 'Putri Ayu'],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Avatar group', 'subtitle' => 'Overlapping avatars for a set of people'])
<p class="aui-showcase-lead">
    Shows who is on something (assignees, team members, viewers) in little space. Past <code>max</code>
    the rest collapse into a "+N" chip that names them on hover.
</p>

<div class="aui-showcase-demo">
    <div class="aui-stack" style="gap: 18px">
        <x-avian::avatar-group :users="$demoUsers" :max="4" label="Team" />
        <x-avian::avatar-group :users="array_slice($demoUsers, 0, 5)" :max="3" size="sm" label="Assignees" />
        <x-avian::avatar-group :users="array_slice($demoUsers, 0, 3)" size="lg" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Avatars overlap with a ring in the surface colour; the hovered one comes to the front.</li>
        <li>Each avatar has its name as its accessible label, and the chip reads as "3 more".</li>
        <li>Initials come from the name when there is no image, exactly like <code>&lt;x-avian::avatar&gt;</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
