<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

function treeItems(): array
{
    return [
        ['id' => 'orders', 'label' => 'Orders', 'icon' => 'fas fa-box', 'badge' => 3, 'children' => [
            ['id' => 'orders.view', 'label' => 'View orders'],
            ['id' => 'orders.edit', 'label' => 'Edit orders', 'href' => '/orders/edit'],
        ]],
        ['id' => 'settings', 'label' => 'Settings'],
    ];
}

/** The decoded data-aui-tree state of a rendered tree. */
function treeState(string $html): array
{
    preg_match('/data-aui-tree="([^"]*)"/', $html, $match);

    return json_decode(html_entity_decode($match[1]), true);
}

it('renders nested nodes as an aria tree', function () {
    $html = Blade::render('<x-avian::tree :items="$items" label="Permissions" />', ['items' => treeItems()]);

    expect($html)->toContain('x-data="auiTree({ selectable: false })"')
        ->toContain('x-modelable="value"')
        ->toContain('class="aui-tree"')
        ->toContain('role="tree"')
        ->toContain('aria-label="Permissions"')
        ->not->toContain('aria-multiselectable')
        ->toContain('data-aui-node="orders"')
        ->toContain('aria-level="1"')
        ->toContain('aria-level="2"')
        ->toContain('role="group"')
        ->toContain('<i class="aui-tree-icon fas fa-box" aria-hidden="true"></i>')
        ->toContain('<span class="aui-tree-badge">3</span>')
        ->toContain('<span class="aui-tree-label">View orders</span>')
        ->toContain('<a class="aui-tree-label" href="/orders/edit" tabindex="-1"')
        ->not->toContain('type="checkbox"');
});

it('puts only the first node in the tab order', function () {
    $html = Blade::render('<x-avian::tree :items="$items" />', ['items' => treeItems()]);

    expect(substr_count($html, 'tabindex="0"'))->toBe(1)
        ->and(strpos($html, 'tabindex="0"'))->toBeLessThan(strpos($html, 'data-aui-node="orders.view"'));
});

it('renders branches closed unless expanded', function () {
    $closed = Blade::render('<x-avian::tree :items="$items" />', ['items' => treeItems()]);

    expect($closed)->toContain('aria-expanded="false"')
        ->toContain('<ul role="group"  style="display: none"')
        ->and(treeState($closed)['open'])->toBe([]);

    $open = Blade::render('<x-avian::tree :items="$items" :expanded="[\'orders\']" />', ['items' => treeItems()]);

    expect($open)->toContain('aria-expanded="true"')
        ->not->toContain('style="display: none"')
        ->and(treeState($open)['open'])->toBe(['orders']);

    expect(treeState(Blade::render('<x-avian::tree :items="$items" expanded />', ['items' => treeItems()]))['open'])->toBeTrue();
});

it('marks the active node and opens its branch', function () {
    $html = Blade::render('<x-avian::tree :items="$items" active="orders.edit" />', ['items' => treeItems()]);

    expect($html)->toContain('aria-current="page"')
        ->toContain('aui-tree-row is-active')
        ->and(treeState($html))->toMatchArray(['active' => 'orders.edit', 'open' => ['orders']]);
});

it('reads custom keys and falls back to name and value', function () {
    $items = [['value' => 7, 'name' => 'Shoes', 'kids' => [['value' => 8, 'name' => 'Boots']]]];

    $html = Blade::render('<x-avian::tree :items="$items" children-key="kids" expanded />', ['items' => $items]);

    expect($html)->toContain('data-aui-node="7"')
        ->toContain('data-aui-node="8"')
        ->toContain('>Shoes</span>')
        ->toContain('>Boots</span>');
});

it('renders checkboxes that submit every checked id', function () {
    $html = Blade::render('<x-avian::tree name="permissions" :items="$items" selectable :value="[\'orders\']" />', ['items' => treeItems()]);

    expect($html)->toContain('auiTree({ selectable: true })')
        ->toContain('aria-multiselectable="true"')
        ->toContain('class="aui-tree aui-tree-selectable"')
        ->toContain('name="permissions[]"')
        ->toContain('value="orders.view"')
        ->toContain('aria-checked="true"')
        ->toContain('aria-checked="false"')
        ->and(treeState($html)['checked'])->toBe(['orders.view', 'orders.edit', 'orders'])
        ->and(preg_match_all('/\schecked\s/', $html))->toBe(3);
});

it('derives a partly checked branch and opens it', function () {
    $html = Blade::render('<x-avian::tree name="permissions" :items="$items" selectable :value="[\'orders.edit\']" />', ['items' => treeItems()]);

    expect($html)->toContain('aria-checked="mixed"')
        ->and(treeState($html))->toMatchArray(['checked' => ['orders.edit'], 'mixed' => ['orders'], 'open' => ['orders']]);
});

it('checks a branch whose leaves are all checked', function () {
    $html = Blade::render('<x-avian::tree name="p" :items="$items" selectable :value="[\'orders.view\', \'orders.edit\']" />', ['items' => treeItems()]);

    expect(treeState($html)['checked'])->toBe(['orders.view', 'orders.edit', 'orders']);
});

it('repopulates the checks from old input, but not when wired', function () {
    session()->flashInput(['permissions' => ['settings']]);

    expect(treeState(Blade::render('<x-avian::tree name="permissions" :items="$items" selectable />', ['items' => treeItems()]))['checked'])
        ->toBe(['settings']);

    $wired = Blade::render('<x-avian::tree wire:model="permissions" :items="$items" selectable :value="[\'orders.view\']" />', ['items' => treeItems()]);

    expect(treeState($wired)['checked'])->toBe(['orders.view'])
        ->and($wired)->toContain('wire:model="permissions"')
        ->not->toContain('name="permissions[]"');
});

it('reads a validation error from the field or its items', function (string $key) {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([$key => 'Pick a permission.'])));

    expect(Blade::render('<x-avian::tree name="permissions" :items="$items" selectable />', ['items' => treeItems()]))
        ->toContain('aui-tree-invalid')
        ->toContain('Pick a permission.');
})->with(['permissions', 'permissions.*']);

it('renders a field label and hint', function () {
    expect(Blade::render('<x-avian::tree :items="$items" label="Files" hint="Pick one." />', ['items' => treeItems()]))
        ->toContain('Files')
        ->toContain('Pick one.');
});

it('escapes labels and badges', function () {
    $html = Blade::render('<x-avian::tree :items="[[\'id\' => 1, \'label\' => \'<b>X</b>\', \'badge\' => \'<i>2</i>\']]" />');

    expect($html)->toContain('&lt;b&gt;X&lt;/b&gt;')
        ->toContain('&lt;i&gt;2&lt;/i&gt;')
        ->not->toContain('<b>X</b>');
});
