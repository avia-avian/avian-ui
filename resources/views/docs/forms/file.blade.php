@php
    $props = [
        ['name', 'string|null', 'null', 'Input name. Use name="photos[]" together with `multiple` for several files.'],
        ['trigger', 'string', "'Choose file'", 'Text on the button.'],
        ['placeholder', 'string', "'No file chosen'", 'Text shown before a file is picked.'],
        ['icon', 'string|null', "'fas fa-paperclip'", 'Icon on the button. Pass an empty string to remove it.'],
        ['label', 'string|null', 'null', 'Label shown above the control.'],
        ['hint', 'string|null', 'null', 'Helper text under the control — a good place for allowed types and size.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the control.'],
    ];

    $examples = [
        [
            'title' => 'Basic',
            'text' => 'The surrounding form must send multipart data — add `files` to <x-avian::form> (or enctype="multipart/form-data" to a plain form), otherwise the file never reaches the server.',
            'code' => <<<'BLADE'
                <x-avian::form action="{{ route('documents.store') }}" files>
                    <x-avian::file name="attachment" label="Attachment" hint="PDF, max 2 MB." accept=".pdf" />
                </x-avian::form>

                $request->validate(['attachment' => ['required', 'file', 'mimes:pdf', 'max:2048']]);
                $path = $request->file('attachment')->store('documents');
                BLADE,
        ],
        [
            'title' => 'Images only, custom texts',
            'code' => <<<'BLADE'
                <x-avian::file
                    name="avatar"
                    label="Profile photo"
                    accept="image/*"
                    trigger="Upload photo"
                    placeholder="No photo yet"
                    icon="fas fa-image"
                />
                BLADE,
        ],
        [
            'title' => 'Multiple files',
            'text' => 'After picking, the label reads "3 files selected". Validate each file with photos.*.',
            'code' => <<<'BLADE'
                <x-avian::file name="photos[]" label="Photos" multiple accept="image/*" />

                $request->validate([
                    'photos' => ['array', 'max:5'],
                    'photos.*' => ['image', 'max:4096'],
                ]);
                BLADE,
        ],
        [
            'title' => 'Livewire uploads',
            'code' => <<<'BLADE'
                <x-avian::file wire:model="photo" name="photo" label="Photo" />

                // Livewire component
                use WithFileUploads;
                public $photo;
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'File', 'subtitle' => 'Styled file upload'])
<p class="aui-showcase-lead">
    A file input with a proper button and the chosen file name next to it, instead of the browser's
    default control. The real <code>&lt;input type="file"&gt;</code> is still there (visually hidden), so
    uploads, <code>accept</code>, <code>multiple</code> and validation work as usual.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::file name="file_attachment" label="Attachment" hint="PDF, max 2 MB." accept=".pdf" />
        <x-avian::file name="file_avatar" label="Profile photo" accept="image/*" trigger="Upload photo" placeholder="No photo yet" icon="fas fa-image" />
        <x-avian::file name="file_photos[]" label="Photos (multiple)" multiple accept="image/*" />
        <x-avian::file name="file_contract" label="Contract" required error="The contract must be a PDF." />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Clicking the button opens the native file dialog; the label then shows the file name, or "N files selected".</li>
        <li>Browsers never re-fill a file input, so after a failed validation the user has to pick the file again. Keep file rules strict and clear in the hint.</li>
        <li>Uses a small Alpine component (<code>auiFile</code>) from <code>&lt;x-avian::scripts /&gt;</code> for the file name.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
