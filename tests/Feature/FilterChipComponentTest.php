<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

it('renders a filter chip as a checkbox pill', function () {
    $html = Blade::render('<x-avian::filter-chip name="status[]" value="open" label="Open" :count="12" icon="fas fa-inbox" checked />');

    expect($html)->toContain('class="aui-filter-chip"')
        ->toContain('type="checkbox"')
        ->toContain('name="status[]"')
        ->toContain('value="open"')
        ->toContain('id="aui-status-open"')
        ->toContain('checked="checked"')
        ->toContain('<i class="fas fa-inbox aui-filter-chip-icon"')
        ->toContain('<span class="aui-filter-chip-label">Open</span>')
        ->toContain('<span class="aui-filter-chip-count">12</span>');
});

it('renders exclusive filter chips as radios', function () {
    expect(Blade::render('<x-avian::filter-chip type="radio" name="period" value="7d" label="7 days" />'))
        ->toContain('type="radio"')
        ->not->toContain('checked');
});

it('uses the slot as the label when none is given', function () {
    expect(Blade::render('<x-avian::filter-chip name="mine">Only mine</x-avian::filter-chip>'))
        ->toContain('<span class="aui-filter-chip-label">Only mine</span>');
});

it('rechecks filter chips from old input', function () {
    session()->flashInput(['status' => ['open', 'closed']]);

    expect(Blade::render('<x-avian::filter-chip name="status[]" value="closed" label="Closed" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::filter-chip name="status[]" value="pending" label="Pending" checked />'))->not->toContain('checked');
});

it('forwards wire:model to the filter chip input', function () {
    expect(Blade::render('<x-avian::filter-chip wire:model.live="statuses" value="open" label="Open" />'))
        ->toMatch('/<input[^>]*wire:model\.live="statuses"/');
});

it('renders a filter chip as a link when given an href', function () {
    $html = Blade::render('<x-avian::filter-chip href="/orders?status=open" label="Open" active navigate />');

    expect($html)->toContain('<a')
        ->toContain('href="/orders?status=open"')
        ->toContain('is-active')
        ->toContain('aria-current="true"')
        ->toContain('wire:navigate')
        ->not->toContain('<input');

    expect(Blade::render('<x-avian::filter-chip href="/orders" label="All" />'))
        ->not->toContain('is-active')
        ->not->toContain('aria-current')
        ->not->toContain('wire:navigate');
});

it('groups filter chips with a label and the error for the array field', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['status.0' => 'Pick a valid status.'])));

    $html = Blade::render('<x-avian::filter-chip.group name="status" label="Status"><x-avian::filter-chip name="status[]" value="open" label="Open" /></x-avian::filter-chip.group>');

    expect($html)->toContain('class="aui-filter-chips"')
        ->toContain('role="group"')
        ->toContain('aria-label="Status"')
        ->toContain('<span class="aui-error">Pick a valid status.</span>');
});
