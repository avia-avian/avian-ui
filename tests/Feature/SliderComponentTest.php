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
