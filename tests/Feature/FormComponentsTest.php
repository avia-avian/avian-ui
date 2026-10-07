<?php

declare(strict_types=1);

use AvianUi\AvianUi\Tests\Fixtures\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

function bindErrors(array $messages, string $bag = 'default'): void
{
    View::share('errors', (new ViewErrorBag)->put($bag, new MessageBag($messages)));
}

it('renders an input with its label, hint and generated id', function () {
    $html = Blade::render('<x-avian::input name="email" label="Email address" hint="We never share it." />');

    expect($html)->toContain('class="aui-field"')
        ->toContain('<label class="aui-label" for="aui-email">Email address</label>')
        ->toContain('class="aui-input"')
        ->toContain('name="email"')
        ->toContain('id="aui-email"')
        ->toContain('<span class="aui-hint">We never share it.</span>');
});

it('marks required inputs on both the label and the control', function () {
    $html = Blade::render('<x-avian::input name="name" label="Name" required />');

    expect($html)->toContain('aui-label aui-label-required')
        ->toContain('required="required"');
});

it('wires a numeric input to the alpine money mask as a text field', function () {
    $html = Blade::render('<x-avian::input name="budget" label="Budget" numeric />');

    expect($html)->toContain('x-mask:dynamic="$money($input)"')
        ->toContain('x-data="{}"')
        ->toContain('type="text"')
        ->toContain('inputmode="decimal"')
        ->not->toContain('type="number"');
});

it('leaves a plain input without any mask attributes', function () {
    $html = Blade::render('<x-avian::input name="budget" label="Budget" />');

    expect($html)->not->toContain('x-mask')
        ->not->toContain('inputmode');
});

it('pulls the validation message for the field out of the error bag', function () {
    bindErrors(['email' => ['The email field is required.']]);

    $html = Blade::render('<x-avian::input name="email" label="Email" />');

    expect($html)->toContain('aui-input-invalid')
        ->toContain('aria-invalid="true"')
        ->toContain('<span class="aui-error">The email field is required.</span>');
});

it('resolves bracket names against dotted validation keys', function () {
    bindErrors(['user.city' => ['The city is invalid.']]);

    expect(Blade::render('<x-avian::input name="user[city]" />'))->toContain('The city is invalid.');
});

it('reads validation messages from a named error bag', function () {
    bindErrors(['email' => ['Bag specific message.']], 'register');

    expect(Blade::render('<x-avian::input name="email" error-bag="register" />'))
        ->toContain('Bag specific message.');

    expect(Blade::render('<x-avian::input name="email" />'))
        ->not->toContain('Bag specific message.');
});

it('repopulates an input from old input', function () {
    session()->flashInput(['email' => 'old@example.com']);

    expect(Blade::render('<x-avian::input name="email" />'))->toContain('value="old@example.com"');
});

it('leaves the value to livewire when the input is wired', function () {
    session()->flashInput(['email' => 'old@example.com']);

    expect(Blade::render('<x-avian::input name="email" wire:model="email" />'))
        ->toContain('wire:model="email"')
        ->not->toContain('old@example.com');
});

it('never repopulates password inputs', function () {
    session()->flashInput(['secret' => 'hunter2']);

    expect(Blade::render('<x-avian::input name="secret" type="password" />'))->not->toContain('hunter2');
});

it('renders an input group with a prefix, suffix and icon', function () {
    $html = Blade::render('<x-avian::input name="site" prefix="https://" suffix=".com" icon="fas fa-globe" />');

    expect($html)->toContain('aui-input-group-prefixed')
        ->toContain('aui-input-group-suffixed')
        ->toContain('aui-input-group-icon')
        ->toContain('<span class="aui-input-affix aui-input-affix-prefix">https://</span>')
        ->toContain('<span class="aui-input-affix aui-input-affix-suffix">.com</span>')
        ->toContain('aui-input-icon fas fa-globe');
});

