{{--
    A rich text editor on Tiptap. It submits HTML through a hidden input.

        <x-avian::editor name="body" label="Body" placeholder="Write something…" />

    `toolbar` picks the buttons and their order, `|` starting a new group. It
    takes a preset (`default`, `minimal`, `full`), a string or an array, or
    `false` for no toolbar at all:

        <x-avian::editor name="note" toolbar="bold italic | bullet-list ordered-list | link" />

    The editor only accepts the nodes and marks its toolbar can make, so a
    pasted <h4> or table is dropped when there is no button for it. `nodes`
    allows more on top, for content that arrives by paste or keyboard shortcut:

        <x-avian::editor name="note" toolbar="bold italic" nodes="h2 h3 bullet-list" />

    Files picked with the image or file button, dropped or pasted are uploaded
    straight away and inserted once the server answers with their URL: to a
    Livewire method through a WithFileUploads property, or POSTed to a URL.

        <x-avian::editor wire:model="body" toolbar="bold italic | image file" upload="storeEditorFile" upload-model="editorFile" />
        <x-avian::editor name="body" toolbar="bold italic | image" upload-url="/editor/uploads" />

    Tiptap comes from <x-avian::scripts editor />, or from the host app's own
    `window.AvianUITiptap = { Editor, StarterKit, ... }`.

    `aui-editor-set` (replace everything) and `aui-editor-insert` (add at the
    cursor) browser events change the content from outside, aimed at the editor
    by its `name` or its wire:model property:

        $this->dispatch('aui-editor-insert', name: 'body', html: $snippet->body);

    With Livewire, `wire:model` binds the HTML through Alpine's `x-modelable`;
    the editable area is `wire:ignore`d so a morph never rebuilds it.
--}}
@props([
    'name' => null,
    'id' => null,
    'value' => null,
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'required' => false,
    'placeholder' => null,
    'toolbar' => 'default',
    'nodes' => null,
    'minHeight' => null,
    'colors' => ['#0f172a', '#64748b', '#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#0891b2', '#2563eb', '#7c3aed', '#db2777'],
    'fontSizes' => ['12px', '14px', '16px', '18px', '20px', '24px', '30px', '36px'],
    'upload' => null,
    'uploadModel' => 'editorUpload',
    'uploadUrl' => null,
    'accept' => 'image/png, image/jpeg, image/gif, image/webp',
    'disabled' => false,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $uploading = filled($upload) || filled($uploadUrl);

    // A rejected Livewire upload reports its error on the upload property.
    $inputError = $error
        ?? $avianUi->errorFor($fieldName, $errorBag)
        ?? (filled($upload) ? $avianUi->errorFor((string) $uploadModel, $errorBag) : null);
    $inputId = $id ?? (filled($fieldName) ? 'aui-'.str_replace(['[', ']', '.', '_'], '-', trim((string) $fieldName, '[]')) : null);

    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $rootAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));
    $wired = $modelAttributes->isNotEmpty();

    $current = $wired ? $value : $avianUi->old($name, $value);
    $current = is_scalar($current) ? (string) $current : '';

    if ($current === '' && ! $wired && $slot->isNotEmpty()) {
        $current = trim((string) $slot);
    }

    // Every toolbar item: its icon (or text), and the node or mark it needs.
    $catalog = [
        'paragraph' => ['text' => 'P', 'node' => null],
        'h1' => ['text' => 'H1', 'node' => 'heading'],
        'h2' => ['text' => 'H2', 'node' => 'heading'],
        'h3' => ['text' => 'H3', 'node' => 'heading'],
        'h4' => ['text' => 'H4', 'node' => 'heading'],
        'h5' => ['text' => 'H5', 'node' => 'heading'],
        'h6' => ['text' => 'H6', 'node' => 'heading'],
        'bold' => ['icon' => 'fas fa-bold', 'node' => 'bold'],
        'italic' => ['icon' => 'fas fa-italic', 'node' => 'italic'],
        'underline' => ['icon' => 'fas fa-underline', 'node' => 'underline'],
        'strike' => ['icon' => 'fas fa-strikethrough', 'node' => 'strike'],
        'code' => ['icon' => 'fas fa-code', 'node' => 'code'],
        'highlight' => ['icon' => 'fas fa-highlighter', 'node' => 'highlight'],
        'color' => ['icon' => 'fas fa-font', 'node' => 'color'],
        'font-size' => ['node' => 'font-size'],
        'link' => ['icon' => 'fas fa-link', 'node' => 'link'],
        'bullet-list' => ['icon' => 'fas fa-list-ul', 'node' => 'bullet-list'],
        'ordered-list' => ['icon' => 'fas fa-list-ol', 'node' => 'ordered-list'],
        'task-list' => ['icon' => 'fas fa-list-check', 'node' => 'task-list'],
        'blockquote' => ['icon' => 'fas fa-quote-right', 'node' => 'blockquote'],
        'code-block' => ['icon' => 'fas fa-file-code', 'node' => 'code-block'],
        'horizontal-rule' => ['icon' => 'fas fa-minus', 'node' => 'horizontal-rule'],
        'table' => ['icon' => 'fas fa-table', 'node' => 'table'],
        'image' => ['icon' => 'fas fa-image', 'node' => 'image'],
        'file' => ['icon' => 'fas fa-paperclip', 'node' => null],
        'align-left' => ['icon' => 'fas fa-align-left', 'node' => 'text-align'],
        'align-center' => ['icon' => 'fas fa-align-center', 'node' => 'text-align'],
        'align-right' => ['icon' => 'fas fa-align-right', 'node' => 'text-align'],
        'align-justify' => ['icon' => 'fas fa-align-justify', 'node' => 'text-align'],
        'clear' => ['icon' => 'fas fa-text-slash', 'node' => null],
        'undo' => ['icon' => 'fas fa-rotate-left', 'node' => null],
        'redo' => ['icon' => 'fas fa-rotate-right', 'node' => null],
    ];

    // Toggles report their state with aria-pressed; actions do not.
    $actions = ['horizontal-rule', 'table', 'image', 'file', 'clear', 'undo', 'redo'];

    // The bar that edits the table the cursor is in.
    $tableActions = [
        'table-row-before' => 'fas fa-arrow-up',
        'table-row-after' => 'fas fa-arrow-down',
        'table-column-before' => 'fas fa-arrow-left',
        'table-column-after' => 'fas fa-arrow-right',
        'table-delete-row' => 'fas fa-minus',
        'table-delete-column' => 'fas fa-minus',
        'table-header-row' => 'fas fa-heading',
        'table-merge' => 'fas fa-object-group',
        'table-delete' => 'fas fa-trash-can',
    ];

    $presets = [
        'default' => 'h2 h3 | bold italic underline strike | bullet-list ordered-list blockquote | link | undo redo',
        'minimal' => 'bold italic underline | bullet-list ordered-list | link',
        'full' => 'paragraph h1 h2 h3 | font-size | bold italic underline strike code highlight color | align-left align-center align-right | bullet-list ordered-list task-list | blockquote code-block horizontal-rule table | link image file | clear | undo redo',
    ];

    $words = fn (mixed $list): array => match (true) {
        is_array($list) => array_values(array_map(fn (mixed $item): string => trim((string) $avianUi->scalar($item)), $list)),
        is_string($list) => preg_split('/[\s,]+/', trim($presets[$list] ?? $list), -1, PREG_SPLIT_NO_EMPTY) ?: [],
        default => [],
    };

    $items = $toolbar === false || $toolbar === null ? [] : $words($toolbar === true ? 'default' : $toolbar);

    // Unknown items are dropped, then empty groups: a "|" only ever sits between two buttons.
    // The file button only makes sense with somewhere to upload to.
    $groups = [[]];

    foreach ($items as $item) {
        if ($item === '|') {
            $groups[] = [];
        } elseif (isset($catalog[$item]) && ($item !== 'file' || $uploading) && ! in_array($item, array_merge(...$groups), true)) {
            $groups[count($groups) - 1][] = $item;
        }
    }

    $groups = array_values(array_filter($groups));
    $buttons = array_merge(...($groups ?: [[]]));

    // The schema: what the buttons make, plus `nodes`. No toolbar keeps the default preset's.
    $allowed = array_merge($groups === [] && $nodes === null ? $words('default') : $buttons, $words($nodes));
    $schema = [];
    $headings = [];

    foreach ($allowed as $item) {
        if (preg_match('/^h([1-6])$/', $item, $level)) {
            $headings[] = (int) $level[1];
        } elseif ($item === 'heading') {
            $headings = array_merge($headings, range(1, 6));
        } elseif (isset($catalog[$item]['node'])) {
            $schema[] = $catalog[$item]['node'];
        } elseif ($item === 'text-align') {
            $schema[] = $item;
        }
    }

    $headings = array_values(array_unique($headings));
    sort($headings);
    $schema = array_values(array_unique(array_diff($schema, ['heading'])));

    $itemLabel = fn (string $item): string => preg_match('/^h([1-6])$/', $item, $level)
        ? __('avian-ui::messages.editor.heading', ['level' => $level[1]])
        : __('avian-ui::messages.editor.'.str_replace('-', '_', $item));

    $config = [
        'name' => $fieldName,
        'nodes' => $schema,
        'headings' => $headings,
        'placeholder' => $placeholder,
        'disabled' => (bool) $disabled,
        'uploads' => $uploading ? [
            'method' => filled($upload) ? (string) $upload : null,
            'model' => (string) $uploadModel,
            'url' => filled($upload) ? null : (string) $uploadUrl,
            'accept' => $words($accept),
        ] : null,
        'messages' => $uploading ? [
            'failed' => __('avian-ui::messages.editor.upload_failed'),
            'rejected' => __('avian-ui::messages.editor.upload_rejected'),
        ] : null,
    ];
