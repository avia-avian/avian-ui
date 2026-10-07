{{-- Page heading. Expects a `$title` and a `$subtitle`; `$group` comes from the docs index loop. --}}
<header class="aui-showcase-header">
    @isset($group)
        <p class="aui-showcase-eyebrow">{{ $group }}</p>
    @endisset

    <h1 class="aui-showcase-title">{{ $title }}</h1>
    <p class="aui-showcase-subtitle">{{ $subtitle }}</p>
</header>