it('attaches prepended and appended buttons to the input edges', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::input name="qty" suffix="pcs">
            <x-slot:prepend><x-avian::button>Minus</x-avian::button></x-slot:prepend>
            <x-slot:append><x-avian::button>Plus</x-avian::button></x-slot:append>
        </x-avian::input>
        BLADE);

    expect($html)->toContain('aui-input-group aui-input-group-suffixed aui-input-group-prepended aui-input-group-appended')
        ->toContain('<div class="aui-input-addon aui-input-addon-prepend">')
        ->toContain('<div class="aui-input-addon aui-input-addon-append">')
        ->toContain('Minus')
        ->toContain('Plus');

    // prepend, input, suffix, append — in that order.
    expect(strpos($html, 'Minus'))->toBeLessThan(strpos($html, 'name="qty"'))
        ->and(strpos($html, 'name="qty"'))->toBeLessThan(strpos($html, 'pcs'))
        ->and(strpos($html, 'pcs'))->toBeLessThan(strpos($html, 'Plus'));
});

it('keeps the icon inside the text box next to an appended button', function () {
    $html = Blade::render('<x-avian::input name="q" icon="fas fa-search"><x-slot:append><x-avian::button>Go</x-avian::button></x-slot:append></x-avian::input>');

    expect($html)->toContain('<div class="aui-input-control">')
        ->toContain('aui-input-icon fas fa-search')
        ->toContain('aui-input-group-appended');
});

it('does not group an input for an empty append slot', function () {
    expect(Blade::render('<x-avian::input name="q"><x-slot:append></x-slot:append></x-avian::input>'))
        ->not->toContain('aui-input-group');
});

it('renders a bare input without the field wrapper', function () {
    $html = Blade::render('<x-avian::input name="q" :field="false" placeholder="Search" />');

    expect($html)->toContain('class="aui-input"')
        ->toContain('placeholder="Search"')
        ->not->toContain('aui-field');
});

it('renders a textarea with its value as content', function () {
    $html = Blade::render('<x-avian::textarea name="notes" label="Notes" :value="\'Hello\'" rows="6" />');

    expect($html)->toContain('class="aui-textarea"')
        ->toContain('rows="6"')
        ->toContain('>Hello</textarea>');
});

it('renders a select with options and marks the selected one', function () {
    $html = Blade::render(
        '<x-avian::select name="role" label="Role" placeholder="Choose..." :options="$options" :value="\'admin\'" />',
        ['options' => ['admin' => 'Administrator', 'user' => 'User']],
    );

    expect($html)->toContain('class="aui-select"')
        ->toContain('<option value="">Choose...</option>')
        ->toContain('<option value="admin" selected>Administrator</option>')
        ->toContain('<option value="user" >User</option>');
});

it('marks selected options for a multiple select', function () {
    $html = Blade::render(
        '<x-avian::select name="tags[]" multiple :options="$options" :value="$value" />',
        ['options' => ['a' => 'A', 'b' => 'B'], 'value' => ['b']],
    );

    expect($html)->toContain('<option value="b" selected>B</option>')
        ->toContain('<option value="a" >A</option>');
});

it('renders a searchable select wired to its alpine component', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="role" label="Role" placeholder="Choose..." :options="$options" :value="\'admin\'" />',
        ['options' => ['admin' => 'Administrator', 'user' => 'User']],
    );

    expect($html)->toContain('x-data="auiSearchableSelect(')
        ->toContain('class="aui-field"')
        ->toContain('aui-select aui-combobox-trigger')
        ->toContain('>Administrator</span>')
        ->toContain('aui-combobox-item active')
        ->toContain('data-label="Administrator"')
        ->toContain('data-label="User"')
        ->toContain('type="hidden"')
        ->toContain('name="role"')
        ->toContain('value="admin"');
});

it('shows the placeholder when a searchable select has no selection', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="role" placeholder="Choose a role" :options="$options" />',
        ['options' => ['admin' => 'Administrator']],
    );

    expect($html)->toContain('>Choose a role</span>')
        ->not->toContain('aui-combobox-item active');
});

it('leaves the searchable select value to livewire when wired', function () {
    session()->flashInput(['role' => 'admin']);

    $html = Blade::render(
        '<x-avian::searchable-select name="role" :options="$options" wire:model="role" />',
        ['options' => ['admin' => 'Administrator']],
    );

    expect($html)->toContain('wire:model="role"')
        ->not->toContain('value="admin"');
});

