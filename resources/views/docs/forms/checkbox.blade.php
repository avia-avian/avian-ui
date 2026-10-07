@php
    $props = [
        ['name', 'string|null', 'null', 'Input name. Use name="roles[]" for a group that submits an array.'],
        ['value', 'string', "'1'", 'Value sent when checked. Nothing is sent when unchecked.'],
        ['checked', 'bool', 'false', 'Initial checked state. (Not read from old input — pass it yourself, see below.)'],
        ['label', 'string|null', 'null', 'Text next to the box. The slot is used when label is omitted.'],
        ['hint', 'string|null', 'null', 'Muted line under the label text.'],
        ['inline', 'bool', 'false', 'Lay checkboxes out side by side instead of stacked.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['field', 'bool', 'true', 'Set :field="false" to drop the wrapper — use it inside groups so the error shows once, under the group.'],
    ];

    $examples = [
        [
            'title' => 'Single checkbox (boolean)',
            'text' => 'An unchecked box sends nothing, so read it with $request->boolean(). Pass `checked` from old input or the model to keep the state.',
            'code' => <<<'BLADE'
                <x-avian::checkbox
                    name="newsletter"
                    label="Send me the newsletter"
                    :checked="old('newsletter', $user->newsletter)"
                />

                $user->newsletter = $request->boolean('newsletter');
                BLADE,
        ],
        [
            'title' => 'Accept the terms',
            'code' => <<<'BLADE'
                <x-avian::checkbox name="terms" label="I accept the terms" hint="You can revoke this at any time." />

                $request->validate(['terms' => ['accepted']]);
                BLADE,
        ],
        [
            'title' => 'Group of checkboxes (array)',
            'text' => 'Give every box the same name ending in [] and its own value. Use :field="false" on each box and one <x-avian::error> for the group.',
            'code' => <<<'BLADE'
                <x-avian::label>Permissions</x-avian::label>

                @foreach (['view' => 'View', 'edit' => 'Edit', 'delete' => 'Delete'] as $value => $text)
                    <x-avian::checkbox
                        name="permissions[]"
                        :value="$value"
                        :label="$text"
                        :checked="in_array($value, old('permissions', $role->permissions))"
                        :field="false"
                        inline
                    />
                @endforeach

                <x-avian::error name="permissions" />
                BLADE,
        ],
        [
            'title' => 'Rich label via the slot',
            'code' => <<<'BLADE'
                <x-avian::checkbox name="terms">
                    I agree to the <a href="/terms">terms of service</a>
                </x-avian::checkbox>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'code' => <<<'BLADE'
                <x-avian::checkbox wire:model.live="showArchived" label="Show archived" />
                <x-avian::checkbox wire:model="selected" value="{{ $row->id }}" :field="false" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Checkbox', 'subtitle' => 'On/off choices and multi-choice groups'])
<p class="aui-showcase-lead">
    A checkbox with its label and an optional hint line. Use a single checkbox for a yes/no answer
    (terms, newsletter), or a group sharing one <code>name[]</code> to pick several options from a short list.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <div class="aui-stack">
            <x-avian::checkbox name="checkbox_terms" label="I accept the terms" hint="You can revoke this at any time." />
            <x-avian::checkbox name="checkbox_newsletter" label="Send me the newsletter" checked />
            <x-avian::checkbox name="checkbox_disabled" label="Disabled option" disabled />
        </div>

        <div>
            <x-avian::label>Permissions</x-avian::label>
            @foreach (['view' => 'View', 'edit' => 'Edit', 'delete' => 'Delete'] as $value => $text)
                <x-avian::checkbox name="checkbox_permissions[]" :value="$value" :label="$text" :checked="$value === 'view'" :field="false" inline />
            @endforeach
            <x-avian::error>Choose at least one permission.</x-avian::error>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The whole row (box + text) is the label, so clicking the text toggles the box.</li>
        <li>Unchecked boxes are not submitted. Use <code>$request-&gt;boolean('name')</code> for a single box and <code>$request-&gt;input('name', [])</code> for a group.</li>
        <li><code>checked</code> is <strong>not</strong> restored from old input automatically — bind it with <code>:checked="old('name', $default)"</code>.</li>
        <li>The generated id includes the value (<code>aui-permissions-edit</code>), so boxes in a group never clash.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
