<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * The config the editor hands to auiEditor().
 *
 * @return array<string, mixed>
 */
function editorConfig(string $html): array
{
    preg_match('/x-data="auiEditor\((.*?)\)"/', $html, $match);

    return (array) json_decode(html_entity_decode($match[1] ?? '{}'), true);
}

/**
 * The toolbar buttons in order, with `|` between groups.
 *
 * @return list<string>
 */
function editorToolbar(string $html): array
{
    preg_match_all('/class="aui-editor-group"|data-aui-command="([^"]+)"/', $html, $matches, PREG_SET_ORDER);

    $items = array_map(fn (array $match): string => $match[1] ?? '|', $matches);

    return array_slice($items, 1);
}

it('renders an editor with the default toolbar, a label and a hidden input', function () {
    $html = Blade::render('<x-avian::editor name="body" label="Body" placeholder="Write…" />');

    expect($html)->toContain('x-data="auiEditor(')
        ->toContain('x-modelable="value"')
        ->toContain('class="aui-editor"')
        ->toContain('role="toolbar"')
        ->toContain('<label class="aui-label" for="aui-body">Body</label>')
        ->toContain('data-aui-id="aui-body"')
        ->toContain('data-aui-label="Body"')
        ->toContain('wire:ignore')
        ->toContain('<input type="hidden" name="body" value="" x-bind:value="value">')
        ->toContain('aria-label="Bold"')
        ->toContain('aria-label="Heading 2"');

    expect(editorToolbar($html))->toBe(['h2', 'h3', '|', 'bold', 'italic', 'underline', 'strike', '|', 'bullet-list', 'ordered-list', 'blockquote', '|', 'link', '|', 'undo', 'redo']);

    expect(editorConfig($html))->toBe([
        'name' => 'body',
        'nodes' => ['bold', 'italic', 'underline', 'strike', 'bullet-list', 'ordered-list', 'blockquote', 'link'],
        'headings' => [2, 3],
        'placeholder' => 'Write…',
        'max' => null,
        'counter' => false,
        'uploads' => null,
        'messages' => null,
    ]);
});

it('builds the toolbar and the schema from a custom item list', function () {
    $html = Blade::render('<x-avian::editor name="note" toolbar="bold italic | | h1 bogus | task-list align-center" />');

    expect(editorToolbar($html))->toBe(['bold', 'italic', '|', 'h1', '|', 'task-list', 'align-center'])
        ->and(editorConfig($html)['nodes'])->toBe(['bold', 'italic', 'task-list', 'text-align'])
        ->and(editorConfig($html)['headings'])->toBe([1])
        ->and($html)->not->toContain('bogus');
});

it('takes the toolbar as an array or a preset', function () {
    $array = Blade::render('<x-avian::editor name="a" :toolbar="[\'bold\', \'|\', \'link\']" />');
    $minimal = Blade::render('<x-avian::editor name="b" toolbar="minimal" />');
    $full = Blade::render('<x-avian::editor name="c" toolbar="full" />');

    expect(editorToolbar($array))->toBe(['bold', '|', 'link'])
        ->and(editorToolbar($minimal))->toBe(['bold', 'italic', 'underline', '|', 'bullet-list', 'ordered-list', '|', 'link'])
        ->and(editorConfig($full)['nodes'])->toContain('highlight', 'color', 'font-size', 'table', 'image', 'text-align', 'code-block', 'horizontal-rule')
        ->and(editorConfig($full)['headings'])->toBe([1, 2, 3]);
});

it('allows extra nodes on top of the toolbar', function () {
    $html = Blade::render('<x-avian::editor name="note" toolbar="bold" nodes="h3 bogus code-block, link" />');

    expect(editorToolbar($html))->toBe(['bold'])
        ->and(editorConfig($html)['nodes'])->toBe(['bold', 'code-block', 'link'])
        ->and(editorConfig($html)['headings'])->toBe([3]);

    expect(editorConfig(Blade::render('<x-avian::editor name="n" toolbar="bold" nodes="heading" />'))['headings'])
        ->toBe([1, 2, 3, 4, 5, 6]);
});

it('renders no toolbar when it is turned off and keeps the default schema', function () {
    $html = Blade::render('<x-avian::editor name="note" :toolbar="false" />');

    expect($html)->not->toContain('aui-editor-toolbar')
        ->not->toContain('aui-editor-panel')
        ->and(editorConfig($html)['headings'])->toBe([2, 3])
        ->and(editorConfig($html)['nodes'])->toContain('bold', 'link');
});

