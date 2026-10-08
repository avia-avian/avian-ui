@php
    $props = [
        ['items', 'iterable', '[]', 'Nodes: arrays or objects with an id, a label and optionally children, href, icon and badge.'],
        ['value-key', 'string', "'id'", 'Attribute holding the node id (falls back to value).'],
        ['label-key', 'string', "'label'", 'Attribute holding the label (falls back to name, then title).'],
        ['children-key', 'string', "'children'", 'Attribute holding the child nodes.'],
        ['expanded', 'bool|array', '[]', 'true opens every branch; a list of ids opens those.'],
        ['active', 'mixed', 'null', 'Id of the current node: highlighted, aria-current, its branch opened.'],
        ['selectable', 'bool', 'false', 'Tri-state checkboxes with cascading.'],
        ['name', 'string|null', 'null', 'With selectable: submits name[] with every checked id.'],
        ['value', 'array|null', 'null', 'Checked ids. A checked branch checks all of it. Falls back to old input.'],
        ['label', 'string|null', 'null', 'Field label, also the tree\'s accessible name.'],
        ['hint', 'string|null', 'null', 'Helper text under the tree.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from name and name.*.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the tree.'],
    ];

    $examples = [
        [
            'title' => 'Category navigation',
            'code' => <<<'BLADE'
                <x-avian::tree
                    label="Categories"
                    label-key="name"
                    :items="$categories->map(fn ($category) => [
                        'id' => $category->id,
                        'name' => $category->name,
                        'href' => route('categories.show', $category),
                        'badge' => $category->products_count,
                        'children' => $category->children->map(...),
                    ])"
                    :active="$current->id"
                />
                BLADE,
        ],
        [
            'title' => 'Permissions as a form field',
            'text' => 'Checking a branch checks everything under it. The request receives every checked id, branches included.',
            'code' => <<<'BLADE'
                <x-avian::tree
                    name="permissions"
                    label="Permissions"
                    :items="$permissionTree"
                    :value="$role->permissions->pluck('id')"
                    selectable
                />

                $request->validate(['permissions' => 'array', 'permissions.*' => 'exists:permissions,id']);
                BLADE,
        ],
        [
            'title' => 'Reacting to a node',
            'text' => 'A node that is neither a link nor a checkbox dispatches aui-tree-select when it is clicked or activated with Enter.',
            'code' => <<<'BLADE'
                <x-avian::tree :items="$folders" x-on:aui-tree-select="$wire.openFolder($event.detail.id)" />
                BLADE,
        ],
    ];

    $folders = [
        ['id' => 'src', 'label' => 'src', 'icon' => 'fas fa-folder', 'children' => [
            ['id' => 'components', 'label' => 'components', 'icon' => 'fas fa-folder', 'badge' => 3, 'children' => [
                ['id' => 'button', 'label' => 'button.blade.php', 'icon' => 'far fa-file-code'],
                ['id' => 'card', 'label' => 'card.blade.php', 'icon' => 'far fa-file-code'],
                ['id' => 'tree', 'label' => 'tree.blade.php', 'icon' => 'far fa-file-code'],
            ]],
            ['id' => 'provider', 'label' => 'AvianUiServiceProvider.php', 'icon' => 'far fa-file-code'],
        ]],
        ['id' => 'tests', 'label' => 'tests', 'icon' => 'fas fa-folder', 'children' => [
            ['id' => 'pest', 'label' => 'Pest.php', 'icon' => 'far fa-file-code'],
        ]],
        ['id' => 'readme', 'label' => 'README.md', 'icon' => 'far fa-file-lines'],
    ];

    $permissions = [
        ['id' => 'orders', 'label' => 'Orders', 'children' => [
            ['id' => 'orders.view', 'label' => 'View orders'],
            ['id' => 'orders.edit', 'label' => 'Edit orders'],
            ['id' => 'orders.refund', 'label' => 'Refund orders'],
        ]],
        ['id' => 'customers', 'label' => 'Customers', 'children' => [
            ['id' => 'customers.view', 'label' => 'View customers'],
            ['id' => 'customers.export', 'label' => 'Export customers'],
        ]],
        ['id' => 'settings', 'label' => 'Settings'],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Tree', 'subtitle' => 'Nested data you can expand, navigate and select'])
<p class="aui-showcase-lead">
    Shows nested data such as categories, folders or permission sets. As a form field it adds tri-state
    checkboxes: checking a branch checks all of it.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::tree label="Files" :items="$folders" :expanded="['src']" active="card" />
        <x-avian::tree name="docs_permissions" label="Permissions" :items="$permissions" :value="['orders.view', 'customers']" selectable hint="Checking a group checks everything in it." />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Follows the WAI-ARIA tree pattern: <kbd class="aui-kbd">↑</kbd> <kbd class="aui-kbd">↓</kbd> move, <kbd class="aui-kbd">→</kbd> opens a branch or steps into it, <kbd class="aui-kbd">←</kbd> closes it or steps out, <kbd class="aui-kbd">Home</kbd> / <kbd class="aui-kbd">End</kbd> jump, <kbd class="aui-kbd">*</kbd> opens every branch at that level, and typing a letter jumps to the next match.</li>
        <li><kbd class="aui-kbd">Enter</kbd> follows a link (or toggles); <kbd class="aui-kbd">Space</kbd> ticks a checkbox. Only one node is in the tab order at a time.</li>
        <li>Open branches and checks are kept by id, so a Livewire re-render does not collapse the tree. <code>wire:model</code> binds the checked ids.</li>
        <li>Everything is rendered up front: load the nested data with eager loading (<code>with('children.children')</code>).</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