it('renders custom option markup passed as children instead of the options prop', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::searchable-select name="item" :value="'a1'">
            <x-avian::searchable-select.option value="a1" label="Item A1" selected="a1">
                <strong>Item A1</strong> <small>First batch</small>
            </x-avian::searchable-select.option>
            <x-avian::searchable-select.option value="b2" label="Item B2" selected="a1" />
        </x-avian::searchable-select>
    BLADE);

    expect($html)->toContain('<strong>Item A1</strong>')
        ->toContain('<small>First batch</small>')
        ->toContain('>Item B2</span>')
        ->toContain('aui-combobox-item active')
        ->toContain('wire:key="aui-combobox-option-a1"')
        ->toContain('wire:key="aui-combobox-option-b2"');
});

it('hands filtering to the server when a search model is given', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="status" wire:model.live="filter.status" :value="$value" :options="$options" search-model="filter.statusSearch" search-debounce="300ms" />',
        ['options' => ['open' => 'Open'], 'value' => 'open'],
    );

    expect($html)->toContain('wire:model.live.debounce.300ms="filter.statusSearch"')
        ->not->toContain('x-model="search"')
        ->not->toContain('x-on:input="typed(search)"');
});

it('renders a clear button only for a clearable searchable select', function () {
    $options = ['admin' => 'Administrator'];

    $clearable = Blade::render('<x-avian::searchable-select name="role" :options="$options" clearable />', ['options' => $options]);
    $plain = Blade::render('<x-avian::searchable-select name="role" :options="$options" />', ['options' => $options]);
    $disabled = Blade::render('<x-avian::searchable-select name="role" :options="$options" clearable disabled />', ['options' => $options]);

    expect($clearable)->toContain('is-clearable')
        ->toContain('class="aui-combobox-clear"')
        ->toContain('x-on:click.stop="clear()"')
        ->toContain('x-on:keydown.backspace.prevent="clear()"')
        ->and($plain)->not->toContain('aui-combobox-clear')
        ->not->toContain('is-clearable')
        ->and($disabled)->not->toContain('class="aui-combobox-clear"');
});

it('offers typed values as new options in a taggable searchable select', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="city" :options="$options" taggable create-text="Use :term" />',
        ['options' => ['jkt' => 'Jakarta']],
    );

    expect($html)->toContain('taggable: true')
        ->toContain('createText: \'Use :term\'')
        ->toContain('aui-combobox-item aui-combobox-create')
        ->toContain('x-on:click="create()"');

    expect(Blade::render('<x-avian::searchable-select name="city" :options="[]" />'))
        ->not->toContain('aui-combobox-create')
        ->toContain('taggable: false');
});

it('labels a taggable value that is not among the options with the value itself', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="city" :options="$options" value="Bogor" taggable />',
        ['options' => ['jkt' => 'Jakarta']],
    );

    expect($html)->toContain('>Bogor</span>')
        ->toContain('value="Bogor"')
        ->toContain('data-aui-labels="{&quot;Bogor&quot;:&quot;Bogor&quot;}"');

    expect(Blade::render(
        '<x-avian::searchable-select name="city" :options="$options" value="Bogor" placeholder="Pick" />',
        ['options' => ['jkt' => 'Jakarta']],
    ))->toContain('>Pick</span>');
});

it('hides the server-rendered empty state while a taggable search model can create', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="city" search-model="citySearch" :options="[]" taggable />',
    );

    expect($html)->toContain('x-on:input="typed($event.target.value)"')
        ->toContain('x-show="!canCreate"');
});

it('shows the empty state for a search model list with no results', function () {
    $html = Blade::render(
        '<x-avian::searchable-select name="status" search-model="statusSearch" :options="[]" empty-text="Nothing matched" />',
    );

    expect($html)->toContain('Nothing matched')
        ->not->toContain('x-ref="empty"');
});

it('renders a multi select with chips, options and array input names', function () {
    $html = Blade::render(
        '<x-avian::multi-select name="tags" label="Tags" :options="$options" :value="[\'php\', \'go\']" />',
        ['options' => ['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go']],
    );

    expect($html)->toContain('x-data="auiMultiSelect(')
        ->toContain('x-modelable="values"')
        ->toContain('<label class="aui-label" for="aui-tags">Tags</label>')
        ->toContain('aui-select aui-combobox-trigger aui-multiselect-trigger')
        ->toContain('data-aui-values="[&quot;php&quot;,&quot;go&quot;]"')
        ->toContain('name="tags[]"')
        ->toContain('aria-multiselectable="true"')
        ->toContain('<span class="aui-multiselect-chip">PHP</span>')
        ->toContain('<span class="aui-multiselect-chip">Go</span>')
        ->toContain('wire:key="aui-multiselect-option-js"')
        ->toContain('data-label="JavaScript"');

    expect(substr_count($html, 'aui-combobox-item active'))->toBe(2);
});