it('only renders the url bar when the toolbar has a link or image button', function () {
    expect(Blade::render('<x-avian::editor name="a" toolbar="bold" />'))->not->toContain('aui-editor-panel')
        ->and(Blade::render('<x-avian::editor name="b" toolbar="image" />'))->toContain('aui-editor-panel');
});

it('passes the initial html escaped, from the value, old input or slot', function () {
    $html = Blade::render('<x-avian::editor name="body" value="<p>Hi <script>x</script></p>" />');

    expect($html)->toContain('value="&lt;p&gt;Hi &lt;script&gt;x&lt;/script&gt;&lt;/p&gt;"')
        ->not->toContain('<script>x</script>');

    expect(Blade::render('<x-avian::editor name="intro"><p>Hello</p></x-avian::editor>'))
        ->toContain('value="&lt;p&gt;Hello&lt;/p&gt;"');

    session()->flashInput(['body' => '<p>Edited</p>']);

    expect(Blade::render('<x-avian::editor name="body" value="<p>Saved</p>" />'))
        ->toContain('value="&lt;p&gt;Edited&lt;/p&gt;"');
});

it('binds a wired editor through x-modelable on its wrapper', function () {
    session()->flashInput(['content' => '<p>Old</p>']);

    $html = Blade::render('<x-avian::editor wire:model="content" class="mt-2" />');

    expect($html)->toContain('wire:model="content"')
        ->toContain('class="aui-editor mt-2"')
        ->toContain('data-aui-id="aui-content"')
        ->not->toContain('Old')
        ->not->toContain('type="hidden"');
});

it('shows a validation error and marks the editor invalid', function () {
    view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['body' => ['The body is required.']])));

    $html = Blade::render('<x-avian::editor name="body" />');

    expect($html)->toContain('aui-editor aui-editor-invalid')
        ->toContain('data-aui-invalid="true"')
        ->toContain('The body is required.');
});

it('renders a disabled editor with a minimum height', function () {
    $html = Blade::render('<x-avian::editor name="body" disabled :min-height="300" />');

    expect($html)->toContain('aui-editor is-disabled')
        ->toContain('data-aui-disabled="true"')
        ->toContain('--aui-editor-min-height: 300px');
});

it('keeps the disabled state out of x-data so a livewire morph can change it', function () {
    $html = Blade::render('<x-avian::editor name="body" />');

    expect($html)->toContain('data-aui-disabled="false"')
        ->and(editorConfig($html))->not->toHaveKey('disabled');
});

it('offers collapsible sections and line breaks', function () {
    $html = Blade::render('<x-avian::editor name="faq" toolbar="bold | details hard-break" />');

    expect(editorToolbar($html))->toBe(['bold', '|', 'details', 'hard-break'])
        ->and(editorConfig($html)['nodes'])->toBe(['bold', 'details'])
        ->and(editorConfig($html)['messages'])->toBe(['expand' => 'Expand section', 'collapse' => 'Collapse section'])
        ->and($html)->toContain('aria-label="Collapsible section"')
        ->toContain('aria-label="Line break"')
        ->toContain("x-bind:aria-pressed=\"isActive('details')")
        ->not->toContain("x-bind:aria-pressed=\"isActive('hard-break')");

    expect(editorToolbar(Blade::render('<x-avian::editor name="a" toolbar="full" />')))->toContain('details', 'hard-break')
        ->and(editorConfig(Blade::render('<x-avian::editor name="b" toolbar="bold" nodes="details" />'))['nodes'])->toBe(['bold', 'details']);
});

it('limits the characters and shows a counter', function () {
    $html = Blade::render('<x-avian::editor name="summary" :max="280" />');

    expect(editorConfig($html))->toMatchArray(['max' => 280, 'counter' => true])
        ->and($html)->toContain('class="aui-editor-count"')
        ->toContain('x-text="characters()"')
        ->toContain('/ 280')
        ->toContain('title="Up to 280 characters"');

    $counter = Blade::render('<x-avian::editor name="body" counter />');

    expect(editorConfig($counter))->toMatchArray(['max' => null, 'counter' => true])
        ->and($counter)->toContain('aui-editor-count')
        ->toContain('characters')
        ->not->toContain('/ ');

    expect(Blade::render('<x-avian::editor name="body" />'))->not->toContain('aui-editor-count')
        ->and(editorConfig(Blade::render('<x-avian::editor name="body" max="0" />'))['counter'])->toBeFalse();
});

