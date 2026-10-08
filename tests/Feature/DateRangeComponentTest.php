<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

it('renders a range picker that submits a from and a to field', function () {
    $html = Blade::render('<x-avian::date-range name="period" label="Period" />');

    expect($html)->toContain('x-data="auiDateRange({ format: \'Y-m-d\' })"')
        ->toContain('x-modelable="value"')
        ->toContain('data-aui-value="{&quot;from&quot;:&quot;&quot;,&quot;to&quot;:&quot;&quot;}"')
        ->toContain('class="aui-date-range"')
        ->toContain('data-fp-mode="range"')
        ->toContain('data-fp-date-format="d/m/Y"')
        ->toContain('class="aui-input flatpickr-input"')
        ->toContain('id="aui-period"')
        ->toContain('for="aui-period"')
        ->toContain('x-on:change="changed()"')
        ->toContain('<input type="hidden" name="period[from]" value="" x-bind:value="value.from">')
        ->toContain('<input type="hidden" name="period[to]" value="" x-bind:value="value.to">')
        ->not->toContain('aui-date-range-presets');
});

it('shows a saved range in the display format and submits the value format', function () {
    $html = Blade::render('<x-avian::date-range name="period" :value="$value" />', [
        'value' => ['from' => Carbon::parse('2026-03-01'), 'to' => '2026-03-15'],
    ]);

    expect($html)->toContain('value="01/03/2026 to 15/03/2026"')
        ->toContain('name="period[from]" value="2026-03-01"')
        ->toContain('name="period[to]" value="2026-03-15"');
});

it('swaps a range given backwards', function () {
    $html = Blade::render('<x-avian::date-range name="period" :value="[\'from\' => \'2026-03-15\', \'to\' => \'2026-03-01\']" />');

    expect($html)->toContain('name="period[from]" value="2026-03-01"')
        ->toContain('name="period[to]" value="2026-03-15"');
});

it('uses custom display and value formats', function () {
    $html = Blade::render('<x-avian::date-range name="period" date-format="Y-m-d" value-format="d.m.Y" :value="[\'from\' => \'01.03.2026\', \'to\' => \'15.03.2026\']" />');

    expect($html)->toContain("auiDateRange({ format: 'd.m.Y' })")
        ->toContain('value="2026-03-01 to 2026-03-15"')
        ->toContain('name="period[from]" value="01.03.2026"');
});

it('ignores a value it cannot read as a date', function () {
    $html = Blade::render('<x-avian::date-range name="period" :value="[\'from\' => \'not a date\', \'to\' => \'\']" />');

    expect($html)->toContain('name="period[from]" value=""')
        ->toContain('name="period[to]" value=""');
});

it('repopulates the range from old input', function () {
    session()->flashInput(['period' => ['from' => '2026-04-01', 'to' => '2026-04-30']]);

    expect(Blade::render('<x-avian::date-range name="period" />'))
        ->toContain('name="period[from]" value="2026-04-01"')
        ->toContain('name="period[to]" value="2026-04-30"');
});

it('reads an error from the field or from either end of the range', function (string $key) {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag([$key => 'Pick a valid period.'])));

    expect(Blade::render('<x-avian::date-range name="period" />'))
        ->toContain('aui-input-invalid')
        ->toContain('aria-invalid="true"')
        ->toContain('Pick a valid period.');
})->with(['period', 'period.from', 'period.to']);

it('renders the default presets and a clear button', function () {
    $html = Blade::render('<x-avian::date-range name="period" presets clearable />');

    expect($html)->toContain('class="aui-date-range-presets" role="group" aria-label="Date presets"')
        ->toContain('x-on:click="preset(\'today\')"')
        ->toContain('x-on:click="preset(\'last_7_days\')"')
        ->toContain('x-on:click="preset(\'last_30_days\')"')
        ->toContain('x-on:click="preset(\'this_month\')"')
        ->toContain('x-on:click="preset(\'last_month\')"')
        ->not->toContain("preset('yesterday')")
        ->toContain('>Last 7 days</button>')
        ->toContain('x-on:click="clear()"')
        ->toContain('>Clear</button>');
});

it('renders only the known presets it is given', function () {
    $html = Blade::render('<x-avian::date-range name="period" :presets="[\'this_year\', \'next_decade\', \'yesterday\']" />');

    expect($html)->toContain("preset('this_year')")
        ->toContain("preset('yesterday')")
        ->not->toContain('next_decade')
        ->not->toContain('x-on:click="clear()"');
});

it('binds wire:model on the wrapper and leaves the hidden fields out without a name', function () {
    session()->flashInput(['period' => ['from' => '2026-04-01', 'to' => '2026-04-30']]);

    $html = Blade::render('<x-avian::date-range wire:model.live="period" :value="[\'from\' => \'2026-01-01\', \'to\' => \'2026-01-02\']" />');

    expect($html)->toContain('wire:model.live="period"')
        ->toContain('id="aui-period"')
        ->not->toContain('type="hidden"')
        ->toContain('&quot;from&quot;:&quot;2026-01-01&quot;')
        ->and(substr_count($html, 'wire:model'))->toBe(1);
});

it('disables the field and the presets, and passes the date bounds', function () {
    $html = Blade::render('<x-avian::date-range name="period" presets disabled required :min-date="$min" max-date="31/12/2026" />', [
        'min' => Carbon::parse('2026-01-01'),
    ]);

    expect($html)->toContain('class="aui-date-range is-disabled"')
        ->toContain('data-fp-min-date="01/01/2026"')
        ->toContain('data-fp-max-date="31/12/2026"')
        ->toContain('required="required"')
        ->toContain('disabled="disabled"')
        ->and(substr_count($html, 'disabled'))->toBeGreaterThan(5);
});