it('preselects a multi select option marked as selected', function () {
    $html = Blade::render(
        '<x-avian::multi-select name="tags"><x-avian::multi-select.option value="php" label="PHP" selected /><x-avian::multi-select.option value="go" label="Go" /></x-avian::multi-select>',
    );

    expect($html)->toContain('preselect(&#039;php&#039;)')
        ->not->toContain('preselect(&#039;go&#039;)');

    expect(substr_count($html, 'aui-combobox-item active'))->toBe(1);
});

it('keeps an explicit array suffix on the multi select name', function () {
    $html = Blade::render('<x-avian::multi-select name="tags[]" :options="[\'a\' => \'A\']" />');

    expect($html)->toContain('name="tags[]"')
        ->not->toContain('tags[][]')
        ->toContain('id="aui-tags"');
});

it('repopulates the multi select from old input', function () {
    session()->flashInput(['tags' => ['js']]);

    $html = Blade::render(
        '<x-avian::multi-select name="tags" :options="$options" />',
        ['options' => ['php' => 'PHP', 'js' => 'JavaScript']],
    );

    expect($html)->toContain('data-aui-values="[&quot;js&quot;]"')
        ->toContain('<span class="aui-multiselect-chip">JavaScript</span>');
});

it('shows the multi select placeholder when nothing is picked', function () {
    $html = Blade::render('<x-avian::multi-select name="tags" placeholder="Pick tags" :options="[\'a\' => \'A\']" />');

    expect($html)->toContain('data-aui-values="[]"')
        ->toContain('>Pick tags</span>')
        ->not->toContain('aui-combobox-item active');
});

it('binds the multi select to livewire through x-modelable', function () {
    session()->flashInput(['tags' => ['a']]);

    $html = Blade::render('<x-avian::multi-select name="tags" wire:model.live="tags" :options="[\'a\' => \'A\']" />');

    expect($html)->toContain('x-modelable="values"')
        ->toContain('wire:model.live="tags"')
        ->toContain('data-aui-values="[]"');
});

it('shows item level validation messages on the multi select', function () {
    bindErrors(['tags.1' => ['The selected tag is invalid.']]);

    $html = Blade::render('<x-avian::multi-select name="tags" :options="[\'a\' => \'A\']" />');

    expect($html)->toContain('The selected tag is invalid.')
        ->toContain('aui-select-invalid')
        ->toContain('aria-invalid="true"');
});

it('renders custom multi select option markup passed as children', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::multi-select name="users" :value="['1']">
            <x-avian::multi-select.option value="1" label="Ada" :selected="['1']">
                <strong>Ada</strong> <small>ada@example.com</small>
            </x-avian::multi-select.option>
            <x-avian::multi-select.option value="2" label="Grace" :selected="['1']" />
        </x-avian::multi-select>
    BLADE);

    expect($html)->toContain('<small>ada@example.com</small>')
        ->toContain('>Grace</span>')
        ->toContain('x-on:click="toggleValue(')
        ->toContain('aui-combobox-item active')
        ->toContain('data-value="2"');
});

it('renders a clear button only for a clearable multi select', function () {
    $options = ['php' => 'PHP'];

    $clearable = Blade::render('<x-avian::multi-select name="tags" :options="$options" clearable />', ['options' => $options]);
    $plain = Blade::render('<x-avian::multi-select name="tags" :options="$options" />', ['options' => $options]);
    $disabled = Blade::render('<x-avian::multi-select name="tags" :options="$options" clearable disabled />', ['options' => $options]);

    expect($clearable)->toContain('is-clearable')
        ->toContain('class="aui-combobox-clear"')
        ->toContain('x-on:keydown.delete.prevent="clear()"')
        ->and($plain)->not->toContain('class="aui-combobox-clear"')
        ->not->toContain('is-clearable')
        ->and($disabled)->not->toContain('class="aui-combobox-clear"');
});