@endphp

<x-avian-ui::field :bare="! $field" :label="$label" :for="$inputId" :hint="$hint" :error="$inputError" :required="$required">
    {{-- Per-render state goes through `data-*` attributes so the `x-data`
         expression stays constant across Livewire morphs. --}}
    <div
        x-data="auiEditor({{ json_encode($config) }})"
        x-modelable="value"
        {{ $modelAttributes }}
        data-aui-value="{{ json_encode($current) }}"
        {{ $rootAttributes->class([
            'aui-editor',
            'aui-editor-invalid' => filled($inputError),
            'is-disabled' => $disabled,
        ]) }}
        @if (filled($minHeight)) style="--aui-editor-min-height: {{ is_numeric($minHeight) ? $minHeight.'px' : $minHeight }}" @endif
    >
        @if ($groups !== [])
            <div class="aui-editor-toolbar" role="toolbar" aria-label="{{ __('avian-ui::messages.editor.toolbar') }}">
                @foreach ($groups as $group)
                    <div class="aui-editor-group">
                        @foreach ($group as $item)
                            @if ($item === 'font-size')
                                <select
                                    class="aui-editor-select"
                                    data-aui-command="font-size"
                                    aria-label="{{ $itemLabel($item) }}"
                                    title="{{ $itemLabel($item) }}"
                                    x-bind:value="textStyle('fontSize')"
                                    x-on:change="setFontSize($event.target.value)"
                                    x-bind:disabled="! can('font-size')"
                                    disabled
                                >
                                    <option value="">{{ __('avian-ui::messages.editor.default_size') }}</option>
                                    @foreach ((array) $fontSizes as $size)
                                        <option value="{{ $size }}">{{ preg_replace('/px$/', '', (string) $size) }}</option>
                                    @endforeach
                                </select>
                                @continue
                            @endif

                            @if ($item === 'color')
                                <div class="aui-editor-color" x-on:click.outside="colorOpen = false" x-on:keydown.escape.stop="colorOpen = false">
                            @endif

                            <button
                                type="button"
                                class="aui-editor-btn"
                                data-aui-command="{{ $item }}"
                                title="{{ $itemLabel($item) }}"
                                aria-label="{{ $itemLabel($item) }}"
                                x-on:mousedown.prevent
                                x-on:click="run('{{ $item }}')"
                                @if ($item === 'color')
                                    aria-haspopup="true"
                                    x-bind:aria-expanded="colorOpen ? 'true' : 'false'"
                                @else
                                    x-bind:class="{ 'is-active': isActive('{{ $item }}') }"
                                    @unless (in_array($item, $actions, true)) x-bind:aria-pressed="isActive('{{ $item }}') ? 'true' : 'false'" @endunless
                                @endif
                                x-bind:disabled="! can('{{ $item }}')"
                                disabled
                            >
                                @if (isset($catalog[$item]['icon']))
                                    <i class="{{ $catalog[$item]['icon'] }}" aria-hidden="true"></i>
                                @else
                                    <span class="aui-editor-btn-text">{{ $catalog[$item]['text'] }}</span>
                                @endif

                                @if ($item === 'color')
                                    <span class="aui-editor-color-bar" x-bind:style="{ background: textStyle('color') || 'currentColor' }" aria-hidden="true"></span>
                                @endif
                            </button>

                            @if ($item === 'color')
                                    <div class="aui-editor-color-menu" x-show="colorOpen" x-cloak x-transition.opacity>
                                        @foreach ((array) $colors as $color)
                                            <button
                                                type="button"
                                                class="aui-editor-swatch"
                                                style="--aui-swatch: {{ $color }}"
                                                title="{{ $color }}"
                                                aria-label="{{ $color }}"
                                                x-on:mousedown.prevent
                                                x-on:click="setColor(@js((string) $color))"
                                                x-bind:class="{ 'is-active': textStyle('color') === @js((string) $color) }"
                                            ></button>
                                        @endforeach
                                        <button type="button" class="aui-editor-swatch-reset" x-on:mousedown.prevent x-on:click="setColor(null)">
                                            {{ __('avian-ui::messages.editor.default_color') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            @if (in_array('link', $buttons, true) || in_array('image', $buttons, true))
                <div class="aui-editor-panel" x-show="panel" x-cloak x-on:keydown.escape.stop.prevent="closePanel()">
                    <i class="fas" x-bind:class="panel === 'image' ? 'fa-image' : 'fa-link'" aria-hidden="true"></i>
                    <input
                        type="url"
                        class="aui-input aui-input-sm aui-editor-panel-input"
                        x-ref="panelInput"
                        x-model="panelUrl"
                        x-on:keydown.enter.prevent="applyPanel()"
                        x-bind:aria-label="panel === 'image' ? @js(__('avian-ui::messages.editor.image_url')) : @js(__('avian-ui::messages.editor.link_url'))"
                        placeholder="https://"
                    >
                    <button type="button" class="aui-btn aui-btn-sm aui-btn-solid aui-tone-primary" x-on:click="applyPanel()">{{ __('avian-ui::messages.editor.apply') }}</button>
                    @if ($uploading)
                        <button type="button" class="aui-btn aui-btn-sm aui-btn-outline aui-tone-secondary" x-show="panel === 'image'" x-on:click="pickFile('image')">
                            <i class="fas fa-upload" aria-hidden="true"></i> {{ __('avian-ui::messages.editor.upload') }}
                        </button>
                    @endif
                    <button type="button" class="aui-btn aui-btn-sm aui-btn-ghost aui-tone-danger" x-show="panel === 'link' && isActive('link')" x-on:click="removeLink()">{{ __('avian-ui::messages.editor.remove_link') }}</button>
                    <button type="button" class="aui-btn aui-btn-sm aui-btn-ghost aui-tone-secondary" x-on:click="closePanel()">{{ __('avian-ui::messages.cancel') }}</button>
                </div>
            @endif
        @endif

        @if (in_array('table', $schema, true))
            <div class="aui-editor-panel aui-editor-table-bar" x-show="isActive('table') && ! panel" x-cloak role="toolbar" aria-label="{{ __('avian-ui::messages.editor.table') }}">
                @foreach ($tableActions as $action => $icon)
                    <button
                        type="button"
                        class="aui-editor-table-btn"
                        data-aui-command="{{ $action }}"
                        x-on:mousedown.prevent
                        x-on:click="run('{{ $action }}')"
                        x-bind:disabled="! can('{{ $action }}')"
                    >
                        <i class="{{ $icon }}" aria-hidden="true"></i>
                        {{ $itemLabel($action) }}
                    </button>
                @endforeach
            </div>
        @endif

        <div
            class="aui-editor-content"
            x-ref="content"
            wire:ignore
            @if (filled($inputId)) data-aui-id="{{ $inputId }}" @endif
            @if (filled($label)) data-aui-label="{{ $label }}" @endif
            @if (filled($inputError)) data-aui-invalid="true" @endif
        ></div>

        @if ($uploading)
            <input type="file" class="aui-sr-only" tabindex="-1" aria-hidden="true" x-ref="fileInput" x-on:change="picked($event)" multiple>

            <div class="aui-editor-status" x-show="uploading || uploadError" x-cloak aria-live="polite">
                <span x-show="uploading" class="aui-editor-status-busy">
                    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
                    {{ __('avian-ui::messages.editor.uploading') }}
                </span>
                <span x-show="uploadError" class="aui-editor-status-error">
                    <i class="fas fa-circle-exclamation" aria-hidden="true"></i>
                    <span x-text="uploadError"></span>
                    <button type="button" class="aui-editor-status-dismiss" x-on:click="uploadError = ''" aria-label="{{ __('avian-ui::messages.dismiss') }}">
                        <i class="fas fa-xmark" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        @endif

        @if (filled($name))
            <input type="hidden" name="{{ $name }}" value="{{ $current }}" x-bind:value="value">
        @endif
    </div>
</x-avian-ui::field>
