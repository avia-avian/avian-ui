@php
    $props = [
        ['name', 'string|null', 'null', 'Field name. Values submit as name[] (a trailing [] is added for you), so the request receives an array.'],
        ['options', 'array|Collection|null', 'null', 'value => label pairs. Leave it out (null) to write the options yourself in the slot.'],
        ['value', 'array|null', 'null', 'Pre-selected values. Falls back to old input when wire:model is not used.'],
        ['max', 'int|null', 'null', 'Maximum number of values that can be picked. Further options are ignored once reached.'],
        ['clearable', 'bool', 'false', 'Adds a × button (and Backspace / Delete on the trigger) that removes every pick.'],
        ['taggable', 'bool', 'false', 'Lets the user add typed values that are not in the list. Backspace in an empty search box takes a typed tag back for editing.'],
        ['create-text', 'string', "'Add \":term\"'", 'Row text offering the typed value in taggable mode; `:term` is replaced.'],
        ['placeholder', 'string', "'Select options'", 'Trigger text when nothing is selected.'],
        ['search-placeholder', 'string', "'Search...'", 'Placeholder of the search box inside the dropdown.'],
        ['empty-text', 'string', "'No results found.'", 'Shown when the search matches nothing.'],
        ['label', 'string|null', 'null', 'Label shown above the control.'],
        ['hint', 'string|null', 'null', 'Helper text under the control.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise the first error for `name` or `name.*` is used.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label. Validate on the server (e.g. required|array|min:1).'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height.'],
        ['disabled', 'bool', 'false', 'Prevents opening the dropdown and removing chips.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the control.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::multi-select
                    name="skills"
                    label="Skills"
                    placeholder="Pick a few skills"
                    :options="['php' => 'PHP', 'laravel' => 'Laravel', 'js' => 'JavaScript']"
                    :value="['php']"
                />
                BLADE,
        ],
        [
            'title' => 'Validating the array',
            'text' => 'The request receives skills as an array. Errors on the array (skills) and on its items (skills.*) are both shown under the control.',
            'code' => <<<'BLADE'
                $request->validate([
                    'skills' => ['required', 'array', 'max:3'],
                    'skills.*' => ['in:php,laravel,js'],
                ]);
                BLADE,
        ],
        [
            'title' => 'Editing a many-to-many relation',
            'text' => 'Pre-select the related ids, then sync() them on save.',
            'code' => <<<'BLADE'
                <x-avian::multi-select
                    name="tag_ids"
                    label="Tags"
                    :options="$tags->pluck('name', 'id')"
                    :value="old('tag_ids', $post->tags->pluck('id')->all())"
                />

                // Controller
                $post->tags()->sync($request->validated('tag_ids', []));
                BLADE,
        ],
        [
            'title' => 'Limit the number of picks',
            'code' => <<<'BLADE'
                <x-avian::multi-select name="reviewers" label="Reviewers" :options="$users" max="2" hint="Up to two reviewers." />
                BLADE,
        ],
        [
            'title' => 'Clearable and taggable',
            'text' => '`clearable` adds a × to the trigger that removes every pick. `taggable` offers whatever the user typed as a new value when no option label matches it — click the Add row or press Enter. A typed tag is its own label, and Backspace in an empty search box takes the last one back into the box for editing. Validate each item on the server: tags are free input.',
            'code' => <<<'BLADE'
                <x-avian::multi-select
                    name="keywords"
                    label="Keywords"
                    :options="$suggestedKeywords"
                    :value="old('keywords', $post->keywords)"
                    clearable
                    taggable
                />
                BLADE,
        ],
        [
            'title' => 'Custom option markup',
            'text' => '`label` is the text shown on the chip and used for searching; the slot is how the row looks in the dropdown.',
            'code' => <<<'BLADE'
                <x-avian::multi-select name="user_ids" label="Members" :value="$memberIds">
                    @foreach ($users as $user)
                        <x-avian::multi-select.option :value="$user->id" :label="$user->name" :selected="$memberIds">
                            <strong>{{ $user->name }}</strong> <small>{{ $user->email }}</small>
                        </x-avian::multi-select.option>
                    @endforeach
                </x-avian::multi-select>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'wire:model binds the whole array (any modifier works), and a server-side change to the property updates the chips.',
            'code' => <<<'BLADE'
                <x-avian::multi-select wire:model.live="tags" :options="$tagOptions" label="Tags" />

                // Livewire component
                public array $tags = [];
                BLADE,
        ],
        [
            'title' => 'Picks outside a limited option list',
            'text' => 'Chip labels come from `options`, so a pick that is missing from them shows its raw value (42 instead of Jane Doe). When the options come from a limited query, merge the current picks in. Filtering happens in the browser, so the extra rows can stay in the list. Merge with `+`, not array_merge(), which renumbers integer ids.',
            'code' => <<<'BLADE'
                {{-- Blade --}}
                <x-avian::multi-select wire:model.live="userIds" :value="$userIds" :options="$this->userOptions" label="Members" />

                // Livewire component
                public array $userIds = [];

                #[Computed]
                public function userOptions(): array
                {
                    $options = User::query()->orderBy('name')->limit(50)->pluck('name', 'id')->all();

                    $missing = array_diff($this->userIds, array_keys($options));

                    return $missing === []
                        ? $options
                        : User::whereKey($missing)->pluck('name', 'id')->all() + $options;
                }
                BLADE,
        ],
        [
            'title' => 'Picks outside the list in slot mode (custom rows)',
            'text' => 'With custom row markup, render the current picks first, then the query results without them. Each row\'s wire:key is derived from its value, so no value may appear twice. Without `options`, the server cannot name the picks, so on a full page load the first-paint chips show raw values until Alpine starts and the rows register their labels.',
            'code' => <<<'BLADE'
                {{-- Blade --}}
                <x-avian::multi-select wire:model.live="userIds" :value="$userIds" label="Members">
                    @foreach ($this->selectedUsers as $user)
                        <x-avian::multi-select.option :value="$user->id" :label="$user->name" :selected="$userIds">
                            <strong>{{ $user->name }}</strong> <small>{{ $user->email }}</small>
                        </x-avian::multi-select.option>
                    @endforeach

                    @foreach ($this->users->whereNotIn('id', $userIds) as $user)
                        <x-avian::multi-select.option :value="$user->id" :label="$user->name" :selected="$userIds">
                            <strong>{{ $user->name }}</strong> <small>{{ $user->email }}</small>
                        </x-avian::multi-select.option>
                    @endforeach
                </x-avian::multi-select>

                // Livewire component
                public array $userIds = [];

                #[Computed]
                public function selectedUsers(): Collection
                {
                    return User::whereKey($this->userIds)->orderBy('name')->get();
                }

                #[Computed]
                public function users(): Collection
                {
                    return User::query()->orderBy('name')->limit(50)->get();
                }
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Multi select', 'subtitle' => 'Pick several values as chips'])
<p class="aui-showcase-lead">
    A searchable dropdown for choosing more than one value — tags, skills, team members. Picked values
    appear as removable chips inside the field and are submitted as an array.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::multi-select
            name="multi_skills"
            label="Skills"
            placeholder="Pick a few skills"
            :options="['php' => 'PHP', 'laravel' => 'Laravel', 'js' => 'JavaScript', 'css' => 'CSS', 'sql' => 'SQL', 'go' => 'Go']"
            :value="['php', 'laravel']"
        />
        <x-avian::multi-select
            name="multi_reviewers"
            label="Reviewers"
            max="2"
            hint="Up to two reviewers (max=2)."
            :options="['ada' => 'Ada Lovelace', 'grace' => 'Grace Hopper', 'alan' => 'Alan Turing', 'linus' => 'Linus Torvalds']"
        />
        <x-avian::multi-select
            name="multi_keywords"
            label="Keywords (clearable, taggable)"
            hint="Type a keyword that is not listed and press Enter to add it."
            clearable
            taggable
            :options="['laravel' => 'Laravel', 'livewire' => 'Livewire', 'alpine' => 'Alpine.js']"
            :value="['laravel', 'ui kit']"
        />
        <x-avian::multi-select
            name="multi_tags"
            label="Tags"
            required
            error="Choose at least one tag."
            :options="['news' => 'News', 'guide' => 'Guide', 'release' => 'Release']"
        />
        <x-avian::multi-select
            name="multi_locked"
            label="Locked"
            disabled
            :options="['a' => 'Alpha', 'b' => 'Beta']"
            :value="['a']"
        />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Click an option to toggle it; the dropdown stays open so you can keep picking. Click outside or press Esc to close.</li>
        <li>Remove a value with the × on its chip, with Backspace in an empty search box, or with <em>Clear</em> in the dropdown.</li>
        <li>With <code>taggable</code>, a term that matches no label shows an <em>Add "…"</em> row; Enter adds it too. Backspace in an empty search box takes a typed tag back for editing.</li>
        <li>One hidden <code>&lt;input name="skills[]"&gt;</code> is rendered per value. When nothing is picked, nothing is sent — use <code>$request-&gt;input('skills', [])</code>.</li>
        <li>Requires Alpine and the package script (<code>&lt;x-avian::scripts /&gt;</code>) loaded before Alpine.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