it('offers typed values as new chips in a taggable multi select', function () {
    $html = Blade::render(
        '<x-avian::multi-select name="tags" :options="$options" :value="[\'php\', \'ui kit\']" taggable create-text="Use :term" />',
        ['options' => ['php' => 'PHP']],
    );

    expect($html)->toContain('taggable: true')
        ->toContain('createText: \'Use :term\'')
        ->toContain('aui-combobox-item aui-combobox-create')
        ->toContain('x-on:click="create()"')
        ->toContain('removeLast()')
        ->toContain('<span class="aui-multiselect-chip">ui kit</span>');

    expect(Blade::render('<x-avian::multi-select name="tags" :options="[]" />'))
        ->not->toContain('aui-combobox-create')
        ->toContain('taggable: false');
});

it('renders a datepicker input with its flatpickr hook attributes', function () {
    $html = Blade::render('<x-avian::datepicker name="start_date" label="Start date" />');

    expect($html)->toContain('class="aui-field"')
        ->toContain('<label class="aui-label" for="aui-start-date">Start date</label>')
        ->toContain('class="aui-input flatpickr-input"')
        ->toContain('name="start_date"')
        ->toContain('id="aui-start-date"')
        ->toContain('data-fp-mode="single"')
        ->toContain('data-fp-date-format="d/m/Y"')
        ->not->toContain('data-fp-enable-time')
        ->not->toContain('data-fp-min-date')
        ->not->toContain('data-fp-max-date');
});

it('renders a datepicker with range, time and bounds options', function () {
    $html = Blade::render(
        '<x-avian::datepicker name="range" mode="range" enable-time date-format="Y-m-d H:i" min-date="2024-01-01" max-date="2024-12-31" />',
    );

    expect($html)->toContain('data-fp-mode="range"')
        ->toContain('data-fp-date-format="Y-m-d H:i"')
        ->toContain('data-fp-enable-time="true"')
        ->toContain('data-fp-min-date="2024-01-01"')
        ->toContain('data-fp-max-date="2024-12-31"');
});

it('renders a time only datepicker without the calendar', function () {
    $html = Blade::render('<x-avian::datepicker name="opens_at" mode="time" min-time="08:00" max-time="17:00" />');

    expect($html)->toContain('data-fp-mode="single"')
        ->toContain('data-fp-date-format="H:i"')
        ->toContain('data-fp-enable-time="true"')
        ->toContain('data-fp-no-calendar="true"')
        ->toContain('data-fp-time-24hr="true"')
        ->toContain('data-fp-min-time="08:00"')
        ->toContain('data-fp-max-time="17:00"')
        ->toContain('aui-timepicker')
        ->not->toContain('data-fp-mode="time"');
});

it('switches a time picker to a 12 hour clock and keeps date pickers calendar based', function () {
    expect(Blade::render('<x-avian::datepicker name="at" mode="time" :time24hr="false" date-format="h:i K" />'))
        ->toContain('data-fp-date-format="h:i K"')
        ->not->toContain('data-fp-time-24hr');

    expect(Blade::render('<x-avian::datepicker name="on" min-time="08:00" />'))
        ->not->toContain('data-fp-no-calendar')
        ->not->toContain('data-fp-time-24hr')
        ->not->toContain('data-fp-min-time');
});

it('pulls the validation message for the datepicker out of the error bag', function () {
    bindErrors(['start_date' => ['The start date field is required.']]);

    $html = Blade::render('<x-avian::datepicker name="start_date" label="Start date" />');

    expect($html)->toContain('aui-input-invalid')
        ->toContain('aria-invalid="true"')
        ->toContain('<span class="aui-error">The start date field is required.</span>');
});

it('repopulates a datepicker from old input', function () {
    session()->flashInput(['start_date' => '01/06/2024']);

    expect(Blade::render('<x-avian::datepicker name="start_date" />'))->toContain('value="01/06/2024"');
});

it('leaves the datepicker value to livewire when wired', function () {
    session()->flashInput(['start_date' => '01/06/2024']);

    expect(Blade::render('<x-avian::datepicker name="start_date" wire:model="startDate" />'))
        ->toContain('wire:model="startDate"')
        ->not->toContain('01/06/2024');
});

