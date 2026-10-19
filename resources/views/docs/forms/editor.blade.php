@php
    $props = [
        ['name', 'string|null', 'null', 'Hidden input name the HTML submits under; also the key for validation errors and old input.'],
        ['value', 'string|null', 'null', 'Initial HTML. Falls back to old input, then to the slot.'],
        ['toolbar', "string|array|false", "'default'", 'Buttons in order, "|" between groups: a preset (default, minimal, full), a space-separated string, an array, or false for none.'],
        ['nodes', 'string|array|null', 'null', 'Extra nodes and marks the content may hold on top of the toolbar\'s, e.g. "h4 code-block table". "heading" allows h1–h6.'],
        ['placeholder', 'string|null', 'null', 'Shown while the editor is empty.'],
        ['min-height', 'int|string|null', 'null', 'Height of the writing area (160px by default). A number is in pixels.'],
        ['colors', 'array', '10 colours', 'Swatches offered by the color button, any CSS colour.'],
        ['font-sizes', 'array', "['12px' … '36px']", 'Sizes offered by the font-size select.'],
        ['code-highlight', 'bool', 'false', 'Colour code blocks by language. Needs the code add-on: <x-avian::scripts editor="code" />.'],
        ['table-resizable', 'bool', 'false', 'Let table columns be dragged wider or narrower.'],
        ['max', 'int|null', 'null', 'Character limit: typing stops there and a counter shows under the editor. It counts text, not HTML; validate on the server too.'],
        ['counter', 'bool', 'false', 'Show the character counter without a limit.'],
        ['upload', 'string|null', 'null', 'Livewire method that stores the uploaded file and returns its URL (or [\'url\' => …, \'name\' => …]).'],
        ['upload-model', 'string', "'editorUpload'", 'The WithFileUploads property the file is uploaded into before `upload` runs.'],
        ['upload-url', 'string|null', 'null', 'Without Livewire: POST endpoint that receives the file as "file" and answers {"url": …}.'],
        ['accept', 'string|array', "'image/png, image/jpeg, image/gif, image/webp'", 'File types that may be uploaded: MIME types, wildcards (image/*) or extensions (.pdf).'],
        ['label', 'string|null', 'null', 'Label shown above the editor.'],
        ['hint', 'string|null', 'null', 'Helper text under the editor.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors (and from upload-model with upload).'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label. Validate on the server: a hidden input is never checked by the browser.'],
        ['disabled', 'bool', 'false', 'Read-only: the content shows but cannot be edited. Follows a Livewire re-render.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the editor.'],
    ];

    $examples = [
        [
            'title' => '1. Load the editor script in your layout (once)',
            'text' => 'Tiptap and every extension the editor uses come bundled in a separate file, so pages without an editor never download it. No npm install needed. Syntax highlighting (about 200 KB) and emoji (about 540 KB) are add-ons: name them to load them too.',
            'code' => <<<'BLADE'
                <x-avian::scripts editor />

                <x-avian::scripts editor="code emoji" />
                BLADE,
        ],
        [
            'title' => '2. Use the component',
            'code' => <<<'BLADE'
                <x-avian::editor name="body" label="Body" placeholder="Write something…" />
                BLADE,
        ],
        [
            'title' => 'Pick the buttons',
            'text' => 'List the toolbar items in order; "|" starts a new group. Items: paragraph, h1–h6, font-size, bold, italic, underline, strike, code, subscript, superscript, highlight, color, link, bullet-list, ordered-list, task-list, blockquote, details, code-block, horizontal-rule, hard-break, table, image, file, emoji, align-left, align-center, align-right, align-justify, clear, undo, redo.',
            'code' => <<<'BLADE'
                <x-avian::editor name="note" toolbar="bold italic | bullet-list ordered-list | link" />

                <x-avian::editor name="note" :toolbar="['h2', 'h3', '|', 'bold', 'italic', '|', 'undo', 'redo']" />
                BLADE,
        ],
        [
            'title' => 'Presets',
            'text' => 'default is headings, basic marks, lists, quote, link and undo/redo; minimal drops the headings, quote and history; full has every item.',
            'code' => <<<'BLADE'
                <x-avian::editor name="comment" toolbar="minimal" />

                <x-avian::editor name="article" toolbar="full" :min-height="320" />
                BLADE,
        ],
        [
            'title' => 'Control which nodes the content may hold',
            'text' => 'The editor only keeps what its toolbar can make: pasted content it has no button for is reduced to plain paragraphs and text. nodes allows more without adding a button, for content that comes in by paste or by Markdown shortcuts (## , > , ```).',
            'code' => <<<'BLADE'
                {{-- Bold and italic buttons, but pasted h2/h3 headings and code blocks survive. --}}
                <x-avian::editor name="note" toolbar="bold italic" nodes="h2 h3 code-block" />

                {{-- No toolbar at all: Markdown shortcuts and keyboard shortcuts only. --}}
                <x-avian::editor name="note" :toolbar="false" nodes="bold italic bullet-list" />
                BLADE,
        ],
        [
            'title' => 'Tables',
            'text' => 'The table button opens a grid: hover or use the arrow keys to pick up to 8×8, then click or press Enter. The table gets a header row. While the cursor is in a table a bar appears to add or delete rows and columns, toggle the header row, merge or split selected cells, and delete the table. table-resizable lets columns be dragged wider or narrower; the widths are saved on the table.',
            'code' => <<<'BLADE'
                <x-avian::editor name="specs" toolbar="bold italic | table | undo redo" />

                <x-avian::editor name="specs" toolbar="table" table-resizable />
                BLADE,
        ],
        [
            'title' => 'Collapsible sections and line breaks',
            'text' => 'details wraps the selected blocks in a collapsible section with a title line; press it again inside one to unwrap it. It is saved as <details> with a <summary>, so it folds on the page that shows it too. hard-break adds a line break inside the paragraph, like Shift+Enter.',
            'code' => <<<'BLADE'
                <x-avian::editor name="faq" toolbar="bold italic | details | hard-break | undo redo" />
                BLADE,
        ],
        [
            'title' => 'Character limit',
            'text' => 'max stops typing and pasting at that many characters and shows a counter, which turns red at the limit. counter shows the count without a limit. The count is of text, not HTML, so check it on the server as well.',
            'code' => <<<'BLADE'
                <x-avian::editor name="summary" toolbar="minimal" :max="280" />

                <x-avian::editor name="body" counter />

                // Server side
                $request->validate(['summary' => ['required', 'string', function ($attribute, $value, $fail) {
                    if (mb_strlen(html_entity_decode(strip_tags($value))) > 280) {
                        $fail('The summary may not be longer than 280 characters.');
                    }
                }]]);
                BLADE,
        ],
        [
            'title' => 'Syntax highlighting',
            'text' => 'code-highlight colours code blocks with lowlight (highlight.js), for the common languages. Type ```php (or js, sql, css…) and a space to start a block in that language; without one, the language is guessed. Load the code add-on after the editor bundle.',
            'code' => <<<'BLADE'
                <x-avian::scripts editor="code" />

                <x-avian::editor name="snippet" toolbar="bold code | code-block" code-highlight />
                BLADE,
        ],
        [
            'title' => 'Emoji',
            'text' => 'The emoji button opens a picker with a search. While typing, :smile: becomes 😄, :) becomes 🙂, and :smi lists matches to pick with the arrow keys and Enter. Emoji are saved as <span data-type="emoji" data-name="smile">😄</span>, so allow the data attributes through your sanitizer. Load the emoji add-on after the editor bundle.',
            'code' => <<<'BLADE'
                <x-avian::scripts editor="emoji" />

                <x-avian::editor name="message" toolbar="bold italic | emoji" />
                BLADE,
        ],
        [
            'title' => 'Text color and size',
            'text' => 'color opens a palette, font-size a size list. Both are stored as inline styles on a span. Pass your own colors and font-sizes to match the brand.',
            'code' => <<<'BLADE'
                <x-avian::editor
                    name="notice"
                    toolbar="font-size | bold italic color highlight"
                    :colors="['#008d4c', '#0a1f42', '#ef4444', '#f59e0b']"
                    :font-sizes="['14px', '18px', '24px']"
                />
                BLADE,
        ],
        [
            'title' => 'Upload images and files with Livewire',
            'text' => 'Picked, dropped and pasted files are uploaded straight away: Livewire puts the file in upload-model, then calls the upload method, which stores it and returns its URL. Store uploads in a temporary folder: they only belong to the record once its content is saved (see the cleanup examples below). Images are inserted as images, other files as a link named after the file. A failed validation shows under the editor.',
            'code' => <<<'BLADE'
                use Livewire\WithFileUploads;

                class EditPost extends Component
                {
                    use WithFileUploads;

                    public Post $post;

                    public string $body = '';

                    public $editorUpload;

                    public function storeEditorFile(): string
                    {
                        $this->validate(['editorUpload' => ['required', 'file', 'max:5120', 'mimes:png,jpg,gif,webp,pdf']]);

                        $path = $this->editorUpload->store('editor-tmp', 'public');
                        $this->reset('editorUpload');

                        return Storage::disk('public')->url($path);
                    }
                }

                <x-avian::editor
                    wire:model="body"
                    toolbar="bold italic | link image file"
                    upload="storeEditorFile"
                    upload-model="editorUpload"
                    accept="image/*, application/pdf"
                />
                BLADE,
        ],
        [
            'title' => 'Upload without Livewire',
            'text' => 'upload-url POSTs the file as "file" (with the page\'s CSRF token) and expects {"url": …} back. A 422 response shows its message under the editor.',
            'code' => <<<'BLADE'
                // routes/web.php
                Route::post('/editor/uploads', function (Request $request) {
                    $request->validate(['file' => ['required', 'image', 'max:5120']]);

                    return ['url' => Storage::disk('public')->url($request->file('file')->store('editor-tmp', 'public'))];
                })->middleware('auth');

                <x-avian::editor name="body" toolbar="bold italic | image" :upload-url="url('/editor/uploads')" />
                BLADE,
        ],
        [
            'title' => 'Cleaning up, step 1: keep what the saved content uses',
            'text' => 'Do not delete a file the moment it leaves the editor: undo or cut-and-paste would bring the image back without its file. Settle the files when the content is saved instead: move the temporary uploads the HTML still uses into the record\'s own folder (rewriting their URLs), and delete the files in that folder the HTML no longer uses. Images removed before saving were never moved; images removed from a saved post go now. Put the rewritten HTML back in the editor too, so its URLs point at the new place.',
            'code' => <<<'BLADE'
                public function save(): void
                {
                    $this->validate(['body' => ['required', 'string']]);

                    $this->post->save(); // a new post needs its id for the folder

                    $this->body = $this->claimEditorFiles($this->body, 'posts/'.$this->post->id);

                    $this->post->update(['body' => $this->body]);
                }

                /**
                 * Move the temporary uploads the HTML uses into $folder, and delete
                 * the files in $folder it no longer uses. Returns the rewritten HTML.
                 */
                protected function claimEditorFiles(string $html, string $folder): string
                {
                    $disk = Storage::disk('public');
                    $tmpUrl = $disk->url('editor-tmp/');

                    preg_match_all('#'.preg_quote($tmpUrl, '#').'([^"\'?\#]+)#', $html, $uploads);

                    foreach (array_unique($uploads[1]) as $file) {
                        if ($disk->exists('editor-tmp/'.$file)) {
                            $disk->move('editor-tmp/'.$file, $folder.'/'.$file);
                        }

                        $html = str_replace($tmpUrl.$file, $disk->url($folder.'/'.$file), $html);
                    }

                    preg_match_all('#'.preg_quote($disk->url($folder.'/'), '#').'([^"\'?\#]+)#', $html, $used);

                    $unused = array_diff($disk->files($folder), array_map(fn ($file) => $folder.'/'.$file, $used[1]));

                    $disk->delete($unused);

                    return $html;
                }
                BLADE,
        ],
        [
            'title' => 'Cleaning up, step 2: drop what was never saved',
            'text' => 'Files uploaded into an editor that was closed without saving never leave the temporary folder. Prune it on a schedule; a day\'s grace keeps the images of a draft someone is still writing.',
            'code' => <<<'BLADE'
                // routes/console.php
                use Illuminate\Support\Facades\Schedule;
                use Illuminate\Support\Facades\Storage;

                Schedule::call(function () {
                    $disk = Storage::disk('public');

                    collect($disk->files('editor-tmp'))
                        ->filter(fn (string $file) => $disk->lastModified($file) < now()->subDay()->getTimestamp())
                        ->each(fn (string $file) => $disk->delete($file));
                })->daily();
                BLADE,
        ],
        [
            'title' => 'Initial content',
            'text' => 'Priority is: value → old input → slot. Render the saved HTML back in with :value; the editor parses it against its schema.',
            'code' => <<<'BLADE'
                <x-avian::editor name="body" label="Body" :value="old('body', $post->body)" />

                <x-avian::editor name="body"><p>Dear customer,</p></x-avian::editor>
                BLADE,
        ],
        [
            'title' => 'Set or insert content from outside',
            'text' => 'Two browser events change an editor from a Livewire method, Alpine or plain JS, aimed at it by its name (or its wire:model property). aui-editor-set replaces everything, for a picked template; aui-editor-insert adds HTML at the cursor and keeps the rest, for snippets such as a signature. Both go through the same schema as typing and pasting.',
            'code' => <<<'BLADE'
                // Livewire
                $this->dispatch('aui-editor-set', name: 'body', html: $template->body);
                $this->dispatch('aui-editor-insert', name: 'body', html: $snippet->body);

                {{-- Alpine --}}
                <button type="button" x-on:click="$dispatch('aui-editor-insert', { name: 'body', html: '<p>Kind regards,</p>' })">Add signature</button>

                // Plain JS
                window.AvianUI.setEditor('body', '<p>Dear customer,</p>');
                window.AvianUI.insertIntoEditor('body', '<p>Kind regards,</p>');
                BLADE,
        ],
        [
            'title' => 'Livewire',
            'text' => 'wire:model binds the HTML string. The writing area is wire:ignore\'d, so re-renders never reset the cursor.',
            'code' => <<<'BLADE'
                <x-avian::editor wire:model="content" label="Content" />
                BLADE,
        ],
        [
            'title' => 'Displaying the HTML',
            'text' => 'The editor submits HTML. Treat it as user input: sanitize it on the server (with HTML Purifier, for example) before you echo it unescaped.',
            'code' => <<<'BLADE'
                <div class="aui-editor-prose">{!! $cleanHtml !!}</div>
                BLADE,
        ],
        [
            'title' => 'Bring your own Tiptap',
            'text' => 'An app that already bundles Tiptap can skip the editor script and hand its own build over instead. Editor and StarterKit are required; the rest only for the items that use them.',
            'code' => <<<'BLADE'
                import { Editor } from '@tiptap/core';
                import StarterKit from '@tiptap/starter-kit';
                import { Placeholder, CharacterCount } from '@tiptap/extensions';
                import Highlight from '@tiptap/extension-highlight';
                import Subscript from '@tiptap/extension-subscript';
                import Superscript from '@tiptap/extension-superscript';
                import { TaskList, TaskItem } from '@tiptap/extension-list';
                import Image from '@tiptap/extension-image';
                import TextAlign from '@tiptap/extension-text-align';
                import { Table, TableRow, TableHeader, TableCell } from '@tiptap/extension-table';
                import { TextStyle, Color, FontSize } from '@tiptap/extension-text-style';
                import FileHandler from '@tiptap/extension-file-handler';
                import { Details, DetailsSummary, DetailsContent } from '@tiptap/extension-details';

                // Only for code-highlight and the emoji button:
                import CodeBlockLowlight from '@tiptap/extension-code-block-lowlight';
                import { createLowlight, common } from 'lowlight';
                import { Emoji, emojis } from '@tiptap/extension-emoji';

                window.AvianUITiptap = {
                    Editor, StarterKit, Placeholder, CharacterCount, Highlight, Subscript, Superscript,
                    TaskList, TaskItem, Image, TextAlign, Table, TableRow, TableHeader, TableCell,
                    TextStyle, Color, FontSize, FileHandler, Details, DetailsSummary, DetailsContent,
                    CodeBlockLowlight, lowlight: createLowlight(common), Emoji, emojis,
                };
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Editor', 'subtitle' => 'Rich text powered by Tiptap'])
<p class="aui-showcase-lead">
    A rich text editor that submits HTML. You choose the toolbar buttons, and the editor accepts
    only the nodes and marks those buttons can make. It runs on <a href="https://tiptap.dev" target="_blank" rel="noopener">Tiptap</a>,
    bundled with the package, and can upload images and files through Livewire or a plain endpoint.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::editor
            class="aui-form-full"
            name="editor_body"
            label="Body"
            placeholder="Write something…"
            hint="The default toolbar."
            value="<h2>Release notes</h2><p>This release adds a <strong>rich text editor</strong>. Select some text and try the toolbar, or type <code>## </code> for a heading.</p><ul><li><p>Pick the buttons you need</p></li><li><p>Everything else is stripped on paste</p></li></ul>"
        />
        <x-avian::editor name="editor_comment" label="Comment" toolbar="minimal" placeholder="Leave a comment…" :min-height="100" :max="280" />
        <x-avian::editor name="editor_error" label="Summary" toolbar="bold italic | bullet-list" :min-height="100" required error="Please write a summary." />
        <x-avian::editor
            class="aui-form-full"
            name="editor_full"
            label="Article"
            toolbar="full"
            value="<h3>Everything on</h3><p style='text-align: center'>Centered, with <mark>highlight</mark>, <span style='color: #dc2626'>colour</span>, <span style='font-size: 20px'>size</span> and a <a href='https://tiptap.dev'>link</a>.</p><table><tbody><tr><th><p>Plan</p></th><th><p>Seats</p></th><th><p>Price</p></th></tr><tr><td><p>Starter</p></td><td><p>5</p></td><td><p>$9</p></td></tr><tr><td><p>Team</p></td><td><p>25</p></td><td><p>$29</p></td></tr></tbody></table><ul data-type='taskList'><li data-type='taskItem' data-checked='true'><p>Task lists</p></li><li data-type='taskItem' data-checked='false'><p>Images by URL or upload</p></li></ul><details open><summary>A collapsible section</summary><div data-type='detailsContent'><p>Line one<br>and a line break.</p></div></details><pre><code>composer require avia-avian/avian-ui</code></pre>"
        />
    </div>

    <div class="aui-toolbar" style="margin-top: 12px">
        <x-avian::button size="sm" variant="outline" x-data x-on:click="$dispatch('aui-editor-set', { name: 'editor_comment', html: '<p>Thanks for the report. We are looking into it.</p>' })">Load a template into Comment</x-avian::button>
        <x-avian::button size="sm" variant="outline" x-data x-on:click="$dispatch('aui-editor-insert', { name: 'editor_comment', html: '<p><strong>Kind regards,</strong><br>Support team</p>' })">Insert a signature</x-avian::button>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>The HTML goes to the server in a hidden input named after <code>name</code>. An empty editor submits an empty string, not <code>&lt;p&gt;&lt;/p&gt;</code>.</li>
        <li>The toolbar decides the schema: an editor with only bold and italic turns a pasted heading into a paragraph. Add more with <code>nodes</code>.</li>
        <li>Link and image ask for their URL (and the image for its alt text) in a bar under the toolbar. Empty the link URL to remove the link. With an upload set, the image bar also offers an upload button.</li>
        <li>Uploads happen as soon as a file is picked, dropped or pasted; the file is inserted where it was dropped once the server returns its URL.</li>
        <li>An upload is temporary until the content is saved: on save, move the files the HTML uses into the record's folder and delete the rest; prune the temporary folder on a schedule for editors closed without saving.</li>
        <li><code>aui-editor-set</code> and <code>aui-editor-insert</code> events replace the content or add to it at the cursor, from Livewire or JS. The buttons under the demo use them.</li>
        <li>The toolbar is one Tab stop: Left and Right (or Home and End) move between its buttons and Escape goes back to the text. From the text, <kbd>Alt</kbd>+<kbd>F10</kbd> jumps to the toolbar and <kbd>Ctrl</kbd>/<kbd>Cmd</kbd>+<kbd>K</kbd> opens the link bar. Screen readers announce the hint and error with the editor.</li>
        <li>Wrap stored HTML in <code>aui-editor-prose</code> to display it with the same typography as the editor.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Setup &amp; examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
