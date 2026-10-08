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

it('renders a filter chip with its defaults', function () {
    $html = Blade::render('<x-avian::filter-chip name="mine" label="Only mine" />');

    expect($html)->toContain('<label class="aui-filter-chip"')
        ->toContain('for="aui-mine-1"')
        ->toContain('type="checkbox"')
        ->toContain('id="aui-mine-1"')
        ->toContain('value="1"')
        ->toContain('class="aui-filter-chip-input"')
        ->toContain('<i class="fas fa-check aui-filter-chip-check" aria-hidden="true"></i>')
        ->not->toContain('checked')
        ->not->toContain('aui-filter-chip-icon')
        ->not->toContain('aui-filter-chip-count')
        ->not->toContain('aui-filter-chip-sm');
});

it('renders a small filter chip', function () {
    expect(Blade::render('<x-avian::filter-chip name="mine" label="Mine" size="sm" />'))
        ->toContain('<label class="aui-filter-chip aui-filter-chip-sm"')
        ->and(Blade::render('<x-avian::filter-chip href="/orders" label="All" size="sm" />'))
        ->toContain('class="aui-filter-chip aui-filter-chip-sm"');
});

it('falls back to a checkbox for an unknown filter chip type', function () {
    expect(Blade::render('<x-avian::filter-chip type="switch" name="mine" label="Mine" />'))
        ->toContain('type="checkbox"')
        ->not->toContain('type="switch"');
});

it('uses an explicit id for the filter chip input and its label', function () {
    $html = Blade::render('<x-avian::filter-chip name="status[]" value="open" id="chip-open" label="Open" />');

    expect($html)->toContain('for="chip-open"')
        ->toContain('id="chip-open"')
        ->not->toContain('aui-status-open');
});

it('renders a filter chip without an id when it has no name', function () {
    expect(Blade::render('<x-avian::filter-chip label="Loose" />'))
        ->not->toContain('id=')
        ->not->toContain('for=')
        ->not->toContain('name=');
});

it('derives a filter chip id from a dotted or underscored name', function () {
    expect(Blade::render('<x-avian::filter-chip name="filters[order_status][]" value="open" label="Open" />'))
        ->toContain('id="aui-filters-order-status-open"');
});

it('generates a valid id from a value with spaces or symbols', function (string $component) {
    expect(Blade::render("<x-avian::{$component} name=\"status[]\" value=\" in progress / later \" label=\"Later\" />"))
        ->toContain('id="aui-status-in-progress-later"')
        ->toContain('for="aui-status-in-progress-later"');
})->with(['filter-chip', 'checkbox', 'radio']);

it('shows a zero count on a filter chip', function () {
    expect(Blade::render('<x-avian::filter-chip name="mine" label="Mine" :count="0" />'))
        ->toContain('<span class="aui-filter-chip-count">0</span>');
});

it('rechecks a radio filter chip from old input', function () {
    session()->flashInput(['period' => '30d']);

    expect(Blade::render('<x-avian::filter-chip type="radio" name="period" value="30d" label="30 days" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::filter-chip type="radio" name="period" value="7d" label="7 days" checked />'))->not->toContain('checked');
});

it('unchecks a filter chip missing from old input', function () {
    session()->flashInput(['search' => 'abc']);

    expect(Blade::render('<x-avian::filter-chip name="mine" label="Mine" checked />'))
        ->not->toContain('checked');
});

it('keeps the checked state of a filter chip without old input', function () {
    expect(Blade::render('<x-avian::filter-chip name="mine" label="Mine" checked />'))
        ->toContain('checked="checked"');
});

it('ignores old input for a wired filter chip', function () {
    session()->flashInput(['statuses' => ['closed']]);

    expect(Blade::render('<x-avian::filter-chip wire:model="statuses" value="open" label="Open" checked />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::filter-chip wire:model="statuses" value="closed" label="Closed" />'))->not->toContain('checked');
});

it('derives a wired filter chip id from its model and leaves out the name', function () {
    $html = Blade::render('<x-avian::filter-chip wire:model.live="filters.statuses" value="open" label="Open" />');

    expect($html)->toContain('id="aui-filters-statuses-open"')
        ->toContain('for="aui-filters-statuses-open"')
        ->not->toContain('name=');
});

