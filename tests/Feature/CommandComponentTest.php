<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

function renderCommand(string $attributes = '', string $body = ''): string
{
    return Blade::render(<<<BLADE
        <x-avian::command {$attributes}>
            {$body}
        </x-avian::command>
        BLADE);
}

it('renders a modal search dialog wired to alpine', function () {
    $html = renderCommand();

    expect($html)->toContain('class="aui-command-root"')
        ->toContain("x-data=\"auiCommand({ name: null, shortcut: 'mod+k', server: false })\"")
        ->toContain('x-on:keydown.window="shortcutPressed($event)"')
        ->toContain('x-on:aui-command-open.window="openFrom($event)"')
        ->toContain('role="dialog"')
        ->toContain('aria-modal="true"')
        ->toContain('aria-label="Command palette"')
        ->toContain('role="combobox"')
        ->toContain('placeholder="Search or jump to…"')
        ->toContain('role="listbox"')
        ->toContain('No data found')
        ->toContain('aui-command-footer')
        ->not->toContain('wire:model');
});

it('points the combobox at its listbox', function () {
    $html = renderCommand();

    preg_match('/aria-controls="([^"]+)"/', $html, $controls);

    expect($html)->toContain('id="'.$controls[1].'"');
});

it('takes a name, shortcut, copy and no footer', function () {
    $html = renderCommand('name="admin" shortcut="ctrl+shift+p" placeholder="Find…" empty-text="Nothing" label="Admin search" :footer="false"');

    expect($html)->toContain("auiCommand({ name: 'admin', shortcut: 'ctrl+shift+p', server: false })")
        ->toContain('placeholder="Find…"')
        ->toContain('Nothing')
        ->toContain('aria-label="Admin search"')
        ->not->toContain('aui-command-footer');
});

it('turns the shortcut off', function () {
    expect(renderCommand(':shortcut="false"'))->toContain('shortcut: null');
});

it('binds the query to a livewire property in server mode', function () {
    expect(renderCommand('search-model="query" search-debounce="400ms"'))
        ->toContain('server: true')
        ->toContain('wire:model.live.debounce.400ms="query"');
});

it('renders groups and items of every kind', function () {
    $html = renderCommand('', <<<'BLADE'
        <x-avian::command.group label="Pages">
            <x-avian::command.item href="/orders" navigate icon="fas fa-box" hint="Page" keywords="sales invoices" value="orders">Orders</x-avian::command.item>
            <x-avian::command.item modal="create-order" :keywords="['add', 'create']">New order</x-avian::command.item>
            <x-avian::command.item wire:click="logout">Sign out</x-avian::command.item>
        </x-avian::command.group>
        BLADE);

    expect($html)->toContain('class="aui-command-group" role="group" data-aui-command-group')
        ->toContain('aria-labelledby="aui-command-group-')
        ->toContain('>Pages</div>')
        ->toContain('href="/orders"')
        ->toContain('wire:navigate')
        ->toContain('role="option"')
        ->toContain('aria-selected="false"')
        ->toContain('data-aui-command-item')
        ->toContain('data-keywords="sales invoices"')
        ->toContain('data-value="orders"')
        ->toContain('<i class="aui-command-icon fas fa-box" aria-hidden="true"></i>')
        ->toContain('<span class="aui-command-hint">Page</span>')
        ->toContain('data-modal="create-order"')
        ->toContain('data-keywords="add create"')
        ->toContain('wire:click="logout"')
        ->toContain('x-on:click.capture="chosen($el)"')
        ->and(substr_count($html, 'type="button"'))->toBe(2);
});

it('only adds wire:navigate to links', function () {
    expect(Blade::render('<x-avian::command.item navigate>Run</x-avian::command.item>'))
        ->not->toContain('wire:navigate')
        ->toContain('<button');
});

it('escapes item text, hints and group labels', function () {
    $html = Blade::render('<x-avian::command.group label="<i>G</i>"><x-avian::command.item hint="<b>H</b>">{{ $text }}</x-avian::command.item></x-avian::command.group>', ['text' => '<script>x</script>']);

    expect($html)->toContain('&lt;i&gt;G&lt;/i&gt;')
        ->toContain('&lt;b&gt;H&lt;/b&gt;')
        ->toContain('&lt;script&gt;')
        ->not->toContain('<script>');
});

it('keeps its own click handler next to an item\'s x-on:click', function () {
    expect(Blade::render('<x-avian::command.item x-on:click="doThing()">Run</x-avian::command.item>'))
        ->toContain('x-on:click="doThing()"')
        ->toContain('x-on:click.capture="chosen($el)"');
});
