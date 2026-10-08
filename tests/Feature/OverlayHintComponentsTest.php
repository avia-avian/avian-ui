<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('wraps the slot in a tooltip trigger with a hidden tooltip', function () {
    $html = Blade::render('<x-avian::tooltip text="Edit order"><button type="button">Edit</button></x-avian::tooltip>');

    expect($html)->toContain('class="aui-tooltip-trigger"')
        ->toContain('x-data="auiTooltip({ placement: \'top\', delay: 150 })"')
        ->toContain('x-on:mouseenter="show()"')
        ->toContain('x-on:focusin="show()"')
        ->toContain('x-on:keydown.escape="hide()"')
        ->toContain('<button type="button">Edit</button>')
        ->toContain('role="tooltip"')
        ->toContain('class="aui-tooltip"')
        ->toContain('x-show="open"')
        ->toContain('>Edit order</span>');
});

it('passes the tooltip placement and delay to alpine', function () {
    expect(Blade::render('<x-avian::tooltip text="Hi" placement="left" :delay="0">x</x-avian::tooltip>'))
        ->toContain("auiTooltip({ placement: 'left', delay: 0 })");
});

it('falls back to the top for an unknown tooltip placement', function () {
    expect(Blade::render('<x-avian::tooltip text="Hi" placement="diagonal">x</x-avian::tooltip>'))
        ->toContain("placement: 'top'");
});

it('gives the tooltip an id that is stable across renders', function () {
    $render = fn (): string => Blade::render('<x-avian::tooltip text="Edit order"><button>Edit</button></x-avian::tooltip>');

    preg_match('/id="(aui-tooltip-[a-f0-9]+)"/', $render(), $first);
    preg_match('/id="(aui-tooltip-[a-f0-9]+)"/', $render(), $second);

    expect($first[1])->toBe($second[1]);
});

it('uses an explicit tooltip id', function () {
    expect(Blade::render('<x-avian::tooltip text="Hi" id="save-hint">x</x-avian::tooltip>'))
        ->toContain('id="save-hint"');
});

it('renders tooltip content from a slot and escapes the text prop', function () {
    expect(Blade::render('<x-avian::tooltip>x
<x-slot:content>Press <strong>K</strong></x-slot:content></x-avian::tooltip>'))
        ->toContain('Press <strong>K</strong>');

    expect(Blade::render('<x-avian::tooltip text="<b>bold</b>">x</x-avian::tooltip>'))
        ->toContain('&lt;b&gt;bold&lt;/b&gt;')
        ->not->toContain('<b>bold</b>');
});

it('forwards extra attributes to the tooltip wrapper', function () {
    expect(Blade::render('<x-avian::tooltip text="Hi" class="ms-2" data-test="tip">x</x-avian::tooltip>'))
        ->toContain('class="aui-tooltip-trigger ms-2"')
        ->toContain('data-test="tip"');
});

it('renders a popover with its trigger, a labelled dialog and a footer', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::popover title="Filters" width="280px" id="filters">
            <x-slot:trigger><button type="button">Open</button></x-slot:trigger>
            Body text
            <x-slot:footer><button type="button">Apply</button></x-slot:footer>
        </x-avian::popover>
        BLADE);

    expect($html)->toContain('class="aui-popover-wrap"')
        ->toContain("x-data=\"auiPopover({ placement: 'bottom', open: false })\"")
        ->toContain('x-on:click.outside="hide()"')
        ->toContain('x-on:click="toggle($event)"')
        ->toContain('data-aui-controls="filters"')
        ->toContain('<button type="button">Open</button>')
        ->toContain('id="filters"')
        ->toContain('role="dialog"')
        ->toContain('aria-labelledby="filters-title"')
        ->toContain('<strong id="filters-title">Filters</strong>')
        ->toContain('aria-label="Close"')
        ->toContain('style="width: 280px"')
        ->toContain('<div class="aui-popover-body">')
        ->toContain('Body text')
        ->toContain('<div class="aui-popover-footer"><button type="button">Apply</button></div>');
});

/*
 * A named slot written on the same line as its component's opening tag
 * leaves an output buffer open in Blade, so these keep the slot apart.
 */
function renderPopover(string $attributes = ''): string
{
    return Blade::render(<<<BLADE
        <x-avian::popover {$attributes}>
            <x-slot:trigger><button>Open</button></x-slot:trigger>
            Body
        </x-avian::popover>
        BLADE);
}

it('leaves out the popover header and footer when there are none', function () {
    expect(renderPopover())->not->toContain('aui-popover-header')
        ->not->toContain('aui-popover-footer')
        ->not->toContain('aria-labelledby')
        ->not->toContain('style="width');
});

it('renders a popover open and on another side', function () {
    expect(renderPopover('placement="top" open'))->toContain("auiPopover({ placement: 'top', open: true })");
    expect(renderPopover('placement="nowhere"'))->toContain("placement: 'bottom'");
});

it('escapes the popover title', function () {
    expect(renderPopover('title="<i>x</i>"'))->toContain('&lt;i&gt;x&lt;/i&gt;');
});

it('renders a single key from the kbd slot', function () {
    expect(trim(Blade::render('<x-avian::kbd>Esc</x-avian::kbd>')))
        ->toBe('<kbd class="aui-kbd">Esc</kbd>');
});

it('splits a shortcut into keys joined by a hidden separator', function () {
    $html = Blade::render('<x-avian::kbd keys="Ctrl + K" />');

    expect($html)->toContain('<span class="aui-kbd-group">')
        ->toContain('<kbd class="aui-kbd">Ctrl</kbd>')
        ->toContain('<span class="aui-kbd-separator" aria-hidden="true">+</span>')
        ->toContain('<kbd class="aui-kbd">K</kbd>')
        ->and(substr_count($html, 'aui-kbd-separator'))->toBe(1);
});

it('takes the shortcut keys as a list, with a custom separator and size', function () {
    $html = Blade::render('<x-avian::kbd :keys="[\'⌘\', \'Shift\', \'\', \'P\']" separator="·" size="sm" class="ms-auto" />');

    expect($html)->toContain('<span class="aui-kbd-group aui-kbd-sm ms-auto">')
        ->toContain('<kbd class="aui-kbd">⌘</kbd>')
        ->toContain('<kbd class="aui-kbd">Shift</kbd>')
        ->toContain('<kbd class="aui-kbd">P</kbd>')
        ->toContain('aria-hidden="true">·</span>')
        ->and(substr_count($html, '<kbd'))->toBe(3);
});

it('escapes kbd keys', function () {
    expect(Blade::render('<x-avian::kbd keys="<b>+K" />'))
        ->toContain('&lt;b&gt;')
        ->not->toContain('<b>');
});