it('forwards classes and attributes to the filter chip input', function () {
    $html = Blade::render('<x-avian::filter-chip name="mine" label="Mine" class="js-chip" data-test="mine" disabled />');

    expect($html)->toContain('class="aui-filter-chip-input js-chip"')
        ->toMatch('/<input[^>]*data-test="mine"/')
        ->toMatch('/<input[^>]*disabled="disabled"/');
});

it('renders a link filter chip with its icon, count and extra attributes', function () {
    $html = Blade::render('<x-avian::filter-chip href="/orders?status=late" icon="fas fa-clock" :count="3" class="js-chip" data-test="late">Late</x-avian::filter-chip>');

    expect($html)->toContain('class="aui-filter-chip js-chip"')
        ->toContain('data-test="late"')
        ->toContain('<i class="fas fa-clock aui-filter-chip-icon" aria-hidden="true"></i>')
        ->toContain('<span class="aui-filter-chip-label">Late</span>')
        ->toContain('<span class="aui-filter-chip-count">3</span>')
        ->toContain('</a>')
        ->not->toContain('</label>');
});

it('escapes the filter chip label, count, value and href', function () {
    $html = Blade::render('<x-avian::filter-chip name="q" :value="$text" :label="$text" :count="$text" />', [
        'text' => '"><script>alert(1)</script>',
    ]);

    expect($html)->not->toContain('<script>')
        ->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->and(Blade::render('<x-avian::filter-chip :href="$href" label="All" />', ['href' => '/orders?a=1&b="x"']))
        ->toContain('href="/orders?a=1&amp;b=&quot;x&quot;"');
});

it('renders a filter chip group without a label', function () {
    $html = Blade::render('<x-avian::filter-chip.group><x-avian::filter-chip name="mine" label="Mine" /></x-avian::filter-chip.group>');

    expect($html)->toContain('<div class="aui-field">')
        ->toContain('role="group"')
        ->toContain('class="aui-filter-chips"')
        ->not->toContain('aria-label')
        ->not->toContain('class="aui-label"')
        ->not->toContain('aui-error');
});

it('renders a filter chip group label, hint and extra attributes', function () {
    $html = Blade::render('<x-avian::filter-chip.group label="Status" hint="Pick any." class="mb-4" id="status-filters"><x-avian::filter-chip name="mine" label="Mine" /></x-avian::filter-chip.group>');

    expect($html)->toContain('<label class="aui-label">Status</label>')
        ->toContain('<span class="aui-hint">Pick any.</span>')
        ->toContain('class="aui-filter-chips mb-4"')
        ->toContain('id="status-filters"');
});

it('renders just the row of chips when the group field wrapper is turned off', function () {
    $html = Blade::render('<x-avian::filter-chip.group label="Status" hint="Pick any." error="Required." :field="false"><x-avian::filter-chip name="mine" label="Mine" /></x-avian::filter-chip.group>');

    expect($html)->not->toContain('aui-field')
        ->not->toContain('aui-hint')
        ->not->toContain('aui-error')
        ->toContain('aria-label="Status"')
        ->toContain('class="aui-filter-chips"');
});

it('shows a forced filter chip group error', function () {
    expect(Blade::render('<x-avian::filter-chip.group error="Pick at least one."></x-avian::filter-chip.group>'))
        ->toContain('<span class="aui-error">Pick at least one.</span>');
});

it('reads a filter chip group error for the field itself', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['status' => 'Pick at least one status.'])));

    expect(Blade::render('<x-avian::filter-chip.group name="status[]"></x-avian::filter-chip.group>'))
        ->toContain('<span class="aui-error">Pick at least one status.</span>');
});

it('reads a filter chip group error from a named error bag', function () {
    View::share('errors', (new ViewErrorBag)->put('filters', new MessageBag(['status.1' => 'Pick a valid status.'])));

    expect(Blade::render('<x-avian::filter-chip.group name="status" error-bag="filters"></x-avian::filter-chip.group>'))->toContain('Pick a valid status.')
        ->and(Blade::render('<x-avian::filter-chip.group name="status"></x-avian::filter-chip.group>'))->not->toContain('aui-error');
});

it('escapes the filter chip group label', function () {
    expect(Blade::render('<x-avian::filter-chip.group :label="$label"></x-avian::filter-chip.group>', ['label' => '<b>Status</b>']))
        ->toContain('&lt;b&gt;Status&lt;/b&gt;')
        ->not->toContain('<b>Status</b>');
});