it('renders a disabled datepicker', function () {
    $html = Blade::render('<x-avian::datepicker name="start_date" :disabled="true" />');

    expect($html)->toContain('disabled="disabled"');
});

it('renders a bare datepicker without the field wrapper', function () {
    $html = Blade::render('<x-avian::datepicker name="start_date" :field="false" placeholder="Select date" />');

    expect($html)->toContain('class="aui-input flatpickr-input"')
        ->toContain('placeholder="Select date"')
        ->not->toContain('aui-field');
});

it('renders a checkbox with a label and checked state', function () {
    $html = Blade::render('<x-avian::checkbox name="terms" label="I agree" checked />');

    expect($html)->toContain('class="aui-check"')
        ->toContain('type="checkbox"')
        ->toContain('checked="checked"')
        ->toContain('<span class="aui-check-label">I agree</span>');
});

it('renders inline radios that share a name', function () {
    $html = Blade::render('<x-avian::radio name="plan" value="pro" label="Pro" inline />');

    expect($html)->toContain('aui-check aui-check-inline')
        ->toContain('type="radio"')
        ->toContain('name="plan"')
        ->toContain('value="pro"')
        ->toContain('id="aui-plan-pro"');
});

it('renders a switch as a checkbox with switch semantics', function () {
    $html = Blade::render('<x-avian::switch name="active" label="Active" checked />');

    expect($html)->toContain('class="aui-switch"')
        ->toContain('aui-switch-input')
        ->toContain('role="switch"')
        ->toContain('checked="checked"')
        ->toContain('<span class="aui-switch-track"');
});

it('renders a file input wired to its alpine component', function () {
    $html = Blade::render('<x-avian::file name="logo" label="Logo" trigger="Browse" />');

    expect($html)->toContain('x-data="auiFile(')
        ->toContain('type="file"')
        ->toContain('x-ref="input"')
        ->toContain('x-on:change="update($event)"')
        ->toContain('x-on:click="browse()"')
        ->toContain('Browse');
});

it('renders a form with csrf protection and method spoofing', function () {
    $html = Blade::render('<x-avian::form action="/users/1" method="PUT" files>Fields</x-avian::form>');

    expect($html)->toContain('method="POST"')
        ->toContain('action="/users/1"')
        ->toContain('enctype="multipart/form-data"')
        ->toContain('name="_token"')
        ->toContain('name="_method" value="PUT"');
});

it('omits csrf on get forms', function () {
    expect(Blade::render('<x-avian::form action="/search" method="GET">Fields</x-avian::form>'))
        ->toContain('method="GET"')
        ->not->toContain('name="_token"');
});

it('renders a standalone error component for a field', function () {
    bindErrors(['email' => ['Invalid email.']]);

    expect(Blade::render('<x-avian::error name="email" />'))
        ->toContain('<span class="aui-error">Invalid email.</span>');

    expect(Blade::render('<x-avian::error name="name" />'))->toBe('');
});

it('rechecks a checkbox, radio and switch from old input', function () {
    session()->flashInput(['terms' => '1', 'plan' => 'pro', 'notify' => '1', 'tags' => ['php', 'go']]);

    expect(Blade::render('<x-avian::checkbox name="terms" label="Terms" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::radio name="plan" value="pro" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::radio name="plan" value="free" />'))->not->toContain('checked')
        ->and(Blade::render('<x-avian::switch name="notify" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::checkbox name="tags[]" value="go" />'))->toContain('checked="checked"')
        ->and(Blade::render('<x-avian::checkbox name="tags[]" value="js" />'))->not->toContain('checked');
});

it('unchecks a default checked checkbox that was left unticked on submit', function () {
    session()->flashInput(['name' => 'Ada']);

    expect(Blade::render('<x-avian::checkbox name="terms" checked />'))->not->toContain('checked')
        ->and(Blade::render('<x-avian::switch name="notify" checked />'))->not->toContain('checked');
});

it('keeps the checked prop when there is no old input or the box is wired', function () {
    expect(Blade::render('<x-avian::checkbox name="terms" checked />'))->toContain('checked="checked"');

    session()->flashInput(['name' => 'Ada']);

    expect(Blade::render('<x-avian::checkbox name="terms" wire:model="terms" checked />'))->toContain('checked="checked"');
});

