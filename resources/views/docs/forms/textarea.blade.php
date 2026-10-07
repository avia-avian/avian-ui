@php
    $props = [
        ['name', 'string|null', 'null', 'Textarea name; also the key for validation errors and old input.'],
        ['value', 'string|null', 'null', 'Initial content. Falls back to old input, then to the slot.'],
        ['rows', 'int', '4', 'Visible height in text lines. The user can still resize it vertically.'],
        ['label', 'string|null', 'null', 'Label shown above the textarea.'],
        ['hint', 'string|null', 'null', 'Helper text under the textarea.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the <textarea>.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::textarea name="notes" label="Notes" rows="3" placeholder="Anything we should know?" />
                BLADE,
        ],
        [
            'title' => 'Initial content',
            'text' => 'Pass it with :value, or put it in the slot. Priority is: value → old input → slot, so the slot is a good place for a default that old input should replace.',
            'code' => <<<'BLADE'
                <x-avian::textarea name="bio" label="Bio" :value="old('bio', $user->bio)" />

                <x-avian::textarea name="message" label="Message">Hello, I would like to…</x-avian::textarea>
                BLADE,
        ],
        [
            'title' => 'Length limit with a hint',
            'code' => <<<'BLADE'
                <x-avian::textarea name="summary" label="Summary" maxlength="280" hint="Up to 280 characters." />
                BLADE,
        ],
        [
            'title' => 'Full-width in a grid',
            'code' => <<<'BLADE'
                <div class="aui-form-grid">
                    <x-avian::input name="title" label="Title" />
                    <x-avian::input name="slug" label="Slug" />
                    <x-avian::textarea class="aui-form-full" name="body" label="Body" rows="6" />
                </div>
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'code' => <<<'BLADE'
                <x-avian::textarea wire:model.blur="description" name="description" label="Description" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Textarea', 'subtitle' => 'Multi-line text'])
<p class="aui-showcase-lead">
    A multi-line text field for notes, descriptions and messages. It shares the input's label, hint,
    validation and old-input behaviour.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::textarea name="textarea_notes" label="Notes" rows="3" placeholder="Anything we should know?" hint="Optional." />
        <x-avian::textarea name="textarea_reason" label="Reason" rows="3" required error="Please tell us why.">Too short</x-avian::textarea>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li><code>rows</code> sets the starting height; the textarea can be resized vertically by the user.</li>
        <li>Content is escaped, so user input with HTML is safe to re-display.</li>
        <li>With <code>wire:model</code> the component does not read old input.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
