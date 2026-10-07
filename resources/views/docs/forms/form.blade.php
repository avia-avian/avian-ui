@php
    $props = [
        ['action', 'string|null', 'null', 'The URL the form submits to. Usually a route() call.'],
        ['method', 'string', "'POST'", 'GET, POST, PUT, PATCH or DELETE. PUT/PATCH/DELETE are sent as POST with a hidden _method field (method spoofing).'],
        ['csrf', 'bool', 'true', 'Adds the @csrf token for every method except GET. Set :csrf="false" for forms posting to an external service.'],
        ['files', 'bool', 'false', 'Sets enctype="multipart/form-data". Required whenever the form contains <x-avian::file>.'],
    ];

    $examples = [
        [
            'title' => 'Create form',
            'text' => 'A POST form gets its CSRF token automatically. Add `files` when the form uploads anything.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('records.store') }}" files>
                    <x-avian::input name="name" label="Full name" required />
                    <x-avian::file name="attachment" label="Attachment" />

                    <div class="aui-form-actions">
                        <x-avian::button type="submit" icon="fas fa-check">Save</x-avian::button>
                    </div>
                </x-avian::form>
                BLADE,
        ],
        [
            'title' => 'Update and delete forms (method spoofing)',
            'text' => 'HTML forms only support GET and POST. Pass the real verb and the component adds @method() for you, so it matches Route::put() / Route::delete().',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('records.update', $record) }}" method="PUT">
                    <x-avian::input name="name" label="Full name" :value="$record->name" />
                    <x-avian::button type="submit">Update</x-avian::button>
                </x-avian::form>

                <x-avian::form action="{{ route('records.destroy', $record) }}" method="DELETE">
                    <x-avian::button type="submit" variant="danger">Delete</x-avian::button>
                </x-avian::form>
                BLADE,
        ],
        [
            'title' => 'Search / filter form',
            'text' => 'A GET form never gets a CSRF token, so the query string stays clean.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('records.index') }}" method="GET">
                    <x-avian::input name="q" placeholder="Search records" icon="fas fa-search" />
                </x-avian::form>
                BLADE,
        ],
        [
            'title' => 'Grid layout',
            'text' => 'Wrap fields in .aui-form-grid for two columns (.aui-form-grid-3 / -4 for more). Give a field .aui-form-full to span the whole row, and put buttons in .aui-form-actions for a right-aligned, divided footer.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('records.store') }}">
                    <div class="aui-form-grid">
                        <x-avian::input name="first_name" label="First name" />
                        <x-avian::input name="last_name" label="Last name" />
                        <x-avian::textarea class="aui-form-full" name="notes" label="Notes" />
                    </div>

                    <div class="aui-form-actions">
                        <x-avian::button variant="light" type="reset">Cancel</x-avian::button>
                        <x-avian::button type="submit">Save</x-avian::button>
                    </div>
                </x-avian::form>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Form', 'subtitle' => 'Submission boilerplate and layout helpers'])
<p class="aui-showcase-lead">
    <code>&lt;x-avian::form&gt;</code> renders a normal <code>&lt;form&gt;</code> with the boilerplate
    already done: the CSRF token, method spoofing for PUT/PATCH/DELETE and the multipart encoding for
    uploads. Everything below shows how the form controls fit together; each control has its own page
    in the sidebar.
</p>

<div class="aui-showcase-demo">
    <x-avian::form action="#" method="POST" files onsubmit="event.preventDefault()">
        <div class="aui-form-grid">
            <x-avian::input name="form_name" label="Full name" placeholder="Ada Lovelace" required />
            <x-avian::input name="form_email" type="email" label="Email" icon="fas fa-envelope" />
            <x-avian::select
                name="form_role"
                label="Role"
                placeholder="Choose a role"
                :options="['admin' => 'Administrator', 'editor' => 'Editor', 'viewer' => 'Viewer']"
            />
            <x-avian::datepicker name="form_start_date" label="Start date" placeholder="dd/mm/yyyy" />
            <x-avian::textarea class="aui-form-full" name="form_notes" label="Notes" rows="3" />
        </div>

        <x-avian::switch name="form_active" label="Active" checked />

        <div class="aui-form-actions">
            <x-avian::button variant="light" type="reset">Cancel</x-avian::button>
            <x-avian::button type="submit" icon="fas fa-check">Save</x-avian::button>
        </div>
    </x-avian::form>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Every form control reads validation errors from Laravel's <code>$errors</code> bag and old input from the session by its <code>name</code>. After a failed <code>$request-&gt;validate()</code> the form comes back filled in and with messages under each field — no <code>@@error</code> or <code>old()</code> calls needed.</li>
        <li>Array names work too: <code>name="items[0][qty]"</code> looks up the <code>items.0.qty</code> error and old value.</li>
        <li>Controls are plain HTML inputs, so the form also works without the component — <code>&lt;x-avian::form&gt;</code> just saves the boilerplate.</li>
        <li>Extra attributes (<code>id</code>, <code>class</code>, <code>x-data</code>, <code>wire:submit</code>, <code>onsubmit</code>…) land on the <code>&lt;form&gt;</code> tag.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
