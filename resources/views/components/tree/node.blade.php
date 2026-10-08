{{--
    One node of <x-avian::tree>, rendered recursively by the tree itself.
    Expects $node, $level, $first, plus the tree's $state, $selectable, $name
    and $open. Not meant to be used on its own.
--}}
@php
    $nodeId = $node['id'];
    $hasChildren = $node['children'] !== [];
    $isOpen = $hasChildren && ($open === true || in_array($nodeId, $open, true));
    $isChecked = in_array($nodeId, $state['checked'], true);
    $isMixed = in_array($nodeId, $state['mixed'], true);
    $isActive = $state['active'] !== null && $state['active'] === $nodeId;
    $key = \Illuminate\Support\Js::from($nodeId);
@endphp

<li
    role="treeitem"
    class="aui-tree-item"
    data-aui-node="{{ $nodeId }}"
    aria-level="{{ $level }}"
    @if ($hasChildren)
        aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
        x-bind:aria-expanded="isOpen({{ $key }}) ? 'true' : 'false'"
    @endif
    @if ($selectable)
        aria-checked="{{ $isMixed ? 'mixed' : ($isChecked ? 'true' : 'false') }}"
        x-bind:aria-checked="checkState({{ $key }})"
    @endif
    @if ($isActive) aria-current="page" @endif
    tabindex="{{ $level === 1 && $first ? '0' : '-1' }}"
    x-bind:tabindex="focused === {{ $key }} ? 0 : -1"
    x-on:focus.self="focused = {{ $key }}"
>
    <div @class(['aui-tree-row', 'is-active' => $isActive]) style="--aui-tree-level: {{ $level - 1 }}" x-on:click="rowClick({{ $key }}, $event)">
        @if ($hasChildren)
            <span class="aui-tree-toggle" aria-hidden="true" x-on:click.stop="toggle({{ $key }})">
                <i class="fas fa-chevron-right"></i>
            </span>
        @else
            <span class="aui-tree-toggle aui-tree-leaf" aria-hidden="true"></span>
        @endif

        @if ($selectable)
            <input
                type="checkbox"
                class="aui-tree-checkbox"
                tabindex="-1"
                aria-hidden="true"
                @if (filled($name)) name="{{ $name }}[]" @endif
                value="{{ $nodeId }}"
                @checked($isChecked)
                x-bind:checked="checkState({{ $key }}) === 'true'"
                x-effect="$el.indeterminate = checkState({{ $key }}) === 'mixed'"
                x-on:click.stop="check({{ $key }})"
            >
        @endif

        @if (filled($node['icon']))
            <i class="aui-tree-icon {{ $node['icon'] }}" aria-hidden="true"></i>
        @endif

        @if (filled($node['href']))
            <a class="aui-tree-label" href="{{ $node['href'] }}" tabindex="-1" @if ($isActive) aria-current="page" @endif>{{ $node['label'] }}</a>
        @else
            <span class="aui-tree-label">{{ $node['label'] }}</span>
        @endif

        @if (filled($node['badge']))
            <span class="aui-tree-badge">{{ $node['badge'] }}</span>
        @endif
    </div>

    @if ($hasChildren)
        <ul role="group" @unless ($isOpen) style="display: none" @endunless x-show="isOpen({{ $key }})">
            @foreach ($node['children'] as $child)
                @include('avian-ui::components.tree.node', [
                    'node' => $child,
                    'level' => $level + 1,
                    'first' => false,
                ])
            @endforeach
        </ul>
    @endif
</li>
