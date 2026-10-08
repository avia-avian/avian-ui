<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

it('renders a slider as a range input with its scale and fill', function () {
    $html = Blade::render('<x-avian::slider name="volume" label="Volume" :min="0" :max="200" :step="5" :value="50" show-value suffix="%" />');

    expect($html)->toContain('x-data="auiSlider(')
        ->toContain('type="range"')
        ->toContain('name="volume"')
        ->toContain('id="aui-volume"')
        ->toContain('min="0"')
        ->toContain('max="200"')
        ->toContain('step="5"')
        ->toContain('value="50"')
        ->toContain('--aui-slider-end: 25%')
        ->toContain('<label class="aui-label" for="aui-volume">Volume</label>')
        ->toContain('>50%</output>');
});

it('clamps a slider value into its scale', function () {
    expect(Blade::render('<x-avian::slider name="volume" :value="500" />'))->toContain('value="100"')
        ->and(Blade::render('<x-avian::slider name="volume" :value="-5" />'))->toContain('value="0"')
        ->and(Blade::render('<x-avian::slider name="rating" :max="5" :step="0.5" :value="3.5" />'))
        ->toContain('value="3.5"')
        ->toContain('step="0.5"');
});

it('renders a range slider with min and max inputs in order', function () {
    $html = Blade::render('<x-avian::slider name="price" :max="1000" :value="[500, 100]" range show-value prefix="$" />');

    expect($html)->toContain('aui-slider aui-slider-range')
        ->toContain('name="price[min]"')
        ->toContain('name="price[max]"')
        ->toContain('--aui-slider-start: 10%; --aui-slider-end: 50%')
        ->toContain('$100 – $500</output>');
});

it('repopulates a slider and a range slider from old input', function () {
    session()->flashInput(['volume' => '75', 'price' => ['min' => '200', 'max' => '300']]);

    expect(Blade::render('<x-avian::slider name="volume" :value="10" />'))->toContain('value="75"')
        ->and(Blade::render('<x-avian::slider name="price" :max="1000" :value="[0, 1000]" range />'))
        ->toContain('value="200"')
        ->toContain('value="300"');
});

it('binds a wired slider through x-modelable on its wrapper', function () {
    $html = Blade::render('<x-avian::slider wire:model.live.debounce.300ms="volume" class="mt-2" />');

    expect($html)->toContain('x-modelable="value"')
        ->toContain('wire:model.live.debounce.300ms="volume"')
        ->toContain('class="aui-slider mt-2"')
        ->not->toMatch('/<input[^>]*wire:model/');
});

it('reads a range slider error from either bound', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['price.max' => 'The maximum is too high.'])));

    expect(Blade::render('<x-avian::slider name="price" range />'))
        ->toContain('aui-slider-invalid')
        ->toContain('aria-invalid="true"')
        ->toContain('<span class="aui-error">The maximum is too high.</span>');
});

it('registers the slider behaviour and ships its styles', function () {
    expect(file_get_contents(__DIR__.'/../../public/js/avian-ui.js'))->toContain('auiSlider: function')
        ->and(file_get_contents(__DIR__.'/../../public/css/avian-ui.css'))->toContain('.aui-slider-track {');
});

it('renders a slider with its defaults', function () {
    $html = Blade::render('<x-avian::slider name="volume" />');

    expect($html)->toContain('<div class="aui-field">')
        ->toContain('x-data="auiSlider({ range: false, min: 0, max: 100, step: 1 })"')
        ->toContain('class="aui-slider"')
        ->toContain('min="0"')
        ->toContain('max="100"')
        ->toContain('step="1"')
        ->toContain('value="0"')
        ->toContain('data-aui-value="0"')
        ->toContain('--aui-slider-start: 0%; --aui-slider-end: 0%')
        ->not->toContain('<label')
        ->not->toContain('<output')
        ->not->toContain('aui-hint')
        ->not->toContain('aui-error')
        ->not->toContain('aria-invalid')
        ->not->toContain('required')
        ->not->toContain('disabled');
});

it('renders a slider hint under the control', function () {
    expect(Blade::render('<x-avian::slider name="volume" hint="Drag to adjust." />'))
        ->toContain('<span class="aui-hint">Drag to adjust.</span>');
});

it('marks a required slider and its label', function () {
    $html = Blade::render('<x-avian::slider name="volume" label="Volume" required />');

    expect($html)->toContain('<label class="aui-label aui-label-required" for="aui-volume">Volume</label>')
        ->toMatch('/<input[^>]*\srequired\s/');
});

it('disables a slider and every thumb', function () {
    expect(Blade::render('<x-avian::slider name="volume" disabled />'))
        ->toContain('class="aui-slider is-disabled"')
        ->toMatch('/<input[^>]*\sdisabled\s/')
        ->and(preg_match_all('/<input[^>]*\sdisabled\s/', Blade::render('<x-avian::slider name="price" range disabled />')))
        ->toBe(2);
});

it('renders just the slider when the field wrapper is turned off', function () {
    $html = Blade::render('<x-avian::slider name="volume" label="Volume" hint="Help" error="Too loud." :field="false" />');

    expect($html)->not->toContain('aui-field')
        ->not->toContain('<label')
        ->not->toContain('aui-hint')
        ->not->toContain('aui-error')
        ->toContain('aui-slider-invalid')
        ->toContain('aria-invalid="true"');
});

it('uses an explicit id for the input, label and output', function () {
    $html = Blade::render('<x-avian::slider name="volume" id="loudness" label="Volume" show-value />');

    expect($html)->toContain('id="loudness"')
        ->toContain('for="loudness"')
        ->not->toContain('aui-volume');
});

