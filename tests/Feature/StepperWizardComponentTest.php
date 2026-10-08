<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

it('marks steps before the current one complete and after it upcoming', function () {
    $html = Blade::render('<x-avian::stepper :steps="[\'Cart\', \'Shipping\', \'Payment\']" :current="2" />');

    expect($html)->toContain('<ol aria-label="Progress" class="aui-stepper">')
        ->toContain('<li class="aui-step is-complete" >')
        ->toContain('<li class="aui-step is-current"  aria-current="step" >')
        ->toContain('<li class="aui-step is-upcoming" >')
        ->toContain('<i class="fas fa-check"></i>')
        ->toContain('<span class="aui-step-title">Shipping</span>')
        ->and(substr_count($html, 'aria-current'))->toBe(1);
});

it('numbers the steps that are not done', function () {
    $html = Blade::render('<x-avian::stepper :steps="[\'One\', \'Two\', \'Three\']" :current="2" />');

    expect(preg_replace('/\s+/', ' ', $html))->toContain('aria-hidden="true"> 2 </span>')
        ->toContain('aria-hidden="true"> 3 </span>');
});

it('renders step descriptions, icons and a vertical small stepper', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::stepper vertical size="sm" label="Order progress" :current="1" :steps="[
            ['title' => 'Ordered', 'description' => 'Mar 4'],
            ['title' => 'Packed', 'icon' => 'fas fa-box'],
        ]" />
        BLADE);

    expect($html)->toContain('class="aui-stepper aui-stepper-vertical aui-stepper-sm"')
        ->toContain('aria-label="Order progress"')
        ->toContain('<span class="aui-step-description">Mar 4</span>')
        ->toContain('<i class="fas fa-box"></i>');
});

it('lets a step override its status, including an error', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::stepper :current="1" :steps="[
            ['title' => 'Paid', 'status' => 'complete'],
            ['title' => 'Charge', 'status' => 'error'],
            ['title' => 'Odd', 'status' => 'sideways'],
        ]" />
        BLADE);

    expect($html)->toContain('<li class="aui-step is-complete" >')
        ->toContain('<li class="aui-step is-error" >')
        ->toContain('<i class="fas fa-xmark"></i>')
        ->toContain('<li class="aui-step is-upcoming" >');
});

it('links only the complete steps that have an href', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::stepper :current="2" :steps="[
            ['title' => 'Cart', 'href' => '/cart'],
            ['title' => 'Shipping', 'href' => '/shipping'],
        ]" />
        BLADE);

    expect($html)->toContain('<a class="aui-step-inner" href="/cart">')
        ->not->toContain('href="/shipping"')
        ->toContain('<div class="aui-step-inner">');
});

it('escapes step titles and descriptions', function () {
    $html = Blade::render('<x-avian::stepper :steps="[[\'title\' => \'<b>T</b>\', \'description\' => \'<i>D</i>\']]" />');

    expect($html)->toContain('&lt;b&gt;T&lt;/b&gt;')
        ->toContain('&lt;i&gt;D&lt;/i&gt;');
});

it('renders a wizard with its steps, header and buttons', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::wizard>
            <x-avian::wizard.step title="Account" description="Sign in">Email field</x-avian::wizard.step>
            <x-avian::wizard.step title="Profile">Name field</x-avian::wizard.step>
        </x-avian::wizard>
        BLADE);

    expect($html)->toContain('x-data="auiWizard({ linear: true })"')
        ->toContain('x-modelable="step"')
        ->toContain('data-aui-wizard')
        ->toContain('data-aui-step="1"')
        ->toContain('class="aui-wizard"')
        ->toContain('<ol class="aui-stepper aui-wizard-header" aria-label="Progress">')
        ->toContain('x-for="(item, index) in steps"')
        ->toContain('aria-live="polite"')
        ->toContain('data-template="Step __current__ of __total__"')
        ->toContain('data-title="Account"')
        ->toContain('data-description="Sign in"')
        ->toContain('aria-label="Account"')
        ->toContain('x-show="isActive($el)"')
        ->toContain('Email field')
        ->toContain('Name field')
        ->toContain('x-on:click="back()"')
        ->toContain('x-on:click="next()"')
        ->toContain('type="submit"')
        ->toContain('x-on:click="finish($event)"')
        ->toContain('Back')
        ->toContain('Next')
        ->toContain('Finish')
        ->and(substr_count($html, 'data-aui-wizard-step'))->toBe(2);
});

it('starts a wizard on another step, non-linear, with custom labels', function () {
    $html = Blade::render('<x-avian::wizard :step="2" :linear="false" next-label="Continue" back-label="Previous" finish-label="Create account" finish-icon="fas fa-user-plus"><x-avian::wizard.step title="A" /></x-avian::wizard>');

    expect($html)->toContain('auiWizard({ linear: false })')
        ->toContain('data-aui-step="2"')
        ->toContain('Continue')
        ->toContain('Previous')
        ->toContain('Create account')
        ->toContain('fas fa-user-plus');
});

it('binds wire:model on the wizard root, not on its buttons', function () {
    $html = Blade::render('<x-avian::wizard wire:model="step" class="mt-4"><x-avian::wizard.step title="A" /></x-avian::wizard>');

    expect($html)->toContain('x-modelable="step"')
        ->toContain('wire:model="step"')
        ->toContain('class="aui-wizard mt-4"')
        ->and(substr_count($html, 'wire:model'))->toBe(1);
});

it('escapes wizard step titles', function () {
    expect(Blade::render('<x-avian::wizard.step title="<b>A</b>" />'))
        ->toContain('data-title="&lt;b&gt;A&lt;/b&gt;"')
        ->not->toContain('<b>A</b>');
});
