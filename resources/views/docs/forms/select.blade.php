@php
    $props = [
        ['name', 'string|null', 'null', 'Select name; also the key for validation errors and old input. End it with [] together with `multiple` to submit an array.'],
        ['options', 'array', '[]', 'value => label pairs. Each key becomes the <option value>, each value its text.'],
        ['value', 'mixed', 'null', 'Selected value (or an array of values with `multiple`). Falls back to old input.'],
        ['placeholder', 'string|null', 'null', 'Adds an empty first option (value="") with this text.'],
        ['label', 'string|null', 'null', 'Label shown above the select.'],
        ['hint', 'string|null', 'null', 'Helper text under the select.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the <select>.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'text' => 'Keys are submitted, labels are shown. Add a placeholder so nothing is pre-selected; combine it with a `required` rule to force a choice.',
            'code' => <<<'BLADE'
                <x-avian::select
                    name="role"
                    label="Role"
                    placeholder="Choose a role"
                    :options="['admin' => 'Administrator', 'editor' => 'Editor', 'viewer' => 'Viewer']"
                />
                BLADE,
        ],
        [
            'title' => 'Options from the database',
            'text' => 'pluck() returns a Collection keyed by id — convert it with ->all() (or toArray()) for `options`.',
            'code' => <<<'BLADE'
                <x-avian::select
                    name="category_id"
                    label="Category"
                    placeholder="Choose a category"
                    :options="$categories->pluck('name', 'id')->all()"
                    :value="old('category_id', $post->category_id)"
                />
                BLADE,
        ],
        [
            'title' => 'Enums',
            'code' => <<<'BLADE'
                <x-avian::select
                    name="status"
                    label="Status"
                    :options="collect(Status::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all()"
                />
                BLADE,
        ],
        [
            'title' => 'Custom <option> markup (groups, disabled options)',
            'text' => 'Anything in the slot is appended after the generated options, so you can skip `options` entirely and write your own.',
            'code' => <<<'BLADE'
                <x-avian::select name="city" label="City" placeholder="Choose a city">
                    <optgroup label="Indonesia">
                        <option value="jkt">Jakarta</option>
                        <option value="sby">Surabaya</option>
                    </optgroup>
                    <option value="sg" disabled>Singapore (soon)</option>
                </x-avian::select>
                BLADE,
        ],
        [
            'title' => 'Native multiple select',
            'text' => 'Works, but for picking several values the multi select component is much friendlier.',
            'code' => <<<'BLADE'
                <x-avian::select name="days[]" label="Days" multiple :options="$days" :value="['mon', 'tue']" />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'code' => <<<'BLADE'
                <x-avian::select wire:model.live="role" label="Role" :options="$roles" placeholder="All roles" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Select', 'subtitle' => 'Native dropdown'])
<p class="aui-showcase-lead">
    A styled native <code>&lt;select&gt;</code>. Use it for short lists (up to ~10 options) where the
    browser's own dropdown is good enough — it is the lightest option and needs no JavaScript. For long
    lists use the <strong>searchable select</strong>; to pick several values use the <strong>multi select</strong>.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::select
            name="select_role"
            label="Role"
            placeholder="Choose a role"
            :options="['admin' => 'Administrator', 'editor' => 'Editor', 'viewer' => 'Viewer']"
        />
        <x-avian::select
            name="select_status"
            label="Status"
            value="active"
            hint="Pre-selected through the value prop."
            :options="['active' => 'Active', 'inactive' => 'Inactive']"
        />
        <x-avian::select
            name="select_team"
            label="Team"
            required
            placeholder="Choose a team"
            error="The team field is required."
            :options="['a' => 'Team A', 'b' => 'Team B']"
        />
        <x-avian::select name="select_small" label="Small" size="sm" :options="['1' => 'Option one', '2' => 'Option two']" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Values are compared as strings, so <code>:value="1"</code> selects the option with key <code>'1'</code> — ids from the database just work.</li>
        <li>The placeholder option has an empty value, so a <code>required</code> validation rule rejects it.</li>
        <li>After a failed validation the previously chosen option is selected again.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