it('renders just the editor without the field wrapper', function () {
    expect(Blade::render('<x-avian::editor name="body" label="Body" :field="false" />'))
        ->not->toContain('aui-field')
        ->not->toContain('<label');
});

it('drops the file button when there is nowhere to upload to', function () {
    expect(editorToolbar(Blade::render('<x-avian::editor name="a" toolbar="bold | image file" />')))->toBe(['bold', '|', 'image'])
        ->and(editorToolbar(Blade::render('<x-avian::editor name="b" toolbar="bold | image file" upload-url="/uploads" />')))->toBe(['bold', '|', 'image', 'file']);
});

it('renders the text color palette and the font size select', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::editor name="a" toolbar="font-size | color" :colors="['#ff0000', '#00ff00']" :font-sizes="['14px', '22px']" />
        BLADE);

    expect(editorConfig($html)['nodes'])->toBe(['font-size', 'color'])
        ->and($html)->toContain('<select')
        ->toContain('x-on:change="setFontSize($event.target.value)"')
        ->toContain('<option value="22px">22</option>')
        ->toContain('style="--aui-swatch: #ff0000"')
        ->toContain('style="--aui-swatch: #00ff00"')
        ->toContain('aria-label="Text color"')
        ->toContain('aria-haspopup="true"');
});

it('renders the table bar whenever tables are allowed', function () {
    $html = Blade::render('<x-avian::editor name="a" toolbar="bold table" />');

    expect($html)->toContain('aui-editor-table-bar')
        ->toContain('data-aui-command="table-row-after"')
        ->toContain('data-aui-command="table-merge"')
        ->toContain('Delete table');

    expect(Blade::render('<x-avian::editor name="b" toolbar="bold" nodes="table" />'))->toContain('aui-editor-table-bar')
        ->and(Blade::render('<x-avian::editor name="c" toolbar="bold" />'))->not->toContain('aui-editor-table-bar');
});

it('configures uploads to a livewire method', function () {
    $html = Blade::render('<x-avian::editor wire:model="body" toolbar="image file" upload="storeEditorFile" upload-model="photo" accept="image/*, .pdf" />');

    expect(editorConfig($html)['uploads'])->toBe([
        'method' => 'storeEditorFile',
        'model' => 'photo',
        'url' => null,
        'accept' => ['image/*', '.pdf'],
    ])
        ->and(editorConfig($html)['messages'])->toHaveKeys(['failed', 'rejected'])
        ->and($html)->toContain('type="file"')
        ->toContain('x-on:change="picked($event)"')
        ->toContain('aui-editor-status')
        ->toContain('x-on:click="pickFile(\'image\')"');
});

it('configures uploads to a url with the default image types', function () {
    $html = Blade::render('<x-avian::editor name="body" toolbar="image" upload-url="/editor/uploads" />');

    expect(editorConfig($html)['uploads'])->toBe([
        'method' => null,
        'model' => 'editorUpload',
        'url' => '/editor/uploads',
        'accept' => ['image/png', 'image/jpeg', 'image/gif', 'image/webp'],
    ]);

    expect(Blade::render('<x-avian::editor name="body" toolbar="image" />'))->not->toContain('type="file"')
        ->not->toContain('pickFile');
});

it('shows a rejected livewire upload as the editor error', function () {
    view()->share('errors', (new ViewErrorBag)->put('default', new MessageBag(['photo' => ['The photo must be an image.']])));

    expect(Blade::render('<x-avian::editor wire:model="body" upload="store" upload-model="photo" />'))->toContain('The photo must be an image.')
        ->and(Blade::render('<x-avian::editor wire:model="body" />'))->not->toContain('The photo must be an image.');
});

it('names the editor for the set and insert events by its name or wire:model property', function () {
    expect(editorConfig(Blade::render('<x-avian::editor name="letter" />'))['name'])->toBe('letter')
        ->and(editorConfig(Blade::render('<x-avian::editor wire:model.live="form.body" />'))['name'])->toBe('form.body')
        ->and(editorConfig(Blade::render('<x-avian::editor />'))['name'])->toBeNull();
});
