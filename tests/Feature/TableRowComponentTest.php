<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Js;

it('renders an expandable row with a toggle and a hidden detail row', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::table :headers="['', 'Invoice', 'Total']">
            <x-avian::table.row class="is-late">
                <td>INV-1</td>
                <td>100</td>
                <x-slot:details>Two lines</x-slot:details>
            </x-avian::table.row>
        </x-avian::table>
        BLADE);

    expect($html)->toContain('x-data="auiTableRow({ expanded: false })"')
        ->toContain('class="aui-table-row-expandable is-late"')
        ->toContain('<td class="aui-table-toggle-cell">')
        ->toContain('class="aui-table-toggle"')
        ->toContain('aria-expanded="false"')
        ->toContain('aria-label="Show details"')
        ->toContain('class="aui-table-details aui-table-details-indented"')
        ->toContain('colspan="3"')
        ->toContain('Two lines')
        ->toContain('wire:ignore.self')
        ->toMatch('/class="aui-table-details[^"]*"[^>]*\shidden/');
});

it('puts the toggle cell before the row cells by default and after them on request', function () {
    $start = Blade::render('<x-avian::table.row><td>INV-1</td><x-slot:details>x</x-slot:details></x-avian::table.row>');
    $end = Blade::render('<x-avian::table.row toggle="end"><td>INV-1</td><x-slot:details>x</x-slot:details></x-avian::table.row>');

    expect(strpos($start, 'aui-table-toggle-cell'))->toBeLessThan(strpos($start, 'INV-1'))
        ->and(strpos($end, 'aui-table-toggle-cell'))->toBeGreaterThan(strpos($end, 'INV-1'))
        ->and($start)->toContain('aui-table-details aui-table-details-indented')
        ->and($end)->toContain('aui-table-toggle-cell aui-table-align-right')
        ->and($end)->toContain('class="aui-table-details"');
});

it('starts an expanded row open', function () {
    $html = Blade::render('<x-avian::table.row expanded><td>INV-1</td><x-slot:details>x</x-slot:details></x-avian::table.row>');

    expect($html)->toContain('auiTableRow({ expanded: true })')
        ->toContain('aui-table-row-expandable is-expanded')
        ->toContain('aria-expanded="true"')
        ->not->toMatch('/class="aui-table-details[^"]*"[^>]*\shidden/');
});

it('makes the whole row toggle when clickable', function () {
    expect(Blade::render('<x-avian::table.row clickable><td>INV-1</td><x-slot:details>x</x-slot:details></x-avian::table.row>'))
        ->toContain('x-on:click="clickRow($event)"')
        ->toContain('aui-table-row-clickable');

    expect(Blade::render('<x-avian::table.row><td>INV-1</td><x-slot:details>x</x-slot:details></x-avian::table.row>'))
        ->not->toContain('clickRow');
});

it('spans the detail cell across the table columns or the given colspan', function () {
    expect(Blade::render('<x-avian::table :columns="5"><x-avian::table.row><td>a</td><x-slot:details>x</x-slot:details></x-avian::table.row></x-avian::table>'))
        ->toContain('colspan="5"');

    expect(Blade::render('<x-avian::table :headers="[\'\', \'A\']"><x-avian::table.row :colspan="7"><td>a</td><x-slot:details>x</x-slot:details></x-avian::table.row></x-avian::table>'))
        ->toContain('colspan="7"');

    expect(Blade::render('<x-avian::table.row><td>a</td><x-slot:details>x</x-slot:details></x-avian::table.row>'))
        ->toContain('colspan="100"');
});

it('links the toggle to the detail row through a stable id from wire:key', function () {
    $html = Blade::render('<x-avian::table.row wire:key="invoice-7"><td>a</td><x-slot:details>x</x-slot:details></x-avian::table.row>');

    expect($html)->toContain('wire:key="invoice-7"')
        ->toContain('aria-controls="aui-row-invoice-7-details"')
        ->toContain('id="aui-row-invoice-7-details"');

    expect(Blade::render('<x-avian::table.row><td>a</td><x-slot:details>x</x-slot:details></x-avian::table.row>'))
        ->not->toContain('aria-controls');
});

it('renders a plain row with an empty toggle cell when there are no details', function () {
    $html = Blade::render('<x-avian::table.row class="total"><td>Total</td></x-avian::table.row>');

    expect($html)->toContain('<tr class="total">')
        ->toContain('<td class="aui-table-toggle-cell"></td>')
        ->not->toContain('auiTableRow')
        ->not->toContain('aui-table-details');
});

it('registers the row behaviour and ships its styles', function () {
    expect(file_get_contents(__DIR__.'/../../public/js/avian-ui.js'))->toContain('auiTableRow: function')
        ->and(file_get_contents(__DIR__.'/../../public/css/avian-ui.css'))->toContain('.aui-table-toggle {')
        ->toContain('nth-child(even of :not(.aui-table-details))');
});

it('freezes the header and footer inside a scroll box capped at 70% of the screen', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::table :headers="['Name', 'Total']" sticky-header sticky-footer>
            <tr><td>Ada</td><td>100</td></tr>
            <x-slot:foot><tr><th>Total</th><th>100</th></tr></x-slot:foot>
        </x-avian::table>
        BLADE);

    expect($html)->toContain('style="max-height: 70vh"')
        ->toContain('aui-table-sticky aui-table-sticky-header aui-table-sticky-footer')
        ->not->toContain('auiTable(');
});

it('takes a max height in pixels or any css length', function () {
    expect(Blade::render('<x-avian::table :headers="[\'Name\']" sticky-header :max-height="320" />'))
        ->toContain('style="max-height: 320px"')
        ->and(Blade::render('<x-avian::table :headers="[\'Name\']" max-height="24rem" />'))
        ->toContain('style="max-height: 24rem"')
        ->not->toContain('aui-table-sticky');
});

it('hands frozen columns at either end to the alpine table component', function () {
    $html = Blade::render('<x-avian::table :headers="[\'Name\', \'Email\', \'\']" sticky-columns="1" :sticky-columns-end="1"><tr><td>Ada</td></tr></x-avian::table>');

    expect($html)->toContain('x-data="auiTable(JSON.parse(')
        ->toContain('aui-table aui-table-hover aui-table-sticky"')
        ->toContain((string) Js::from(['start' => 1, 'end' => 1]))
        ->not->toContain('max-height');
});

it('renders a plain table without any sticky behaviour', function () {
    expect(Blade::render('<x-avian::table :headers="[\'Name\']"><tr><td>Ada</td></tr></x-avian::table>'))
        ->not->toContain('aui-table-sticky')
        ->not->toContain('auiTable(')
        ->not->toContain('max-height');
});
