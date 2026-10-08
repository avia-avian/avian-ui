<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('renders a single text line hidden from screen readers', function () {
    expect(trim(Blade::render('<x-avian::skeleton />')))
        ->toBe('<span aria-hidden="true" class="aui-skeleton aui-skeleton-text" ></span>');
});

it('renders a paragraph of lines with a shorter last line', function () {
    $html = Blade::render('<x-avian::skeleton :lines="3" />');

    expect($html)->toContain('<div aria-hidden="true" class="aui-skeleton-lines" >')
        ->and(substr_count($html, 'class="aui-skeleton aui-skeleton-text"'))->toBe(3)
        ->and(substr_count($html, 'style="width: 60%;"'))->toBe(1);
});

it('sizes a circle from its size, in pixels by default', function () {
    expect(Blade::render('<x-avian::skeleton variant="circle" size="44" />'))
        ->toContain('class="aui-skeleton aui-skeleton-circle"')
        ->toContain('style="width: 44px; height: 44px"');

    expect(Blade::render('<x-avian::skeleton variant="circle" />'))
        ->toContain('style="width: 40px; height: 40px"');
});

it('takes any css length for the width and height', function () {
    expect(Blade::render('<x-avian::skeleton variant="rect" width="60%" height="160" />'))
        ->toContain('class="aui-skeleton aui-skeleton-rect"')
        ->toContain('style="width: 60%; height: 160px"');
});

it('renders a button placeholder and falls back to text for an unknown variant', function () {
    expect(Blade::render('<x-avian::skeleton variant="button" />'))->toContain('aui-skeleton-button');
    expect(Blade::render('<x-avian::skeleton variant="blob" />'))->toContain('aui-skeleton-text');
});

it('turns the shimmer off', function () {
    expect(Blade::render('<x-avian::skeleton :animate="false" />'))
        ->toContain('class="aui-skeleton aui-skeleton-text aui-skeleton-static"');
});

it('forwards extra attributes to the skeleton', function () {
    expect(Blade::render('<x-avian::skeleton class="mb-2" data-test="line" />'))
        ->toContain('class="aui-skeleton aui-skeleton-text mb-2"')
        ->toContain('data-test="line"');
});

it('renders a table placeholder announced as a busy status', function () {
    $html = Blade::render('<x-avian::skeleton.table :rows="2" :columns="3" label="Loading orders" />');

    expect($html)->toContain('class="aui-table-wrap aui-skeleton-table"')
        ->toContain('role="status" aria-busy="true"')
        ->toContain('<span class="aui-sr-only">Loading orders</span>')
        ->toContain('<table class="aui-table" aria-hidden="true">')
        ->toContain('<thead>')
        ->and(substr_count($html, '<th>'))->toBe(3)
        ->and(substr_count($html, '<td>'))->toBe(6);
});

it('renders a table placeholder without a header and with the default label', function () {
    $html = Blade::render('<x-avian::skeleton.table :header="false" :animate="false" />');

    expect($html)->not->toContain('<thead>')
        ->toContain('<span class="aui-sr-only">Loading</span>')
        ->toContain('aui-skeleton-static')
        ->and(substr_count($html, '<tr>'))->toBe(5)
        ->and(substr_count($html, '<td>'))->toBe(20);
});
