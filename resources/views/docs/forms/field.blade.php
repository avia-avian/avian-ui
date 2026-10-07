@php
    $props = [
        ['label', 'string|null', 'null', '<x-avian::field>: text of the label rendered above the slot.'],
        ['for', 'string|null', 'null', '<x-avian::field> / <x-avian::label>: id of the control the label points at, so clicking the label focuses it.'],
        ['hint', 'string|null', 'null', '<x-avian::field>: muted helper text rendered under the slot.'],
        ['error', 'string|null', 'null', '<x-avian::field>: error message rendered under the slot (below the hint).'],
        ['required', 'bool', 'false', '<x-avian::field> / <x-avian::label>: adds the red asterisk after the label. Visual only.'],
        ['bare', 'bool', 'false', '<x-avian::field>: render only the slot, without the wrapper, label, hint or error.'],
        ['name', 'string|null', 'null', '<x-avian::error>: field name whose first validation message is shown.'],
        ['error-bag', 'string|null', 'null', '<x-avian::error>: named error bag to read from instead of "default".'],
    ];

    $sharedProps = [
        ['name', 'Name of the input. Also the key used to find its validation error and old input.'],
        ['id', "Defaults to \"aui-\" + the name with [ ] . _ turned into dashes (e.g. user[email] → aui-user-email)."],
        ['label', 'Label text above the control.'],
        ['hint', 'Helper text under the control.'],
        ['error', 'Force an error message. When omitted, the first message for `name` in $errors is used.'],
        ['error-bag', 'Read errors from a named bag, e.g. $request->validateWithBag(\'login\', ...).'],
        ['required', 'Adds the asterisk and the native required attribute.'],
        ['value', 'Initial value. When omitted, old input for `name` is used (skipped when wire:model is present).'],
        [':field="false"', 'Drop the label/hint/error wrapper and render only the control — for tables, toolbars or your own layout.'],
    ];

    $examples = [
        [
            'title' => 'Wrap a custom control',
            'text' => 'Use <x-avian::field> for anything that is not an Avian control — a native colour picker, a third-party widget, a group of buttons — so it still gets the same label, hint and error spacing.',
            'code' => <<<'BLADE'
                <x-avian::field label="Brand colour" for="brand_color" hint="Used for buttons and links." :error="$errors->first('brand_color')">
                    <input type="color" id="brand_color" name="brand_color" value="#008d4c">
                </x-avian::field>
                BLADE,
        ],
        [
            'title' => 'Build a field by hand',
            'text' => 'The three building blocks are also available on their own when you need full control of the markup.',
            'code' => <<<'BLADE'
                <x-avian::label for="code" required>Voucher code</x-avian::label>
                <input id="code" name="code" class="aui-input">
                <x-avian::hint>Printed on the back of the card.</x-avian::hint>
                <x-avian::error name="code" />
                BLADE,
        ],
        [
            'title' => 'Error messages',
            'text' => '<x-avian::error> prints nothing when there is no message, so it is safe to leave in place. It resolves array names (items[0][qty] → items.0.qty) and can read a named bag. Pass a slot to override the message text.',
            'code' => <<<'BLADE'
                <x-avian::error name="email" />
                <x-avian::error name="items[0][qty]" />
                <x-avian::error name="password" error-bag="login" />

                @error('terms')
                    <x-avian::error>Please accept the terms to continue.</x-avian::error>
                @enderror
                BLADE,
        ],
        [
            'title' => 'Named error bags',
            'text' => 'When a page has two forms (login + register), validate each into its own bag and point the controls at it.',
            'code' => <<<'BLADE'
                // Controller
                $request->validateWithBag('login', ['email' => 'required|email']);

                {{-- View --}}
                <x-avian::input name="email" label="Email" error-bag="login" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Field, label, hint & error', 'subtitle' => 'The wrapper every form control is built on'])
<p class="aui-showcase-lead">
    Every Avian form control is rendered inside <code>&lt;x-avian::field&gt;</code>, which stacks a label,
    the control, a hint and an error message with consistent spacing. You rarely use it directly —
    but it is there for custom controls, and <code>&lt;x-avian::label&gt;</code>,
    <code>&lt;x-avian::hint&gt;</code> and <code>&lt;x-avian::error&gt;</code> can be used on their own.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::field label="Volume" for="field_volume" hint="Wraps a native range input.">
            <input type="range" id="field_volume" name="field_volume" min="0" max="100" value="40" style="width: 100%">
        </x-avian::field>

        <x-avian::field label="Voucher code" for="field_code" required error="This voucher has expired.">
            <input id="field_code" name="field_code" class="aui-input aui-input-invalid" value="SUMMER-22">
        </x-avian::field>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Props shared by every control</h2>
    <p class="aui-showcase-text">
        Input, textarea, select, searchable select, multi select, datepicker, file, checkbox, radio and switch
        all accept these, and they behave the same way everywhere:
    </p>

    <div class="aui-showcase-props">
        <x-avian::table :headers="['Prop', 'What it does']" :hover="false">
            @foreach ($sharedProps as [$prop, $description])
                <tr>
                    <td><code>{{ $prop }}</code></td>
                    <td>{{ $description }}</td>
                </tr>
            @endforeach
        </x-avian::table>
    </div>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
