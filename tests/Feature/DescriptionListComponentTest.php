<?php

declare(strict_types=1);

use AvianUi\AvianUi\Tests\Fixtures\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

it('renders a two-column description list of terms and values', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::description-list>
            <x-avian::description-list.item label="Customer" value="Rina" />
        </x-avian::description-list>
        BLADE);

    expect($html)->toContain('<dl class="aui-dl aui-dl-cols-2">')
        ->toContain('<div class="aui-dl-item">')
        ->toContain('<dt class="aui-dl-label">Customer</dt>')
        ->toContain('<dd class="aui-dl-value">')
        ->toContain('<span class="aui-dl-content">Rina</span>');
});

it('takes columns, inline and divided, clamping the columns', function () {
    expect(Blade::render('<x-avian::description-list :columns="3" inline divided class="mt-2" />'))
        ->toContain('<dl class="aui-dl aui-dl-cols-3 aui-dl-inline aui-dl-divided mt-2">');

    expect(Blade::render('<x-avian::description-list :columns="9" />'))->toContain('aui-dl-cols-3');
    expect(Blade::render('<x-avian::description-list :columns="0" />'))->toContain('aui-dl-cols-1');
});

it('renders items from an array before the slot', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::description-list :items="['SKU' => 'AV-1', 'Stock' => 18]">
            <x-avian::description-list.item label="Extra" value="Last" />
        </x-avian::description-list>
        BLADE);

    expect($html)->toContain('<dt class="aui-dl-label">SKU</dt>')
        ->toContain('>AV-1</span>')
        ->toContain('>18</span>')
        ->and(strpos($html, 'SKU'))->toBeLessThan(strpos($html, 'Extra'));
});

it('formats dates, enums and booleans', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::description-list.item label="Created" :value="$date" />
        <x-avian::description-list.item label="Day" :value="$date" date-format="Y-m-d" />
        <x-avian::description-list.item label="Status" :value="$status" />
        <x-avian::description-list.item label="Published" :value="true" />
        <x-avian::description-list.item label="Archived" :value="false" />
        BLADE, ['date' => Carbon::parse('2026-03-04 14:30:00'), 'status' => OrderStatus::Shipped]);

    expect($html)->toContain('>Mar 4, 2026 2:30 PM</span>')
        ->toContain('>2026-03-04</span>')
        ->toContain('>'.OrderStatus::Shipped->value.'</span>')
        ->toContain('>Yes</span>')
        ->toContain('>No</span>');
});

it('shows a muted placeholder for a blank value', function () {
    expect(Blade::render('<x-avian::description-list.item label="Coupon" />'))
        ->toContain('<dd class="aui-dl-value is-empty">')
        ->toContain('—')
        ->not->toContain('aui-dl-content');

    expect(Blade::render('<x-avian::description-list.item label="Coupon" value="" empty="None" />'))
        ->toContain('None');
});

it('prefers the slot over the value and spans the row when full', function () {
    $html = Blade::render('<x-avian::description-list.item label="Status" value="ignored" full><b>Paid</b></x-avian::description-list.item>');

    expect($html)->toContain('<div class="aui-dl-item aui-dl-full">')
        ->toContain('<span class="aui-dl-content"><b>Paid</b></span>')
        ->not->toContain('ignored');
});

it('adds a copy button that copies the value, the slot text or an override', function () {
    expect(Blade::render('<x-avian::description-list.item label="Invoice" value="INV-1" copyable />'))
        ->toContain('class="aui-copy-button aui-copy-button-sm"')
        ->toContain('data-copy="INV-1"')
        ->toContain('aria-label="Copy Invoice"');

    expect(Blade::render('<x-avian::description-list.item label="Status" copyable><b>Paid</b>  now</x-avian::description-list.item>'))
        ->toContain('data-copy="Paid now"');

    expect(Blade::render('<x-avian::description-list.item label="Link" value="Open" copy="https://example.com/x" copyable />'))
        ->toContain('data-copy="https://example.com/x"');
});

it('leaves the copy button out without copyable or for a blank value', function () {
    expect(Blade::render('<x-avian::description-list.item label="Invoice" value="INV-1" />'))->not->toContain('aui-copy-button');
    expect(Blade::render('<x-avian::description-list.item label="Invoice" copyable />'))->not->toContain('aui-copy-button');
});

it('escapes labels and values', function () {
    $html = Blade::render('<x-avian::description-list.item label="<i>L</i>" value="<b>V</b>" copyable />');

    expect($html)->toContain('&lt;i&gt;L&lt;/i&gt;')
        ->toContain('&lt;b&gt;V&lt;/b&gt;')
        ->not->toContain('<b>V</b>');
});

it('renders a standalone copy button', function () {
    $html = Blade::render('<x-avian::copy-button text="secret-token" label="Copy token" class="ms-1" />');

    expect($html)->toContain('type="button"')
        ->toContain('class="aui-copy-button ms-1"')
        ->toContain('x-data="auiCopy"')
        ->toContain('x-on:click="copy($el.dataset.copy)"')
        ->toContain('data-copy="secret-token"')
        ->toContain('aria-label="Copy token"')
        ->toContain('aria-live="polite"');

    expect(Blade::render('<x-avian::copy-button text="x" />'))->toContain('aria-label="Copy"');
});
