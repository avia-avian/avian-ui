{{--
    One page link inside `<x-avian::pagination>`. Renders a `gotoPage()`
    button for Livewire, or a plain link to the paginator's URL otherwise.
    A cursor paginator passes `cursor` instead of `page`.
--}}
@props([
    'paginator',
    'page' => null,
    'cursor' => null,
    'livewire' => false,
])

@php
    $tag = $livewire ? 'button' : 'a';

    if ($cursor instanceof \Illuminate\Pagination\Cursor) {
        $click = "setPage('{$cursor->encode()}', '{$paginator->getCursorName()}')";
        $url = $paginator->url($cursor);
    } else {
        $click = "gotoPage({$page}, '{$paginator->getPageName()}')";
        $url = $paginator->url($page);
    }
@endphp

<{{ $tag }}
    {{ $attributes->class(['aui-pagination-link'])->merge([
        'type' => $livewire ? 'button' : null,
        'wire:click' => $livewire ? $click : null,
        'href' => $livewire ? null : $url,
    ]) }}
>{{ $slot }}</{{ $tag }}>