it('derives a slider id from a bracketed or underscored name', function () {
    expect(Blade::render('<x-avian::slider name="filters[max_price]" />'))
        ->toContain('id="aui-filters-max-price"')
        ->toContain('name="filters[max_price]"');
});

it('renders a slider without a name or id when it has neither', function () {
    expect(Blade::render('<x-avian::slider label="Volume" show-value />'))
        ->not->toContain('name=')
        ->not->toContain('id=')
        ->not->toContain(' for=');
});

it('shows a forced slider error message', function () {
    expect(Blade::render('<x-avian::slider name="volume" error="Too loud." />'))
        ->toContain('aui-slider-invalid')
        ->toContain('aria-invalid="true"')
        ->toContain('<span class="aui-error">Too loud.</span>');
});

it('reads a slider error from the shared error bag', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['volume' => 'The volume is too high.'])));

    expect(Blade::render('<x-avian::slider name="volume" />'))
        ->toContain('<span class="aui-error">The volume is too high.</span>');
});

it('reads a slider error from a named error bag', function () {
    View::share('errors', (new ViewErrorBag)->put('settings', new MessageBag(['volume' => 'The volume is too high.'])));

    expect(Blade::render('<x-avian::slider name="volume" error-bag="settings" />'))->toContain('The volume is too high.')
        ->and(Blade::render('<x-avian::slider name="volume" />'))->not->toContain('aui-error');
});

it('reads a range slider error from its lower bound', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['price.min' => 'The minimum is too low.'])));

    expect(Blade::render('<x-avian::slider name="price" range />'))
        ->toContain('<span class="aui-error">The minimum is too low.</span>')
        ->and(Blade::render('<x-avian::slider name="price" />'))->not->toContain('aui-error');
});

it('falls back to a sane scale when given an invalid one', function () {
    $html = Blade::render('<x-avian::slider name="volume" :min="10" :max="5" :step="0" />');

    expect($html)->toContain('min="10"')
        ->toContain('max="110"')
        ->toContain('step="1"')
        ->toContain('value="10"');
});

it('falls back to the minimum when the slider value is not a number', function () {
    expect(Blade::render('<x-avian::slider name="volume" :min="20" value="loud" />'))
        ->toContain('value="20"');
});

it('formats decimal values without trailing zeros', function () {
    $html = Blade::render('<x-avian::slider name="weight" :min="0.5" :max="2.25" :step="0.25" :value="1.5" show-value suffix=" kg" />');

    expect($html)->toContain('min="0.5"')
        ->toContain('max="2.25"')
        ->toContain('step="0.25"')
        ->toContain('value="1.5"')
        ->toContain('>1.5 kg</output>');
});

it('renders a range slider with keyed values and labelled thumbs', function () {
    $html = Blade::render('<x-avian::slider name="price" label="Price" :value="$value" range />', [
        'value' => ['min' => 20, 'max' => 80],
    ]);

    expect($html)->toContain('x-data="auiSlider({ range: true, min: 0, max: 100, step: 1 })"')
        ->toContain('aria-label="Price minimum"')
        ->toContain('aria-label="Price maximum"')
        ->toContain('value="20"')
        ->toContain('value="80"')
        ->toContain('--aui-slider-start: 20%; --aui-slider-end: 80%')
        ->toContain('data-aui-value="{&quot;min&quot;:20,&quot;max&quot;:80}"');
});

it('spans the whole scale for a range slider without a value', function () {
    $html = Blade::render('<x-avian::slider name="price" :min="10" :max="50" range />');

    expect($html)->toMatch('/name="price\[min\]".*?value="10"/s')
        ->toMatch('/name="price\[max\]".*?value="50"/s')
        ->toContain('aria-label="Minimum"')
        ->toContain('aria-label="Maximum"');
});

it('puts the id on the lower thumb of a range slider only', function () {
    expect(substr_count(Blade::render('<x-avian::slider name="price" range />'), 'id="aui-price"'))->toBe(1);
});

it('ignores old input for a wired slider', function () {
    session()->flashInput(['volume' => '75']);

    expect(Blade::render('<x-avian::slider name="volume" wire:model="volume" :value="10" />'))
        ->toContain('value="10"')
        ->not->toContain('value="75"');
});

it('derives a wired slider id from its model and leaves out the name', function () {
    $html = Blade::render('<x-avian::slider wire:model="settings.volume" label="Volume" />');

    expect($html)->toContain('id="aui-settings-volume"')
        ->toContain('for="aui-settings-volume"')
        ->not->toContain('name=');
});

it('reads a wired slider error from its model', function () {
    View::share('errors', (new ViewErrorBag)->put('default', new MessageBag(['volume' => 'The volume is too high.'])));

    expect(Blade::render('<x-avian::slider wire:model="volume" />'))
        ->toContain('The volume is too high.');
});

it('binds a wired range slider on its wrapper only', function () {
    $html = Blade::render('<x-avian::slider wire:model.live.debounce.250ms="price" range />');

    expect($html)->toContain('x-modelable="value"')
        ->toContain('wire:model.live.debounce.250ms="price"')
        ->toContain('aui-slider aui-slider-range')
        ->not->toMatch('/<input[^>]*wire:model/');
});

it('forwards other attributes to the slider wrapper', function () {
    expect(Blade::render('<x-avian::slider name="volume" data-test="volume" x-on:change="save" />'))
        ->toMatch('/<div[^>]*data-test="volume"[^>]*>/s')
        ->toContain('x-on:change="save"');
});

it('escapes the slider label, hint and shown value', function () {
    $html = Blade::render('<x-avian::slider name="volume" :label="$label" :hint="$label" :prefix="$label" show-value />', [
        'label' => '<b>Loud</b>',
    ]);

    expect($html)->toContain('&lt;b&gt;Loud&lt;/b&gt;')
        ->not->toContain('<b>Loud</b>');
});