it('links the label and errors of a wire:model field that has no name', function (string $component) {
    bindErrors(['email' => ['The email field is required.']]);

    $html = Blade::render("<x-avian::{$component} wire:model=\"email\" label=\"Email\" />");

    expect($html)->toContain('for="aui-email')
        ->toContain('id="aui-email')
        ->toContain('The email field is required.')
        ->not->toContain('name=');
})->with(['input', 'textarea', 'select', 'datepicker', 'searchable-select', 'multi-select']);

it('prefers old input over the saved value after a failed validation', function () {
    session()->flashInput(['title' => 'Edited', 'body' => 'Edited body', 'status' => 'b', 'owner' => 'b', 'tags' => ['b']]);

    $options = ['a' => 'A', 'b' => 'B'];

    expect(Blade::render('<x-avian::input name="title" value="Saved" />'))->toContain('value="Edited"')
        ->and(Blade::render('<x-avian::textarea name="body">Saved</x-avian::textarea>'))->toContain('Edited body')
        ->and(Blade::render('<x-avian::select name="status" value="a" :options="$options" />', ['options' => $options]))
        ->toContain('<option value="b" selected>')
        ->and(Blade::render('<x-avian::searchable-select name="owner" value="a" :options="$options" />', ['options' => $options]))
        ->toContain('data-aui-value="b"')
        ->and(Blade::render('<x-avian::multi-select name="tags" :value="[\'a\']" :options="$options" />', ['options' => $options]))
        ->toContain('data-aui-values="[&quot;b&quot;]"');
});

it('keeps an empty old value instead of falling back to the saved value', function () {
    session()->flashInput(['title' => null]);

    expect(Blade::render('<x-avian::input name="title" value="Saved" />'))->not->toContain('Saved');
});

it('keeps the explicit value of a hidden input over old input', function () {
    session()->flashInput(['id' => '9']);

    expect(Blade::render('<x-avian::input type="hidden" name="id" value="5" />'))->toContain('value="5"');
});

it('accepts enum values in the selects', function () {
    $options = ['pending' => 'Pending', 'shipped' => 'Shipped'];
    $data = ['options' => $options, 'status' => OrderStatus::Shipped];

    expect(Blade::render('<x-avian::searchable-select name="status" :value="$status" :options="$options" />', $data))
        ->toContain('data-aui-value="shipped"')
        ->toContain('>Shipped</span>')
        ->and(Blade::render('<x-avian::multi-select name="statuses" :value="[$status]" :options="$options" />', $data))
        ->toContain('data-aui-values="[&quot;shipped&quot;]"')
        ->and(Blade::render('<x-avian::select name="status" :value="$status" :options="$options" />', $data))
        ->toContain('<option value="shipped" selected>');
});

it('writes a date value in the datepicker format', function () {
    $html = Blade::render(
        '<x-avian::datepicker name="starts_at" :value="$date" :min-date="$date" />',
        ['date' => Carbon::create(2026, 10, 5, 14, 30)],
    );

    expect($html)->toContain('value="05/10/2026"')
        ->toContain('data-fp-min-date="05/10/2026"');

    expect(Blade::render(
        '<x-avian::datepicker name="at" enable-time date-format="Y-m-d H:i" :value="$date" />',
        ['date' => Carbon::create(2026, 10, 5, 14, 30)],
    ))->toContain('value="2026-10-05 14:30"');
});

it('joins a date range with the flatpickr range separator', function () {
    $html = Blade::render(
        '<x-avian::datepicker name="range" mode="range" :value="$range" />',
        ['range' => [Carbon::create(2026, 10, 1), Carbon::create(2026, 10, 5)]],
    );

    expect($html)->toContain('value="01/10/2026 to 05/10/2026"');
});

it('lets an autocomplete attribute replace the datepicker default', function () {
    $html = Blade::render('<x-avian::datepicker name="dob" autocomplete="bday" />');

    expect($html)->toContain('autocomplete="bday"')
        ->not->toContain('autocomplete="off"');
});

it('stops escape inside a combobox list from reaching an enclosing modal', function (string $component) {
    expect(Blade::render("<x-avian::{$component} name=\"tags\" :options=\"['a' => 'A']\" />"))
        ->toContain('x-on:keydown.escape.prevent.stop=');
})->with(['searchable-select', 'multi-select']);
