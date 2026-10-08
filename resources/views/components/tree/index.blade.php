{{--
    Nested data — categories, folders, permissions — as an expandable tree:

        <x-avian::tree :items="$categories" label-key="name" :active="$category->id" />

        <x-avian::tree name="permissions" :items="$permissionTree" selectable :value="$role->permission_ids" />

    A node is an array or object with an id (`value-key`, default id), a label
    (`label-key`, default label, then name or title), and optionally children
    (`children-key`), href, icon and badge. `expanded` is true for every
    branch or a list of ids; the `active` node and the branches holding a
    checked node open on their own.

    `selectable` adds tri-state checkboxes: checking a branch checks all of
    it, and a branch shows a dash while only part of it is checked. With a
    `name` it submits `name[]` (every checked id, branches included), reads
    old input and validation errors like the other controls, and `wire:model`
    binds the array of ids.

    The keyboard follows the WAI-ARIA tree pattern: arrows move and open or
    close, Home / End jump, typing a letter jumps to the next match, Enter
    opens a link (or toggles), Space toggles a checkbox. Activating a node
    that is neither a link nor selectable dispatches `aui-tree-select`.
--}}
@props([
    'items' => [],
    'name' => null,
    'value' => null,
    'selectable' => false,
    'expanded' => [],
    'active' => null,
    'valueKey' => 'id',
    'labelKey' => 'label',
    'childrenKey' => 'children',
    'label' => null,
    'hint' => null,
    'error' => null,
    'errorBag' => null,
    'field' => true,
])

@php
    $avianUi = app(\AvianUi\AvianUi\AvianUi::class);

    $fieldName = $avianUi->fieldName($name, $attributes);
    $inputError = $selectable
        ? ($error ?? $avianUi->errorFor($fieldName, $errorBag) ?? ($fieldName !== null ? $avianUi->errorFor($fieldName.'.*', $errorBag) : null))
        : null;

    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $rootAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));
    $wired = $modelAttributes->isNotEmpty();

    $selected = $wired ? $value : $avianUi->old($name, $value);
    $selected = collect(is_iterable($selected) ? $selected : (filled($selected) ? [$selected] : []))
        ->map(fn (mixed $id): string => (string) ($id instanceof \BackedEnum ? $id->value : $id))
        ->all();

    $read = fn (mixed $node, string $key): mixed => data_get($node, $key);

    // Normalises every node once: string ids, a label, children.
    $normalize = function (iterable $nodes) use (&$normalize, $read, $valueKey, $labelKey, $childrenKey): array {
        $list = [];

        foreach ($nodes as $node) {
            $children = $read($node, $childrenKey);

            $list[] = [
                'id' => (string) ($read($node, $valueKey) ?? $read($node, 'value') ?? ''),
                'label' => (string) ($read($node, $labelKey) ?? $read($node, 'name') ?? $read($node, 'title') ?? ''),
                'href' => $read($node, 'href'),
                'icon' => $read($node, 'icon'),
                'badge' => $read($node, 'badge'),
                'children' => is_iterable($children) ? $normalize($children) : [],
            ];
        }

        return $list;
    };

    $nodes = $normalize(is_iterable($items) ? $items : []);

    // Checked state: a checked branch checks everything under it, and a
    // branch is checked when all of it is, mixed when only part of it is.
    $checked = [];
    $mixed = [];
    $open = $expanded === true ? true : collect(is_iterable($expanded) ? $expanded : [])->map(fn (mixed $id): string => (string) $id)->all();
    $activeId = filled($active) ? (string) $active : null;

    $walk = function (array $nodes, bool $inherited) use (&$walk, &$checked, &$mixed, &$open, $selected, $activeId): array {
        $all = true;
        $any = false;
        $holdsActive = false;

        foreach ($nodes as $node) {
            $self = $inherited || in_array($node['id'], $selected, true);

            if ($node['children'] === []) {
                $isChecked = $self;
                $isPartial = false;
                $hasActive = $node['id'] === $activeId;
            } else {
                [$isChecked, $someChecked, $hasActive] = $walk($node['children'], $self);
                $isPartial = ! $isChecked && $someChecked;

                // Open a branch holding a checked or the active node.
                if (($someChecked || $hasActive) && is_array($open) && ! in_array($node['id'], $open, true)) {
                    $open[] = $node['id'];
                }

                $hasActive = $hasActive || $node['id'] === $activeId;
            }

            if ($isChecked) {
                $checked[] = $node['id'];
            }

            if ($isPartial) {
                $mixed[] = $node['id'];
            }

            $all = $all && $isChecked;
            $any = $any || $isChecked || $isPartial;
            $holdsActive = $holdsActive || $hasActive;
        }

        return [$nodes !== [] && $all, $any, $holdsActive];
    };

    $walk($nodes, false);

    $state = [
        'checked' => $checked,
        'mixed' => $mixed,
        'open' => $open,
        'active' => $activeId,
    ];
@endphp

<x-avian-ui::field :bare="! $field" :label="$label" :hint="$hint" :error="$inputError">
    {{-- Per-render state goes through `data-*` attributes so the `x-data`
         expression stays constant across Livewire morphs. --}}
    <div
        x-data="auiTree({ selectable: @js((bool) $selectable) })"
        x-modelable="value"
        {{ $modelAttributes }}
        data-aui-tree="{{ json_encode($state) }}"
        {{ $rootAttributes->class(['aui-tree', 'aui-tree-selectable' => $selectable, 'aui-tree-invalid' => filled($inputError)]) }}
    >
        <ul
            role="tree"
            @if (filled($label)) aria-label="{{ $label }}" @endif
            @if ($selectable) aria-multiselectable="true" @endif
            x-on:keydown="keydown($event)"
        >
            @foreach ($nodes as $node)
                @include('avian-ui::components.tree.node', [
                    'node' => $node,
                    'level' => 1,
                    'first' => $loop->first,
                ])
            @endforeach
        </ul>
    </div>
</x-avian-ui::field>
