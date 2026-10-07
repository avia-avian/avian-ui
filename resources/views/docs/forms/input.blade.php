@php
    $props = [
        ['name', 'string|null', 'null', 'Input name; also the key for validation errors and old input.'],
        ['type', 'string', "'text'", 'Any native type: text, email, password, number, tel, url, search, date, time…'],
        ['value', 'mixed', 'null', 'Initial value. Falls back to old input (never for password, and not when wire:model is set).'],
        ['label', 'string|null', 'null', 'Label shown above the input.'],
        ['hint', 'string|null', 'null', 'Helper text under the input.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height. Omit for the default size.'],
        ['icon', 'string|null', 'null', 'Icon class (e.g. "fas fa-envelope") drawn inside the input on the left.'],
        ['prefix', 'string|null', 'null', 'Text addon attached to the left edge (e.g. "Rp", "https://").'],
        ['suffix', 'string|null', 'null', 'Text addon attached to the right edge (e.g. ".com", "kg").'],
        ['prepend (slot)', 'slot', '—', 'Buttons attached to the left edge, outside any prefix.'],
        ['append (slot)', 'slot', '—', 'Buttons attached to the right edge, outside any suffix — e.g. a search or copy button.'],
        ['numeric', 'bool', 'false', 'Money mask: formats thousands as the user types. Needs the @alpinejs/mask plugin.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the <input> without label/hint/error.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'code' => <<<'BLADE'
                <x-avian::input name="name" label="Full name" placeholder="Ada Lovelace" required />
                <x-avian::input name="email" type="email" label="Email" hint="We never share it." />
                <x-avian::input name="password" type="password" label="Password" />
                BLADE,
        ],
        [
            'title' => 'Editing an existing record',
            'text' => 'An explicit :value always wins over old input. Wrap it in old() so the user keeps their edits when validation fails and the page redirects back.',
            'code' => <<<'BLADE'
                <x-avian::input name="name" label="Full name" :value="old('name', $user->name)" />
                BLADE,
        ],
        [
            'title' => 'Icon, prefix and suffix',
            'text' => '`icon` sits inside the field; `prefix` and `suffix` are attached text addons. They can be combined.',
            'code' => <<<'BLADE'
                <x-avian::input name="email" label="Email" icon="fas fa-envelope" />
                <x-avian::input name="website" label="Website" prefix="https://" suffix=".com" />
                <x-avian::input name="weight" type="number" label="Weight" suffix="kg" />
                BLADE,
        ],
        [
            'title' => 'Input with a button',
            'text' => 'Put buttons in the `append` or `prepend` slot and they join the input edge. Match the button size to the input size. Validation, old input and the label keep working as usual.',
            'code' => <<<'BLADE'
                <x-avian::form method="GET" action="{{ route('orders.index') }}">
                    <x-avian::input name="q" icon="fas fa-search" placeholder="Search orders" :field="false">
                        <x-slot:append>
                            <x-avian::button type="submit">Search</x-avian::button>
                        </x-slot:append>
                    </x-avian::input>
                </x-avian::form>

                <x-avian::input name="invite_link" label="Invite link" :value="$link" readonly>
                    <x-slot:append>
                        <x-avian::button variant="light" icon="fas fa-copy" icon-only label="Copy"
                            x-data x-on:click="navigator.clipboard.writeText('{{ $link }}')" />
                    </x-slot:append>
                </x-avian::input>
                BLADE,
        ],
        [
            'title' => 'Stepper and prepended buttons',
            'text' => 'Both slots can hold several buttons, and they combine with prefix / suffix text.',
            'code' => <<<'BLADE'
                <div x-data>
                    <x-avian::input name="qty" type="number" label="Quantity" value="1" suffix="pcs" x-ref="qty">
                        <x-slot:prepend>
                            <x-avian::button variant="light" icon="fas fa-minus" icon-only label="Decrease" x-on:click="$refs.qty.stepDown()" />
                        </x-slot:prepend>
                        <x-slot:append>
                            <x-avian::button variant="light" icon="fas fa-plus" icon-only label="Increase" x-on:click="$refs.qty.stepUp()" />
                        </x-slot:append>
                    </x-avian::input>
                </div>
                BLADE,
        ],
        [
            'title' => 'Money / numeric input',
            'text' => '`numeric` switches to a text input with inputmode="decimal" and an Alpine money mask, so 1500000 displays as 1,500,000. The submitted value contains the separators — strip them before validating, e.g. in prepareForValidation().',
            'code' => <<<'BLADE'
                <x-avian::input name="budget" label="Budget" prefix="Rp" numeric />

                // FormRequest
                protected function prepareForValidation(): void
                {
                    $this->merge(['budget' => str_replace(',', '', $this->budget)]);
                }
                BLADE,
        ],
        [
            'title' => 'Sizes and states',
            'code' => <<<'BLADE'
                <x-avian::input name="q" size="sm" placeholder="Small" />
                <x-avian::input name="q" size="lg" placeholder="Large" />
                <x-avian::input name="code" label="Code" disabled value="AUI-001" />
                <x-avian::input name="code" label="Code" readonly value="AUI-001" />
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'Any wire:model modifier works. When wire:model is present the component never pre-fills old input, so Livewire stays the single source of truth.',
            'code' => <<<'BLADE'
                <x-avian::input wire:model.live.debounce.300ms="search" icon="fas fa-search" placeholder="Search" />
                <x-avian::input wire:model="form.email" name="form.email" label="Email" />
                BLADE,
        ],
        [
            'title' => 'Inside a table or toolbar',
            'text' => ':field="false" renders only the <input>, without the wrapper and its bottom margin.',
            'code' => <<<'BLADE'
                <td><x-avian::input name="items[0][qty]" type="number" size="sm" :field="false" /></td>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Input', 'subtitle' => 'Single-line text fields'])
<p class="aui-showcase-lead">
    A text field with label, hint and validation built in. It accepts every native input type and adds
    icons, text addons and a money mask on top.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::input name="input_name" label="Full name" placeholder="Ada Lovelace" required />
        <x-avian::input name="input_email" type="email" label="Email" icon="fas fa-envelope" hint="We never share it." />
        <x-avian::input name="input_website" label="Website" prefix="https://" suffix=".com" />
        <x-avian::input name="input_budget" label="Budget" prefix="Rp" numeric placeholder="0" />
        <x-avian::input name="input_username" label="Username" value="ada" error="This username is already taken." />
        <x-avian::input name="input_code" label="Code (disabled)" value="AUI-001" disabled />
        <x-avian::input name="input_search" label="Search with a button" icon="fas fa-search" placeholder="Search orders">
            <x-slot:append>
                <x-avian::button>Search</x-avian::button>
            </x-slot:append>
        </x-avian::input>
        <x-avian::input name="input_invite" label="Invite link" value="https://example.test/invite/8F2K" readonly>
            <x-slot:append>
                <x-avian::button variant="light" icon="fas fa-copy" icon-only label="Copy" />
            </x-slot:append>
        </x-avian::input>
        <div x-data>
            <x-avian::input name="input_qty" type="number" label="Quantity" value="1" suffix="pcs" x-ref="qty">
                <x-slot:prepend>
                    <x-avian::button variant="light" icon="fas fa-minus" icon-only label="Decrease" x-on:click="$refs.qty.stepDown()" />
                </x-slot:prepend>
                <x-slot:append>
                    <x-avian::button variant="light" icon="fas fa-plus" icon-only label="Increase" x-on:click="$refs.qty.stepUp()" />
                </x-slot:append>
            </x-avian::input>
        </div>
        <x-avian::input name="input_small" size="sm" label="Small" placeholder="Small control" />
        <x-avian::input name="input_large" size="lg" label="Large" placeholder="Large control" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The <code>id</code> is generated from the name, and the label's <code>for</code> points at it — clicking the label focuses the input.</li>
        <li>When validation fails, the first message for the name appears under the input, the border turns red and <code>aria-invalid="true"</code> is set.</li>
        <li>After a redirect back, the input is re-filled from old input. Password inputs are never re-filled.</li>
        <li><code>numeric</code> needs Alpine's mask plugin (<code>@alpinejs/mask</code>) loaded before Alpine starts.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
