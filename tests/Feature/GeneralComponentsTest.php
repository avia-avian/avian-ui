<?php

declare(strict_types=1);

use Illuminate\Pagination\Cursor;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Blade;

it('renders a button with its variant and size', function () {
    $html = Blade::render('<x-avian::button variant="danger" size="sm">Delete</x-avian::button>');

    expect($html)->toContain('aui-btn aui-btn-solid aui-tone-danger aui-btn-sm')
        ->toContain('type="button"')
        ->toContain('Delete');
});

dataset('colors', ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'neutral', 'dark', 'purple', 'indigo', 'teal', 'orange', 'pink']);

it('renders every colour as a solid, outline and ghost button', function (string $color) {
    expect(Blade::render("<x-avian::button variant=\"{$color}\">Go</x-avian::button>"))
        ->toContain("aui-btn aui-btn-solid aui-tone-{$color}");

    expect(Blade::render("<x-avian::button variant=\"outline\" color=\"{$color}\">Go</x-avian::button>"))
        ->toContain("aui-btn aui-btn-outline aui-tone-{$color}");

    expect(Blade::render("<x-avian::button variant=\"ghost\" color=\"{$color}\">Go</x-avian::button>"))
        ->toContain("aui-btn aui-btn-ghost aui-tone-{$color}");
})->with('colors');

it('falls back to primary outline and secondary ghost buttons without a colour', function () {
    expect(Blade::render('<x-avian::button variant="outline">Go</x-avian::button>'))
        ->toContain('aui-btn aui-btn-outline aui-tone-primary');

    expect(Blade::render('<x-avian::button variant="ghost">Go</x-avian::button>'))
        ->toContain('aui-btn aui-btn-ghost aui-tone-secondary');

    expect(Blade::render('<x-avian::button variant="light" color="danger">Go</x-avian::button>'))
        ->toContain('aui-btn aui-btn-light')
        ->not->toContain('aui-tone-');
});

it('applies every colour to every coloured component', function (string $color) {
    expect(Blade::render("<x-avian::badge variant=\"{$color}\">New</x-avian::badge>"))->toContain("aui-badge aui-tone-{$color}")
        ->and(Blade::render("<x-avian::alert variant=\"{$color}\">Hi</x-avian::alert>"))->toContain("aui-alert aui-tone-{$color}")
        ->and(Blade::render("<x-avian::progress :value=\"50\" variant=\"{$color}\" />"))->toContain("aui-progress-bar aui-tone-{$color}")
        ->and(Blade::render("<x-avian::timeline.item title=\"Shipped\" variant=\"{$color}\" />"))->toContain("aui-timeline-item aui-tone-{$color}")
        ->and(Blade::render("<x-avian::stat label=\"Revenue\" value=\"1\" icon=\"fas fa-wallet\" color=\"{$color}\" />"))->toContain("aui-stat-icon aui-tone-{$color}");
})->with('colors');

it('ships a tone for every colour in the stylesheet', function (string $color) {
    $css = file_get_contents(__DIR__.'/../../public/css/avian-ui.css');

    preg_match('/\.aui-tone-'.$color.' \{(.*?)\}/s', $css, $tone);

    expect($tone)->not->toBeEmpty()
        ->and($tone[1])->toContain('--aui-tone:')
        ->toContain('--aui-tone-hover:')
        ->toContain('--aui-tone-soft:')
        ->toContain('--aui-tone-strong:')
        ->toContain('--aui-tone-border:');
})->with('colors');

it('renders a button as a link when given an href', function () {
    $html = Blade::render('<x-avian::button href="/reports" icon="fas fa-file">Reports</x-avian::button>');

    expect($html)->toContain('<a')
        ->toContain('href="/reports"')
        ->toContain('<i class="fas fa-file"')
        ->not->toContain('type="button"');
});

it('adds wire:navigate to a link button only when asked', function () {
    expect(Blade::render('<x-avian::button href="/reports" navigate>Reports</x-avian::button>'))
        ->toContain('wire:navigate');

    expect(Blade::render('<x-avian::button href="/reports">Reports</x-avian::button>'))
        ->not->toContain('wire:navigate');
});

it('never adds wire:navigate to a plain button', function () {
    expect(Blade::render('<x-avian::button navigate>Reports</x-avian::button>'))
        ->not->toContain('wire:navigate');
});

it('disables a loading button and swaps its icon for a spinner', function () {
    $html = Blade::render('<x-avian::button icon="fas fa-save" loading>Saving</x-avian::button>');

    expect($html)->toContain('aui-btn-loading')
        ->toContain('disabled="disabled"')
        ->toContain('aui-spinner')
        ->not->toContain('fas fa-save');
});

it('merges livewire and alpine attributes onto the button', function () {
    expect(Blade::render('<x-avian::button wire:click="save" x-on:click="ping()">Save</x-avian::button>'))
        ->toContain('wire:click="save"')
        ->toContain('x-on:click="ping()"');
});

it('gives a modal trigger button its own alpine scope', function () {
    $html = Blade::render('<x-avian::button modal="create-user">New user</x-avian::button>');

    expect($html)->toContain('x-data="{}"')
        ->toContain('x-on:click="$dispatch(&#039;aui-modal-open&#039;')
        ->toContain('create-user');
});

it('uses the label of an icon-only button as its tooltip unless a title is given', function () {
    expect(Blade::render('<x-avian::button icon="fas fa-pen" icon-only label="Edit" />'))
        ->toContain('aria-label="Edit"')
        ->toContain('title="Edit"');

    expect(Blade::render('<x-avian::button icon="fas fa-pen" icon-only label="Edit" title="Edit order" />'))
        ->toContain('title="Edit order"')
        ->not->toContain('title="Edit"');
});

it('renders rounded, active and extra small buttons', function () {
    expect(Blade::render('<x-avian::button variant="light" size="xs" rounded active>List</x-avian::button>'))
        ->toContain('aui-btn aui-btn-light aui-btn-xs aui-btn-rounded aui-btn-active');
});

it('renders a button group with an accessible label', function () {
    $html = Blade::render('<x-avian::button-group attached label="View"><x-avian::button>List</x-avian::button></x-avian::button-group>');

    expect($html)->toContain('class="aui-btn-group aui-btn-group-attached"')
        ->toContain('role="group"')
        ->toContain('aria-label="View"')
        ->toContain('List');
});

it('renders a plain button group without the attached or vertical modifiers', function () {
    expect(Blade::render('<x-avian::button-group>Buttons</x-avian::button-group>'))
        ->toContain('class="aui-btn-group"')
        ->not->toContain('aria-label');

    expect(Blade::render('<x-avian::button-group attached vertical>Buttons</x-avian::button-group>'))
        ->toContain('aui-btn-group aui-btn-group-attached aui-btn-group-vertical');
});

it('renders a toolbar with its end slot pushed to the right', function () {
    $html = Blade::render('<x-avian::toolbar label="Orders">Filters<x-slot:end>Actions</x-slot:end></x-avian::toolbar>');

    expect($html)->toContain('class="aui-toolbar"')
        ->toContain('role="toolbar"')
        ->toContain('aria-label="Orders"')
        ->toContain('aui-toolbar-start')
        ->toContain('<div class="aui-toolbar-end">');
});

it('leaves out the toolbar end area when no end slot is given', function () {
    expect(Blade::render('<x-avian::toolbar>Filters</x-avian::toolbar>'))
        ->not->toContain('aui-toolbar-end');
});

it('lets a caller override the modal trigger handler', function () {
    expect(Blade::render('<x-avian::button modal="x" x-on:click="custom()">Go</x-avian::button>'))
        ->toContain('x-on:click="custom()"')
        ->not->toContain('aui-modal-open');
});

it('renders an icon-only button with an accessible label and no visible text', function () {
    $html = Blade::render('<x-avian::button icon="fas fa-pen" icon-only label="Edit">Edit</x-avian::button>');

    expect($html)->toContain('aui-btn-icon')
        ->toContain('aria-label="Edit"')
        ->toContain('<i class="fas fa-pen"')
        ->not->toContain('>Edit<');
});

it('keeps a regular button free of icon-only markup', function () {
    $html = Blade::render('<x-avian::button icon="fas fa-pen">Edit</x-avian::button>');

    expect($html)->not->toContain('aui-btn-icon')
        ->not->toContain('aria-label')
        ->toContain('Edit');
});

it('renders a card with a title, actions and footer slots', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::card title="Team" subtitle="Active members">
            <x-slot:actions><button>Add</button></x-slot:actions>
            Body content
            <x-slot:footer>Footer content</x-slot:footer>
        </x-avian::card>
    BLADE);

    expect($html)->toContain('class="aui-card"')
        ->toContain('<h2 class="aui-card-title">Team</h2>')
        ->toContain('<p class="aui-card-subtitle">Active members</p>')
        ->toContain('class="aui-card-actions"')
        ->toContain('class="aui-card-body"')
        ->toContain('Body content')
        ->toContain('class="aui-card-footer"');
});

it('renders a card without a header when nothing fills it', function () {
    expect(Blade::render('<x-avian::card>Plain</x-avian::card>'))
        ->toContain('class="aui-card-body"')
        ->not->toContain('aui-card-header');
});

it('renders a collapsible card with a toggle and a collapsible content area', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::card title="Filters" collapsible persist="filters">
            <x-slot:actions><button>Reset</button></x-slot:actions>
            Body content
            <x-slot:footer>Footer content</x-slot:footer>
        </x-avian::card>
    BLADE);

    expect($html)->toContain('x-data="auiCard({ collapsed: false, persist: \'filters\' })"')
        ->toContain('class="aui-card aui-card-collapsible"')
        ->toContain('x-on:click="headerClick($event)"')
        ->toContain('<button>Reset</button>')
        ->toContain('class="aui-card-toggle"')
        ->toContain('aria-expanded="true"')
        ->toContain('aria-label="Filters"')
        ->toContain('x-show="! collapsed"')
        ->not->toContain('style="display: none"');

    expect(strpos($html, 'class="aui-card-footer"'))->toBeGreaterThan(strpos($html, 'class="aui-card-content"'));
});

it('renders a collapsed card hidden on first paint', function () {
    $html = Blade::render('<x-avian::card collapsible collapsed>Body</x-avian::card>');

    expect($html)->toContain('auiCard({ collapsed: true, persist: null })')
        ->toContain('aui-card aui-card-collapsible is-collapsed')
        ->toContain('class="aui-card-header"')
        ->toContain('aria-expanded="false"')
        ->toContain('aria-label="Toggle content"')
        ->toContain('style="display: none"');
});

it('ignores collapsed on a card that is not collapsible', function () {
    $html = Blade::render('<x-avian::card title="Team" collapsed>Body</x-avian::card>');

    expect($html)->toContain('class="aui-card"')
        ->not->toContain('auiCard')
        ->not->toContain('aui-card-toggle')
        ->not->toContain('aui-card-content')
        ->not->toContain('display: none');
});

it('renders a badge with a variant', function () {
    expect(Blade::render('<x-avian::badge variant="success" dot>Complete</x-avian::badge>'))
        ->toContain('aui-badge aui-tone-success aui-badge-dot')
        ->toContain('Complete');
});

it('renders an alert with its default icon', function () {
    $html = Blade::render('<x-avian::alert variant="danger" title="Failed">Something broke.</x-avian::alert>');

    expect($html)->toContain('aui-alert aui-tone-danger')
        ->toContain('role="alert"')
        ->toContain('fa-circle-exclamation')
        ->toContain('<p class="aui-alert-title">Failed</p>')
        ->toContain('Something broke.');
});

it('wires a dismissible alert to alpine', function () {
    $html = Blade::render('<x-avian::alert variant="info" dismissible>Heads up.</x-avian::alert>');

    expect($html)->toContain('x-data="auiDismiss()"')
        ->toContain('x-show="visible"')
        ->toContain('x-on:click="dismiss()"');
});

it('renders a table with headers and rows', function () {
    $html = Blade::render('<x-avian::table :headers="[\'Name\', \'Role\']"><tr><td>Ada</td></tr></x-avian::table>');

    expect($html)->toContain('class="aui-table-wrap"')
        ->toContain('aui-table aui-table-hover')
        ->toContain('<th>Name</th>')
        ->toContain('<th>Role</th>')
        ->toContain('<td>Ada</td>');
});

it('renders an empty state spanning every column when a table has no rows', function () {
    $html = Blade::render('<x-avian::table :headers="[\'Name\', \'Role\', \'\']">@foreach ([] as $row)<tr><td>{{ $row }}</td></tr>@endforeach</x-avian::table>');

    expect($html)->toContain('class="aui-table-empty"')
        ->toContain('colspan="3"')
        ->toContain('class="aui-empty"')
        ->toContain('No data found');
});

it('renders a custom empty title, text and icon on a table', function () {
    $html = Blade::render('<x-avian::table :headers="[\'Name\']" empty="No users yet" empty-text="Invite someone to get started." empty-icon="fas fa-users"></x-avian::table>');

    expect($html)->toContain('No users yet')
        ->toContain('Invite someone to get started.')
        ->toContain('fas fa-users')
        ->not->toContain('No data found');
});

it('renders an empty slot on a table in place of the default empty state', function () {
    $html = Blade::render('<x-avian::table :columns="4"><x-slot:empty><p>Nothing here</p></x-slot:empty></x-avian::table>');

    expect($html)->toContain('colspan="4"')
        ->toContain('<p>Nothing here</p>')
        ->not->toContain('class="aui-empty"');
});

it('skips the empty state on a table when it is disabled or has rows', function () {
    $disabled = Blade::render('<x-avian::table :headers="[\'Name\']" :empty="false"></x-avian::table>');
    $filled = Blade::render('<x-avian::table :headers="[\'Name\']"><tr><td>Ada</td></tr></x-avian::table>');

    expect($disabled)->not->toContain('aui-table-empty')
        ->and($filled)->not->toContain('aui-table-empty');
});

it('renders the empty state on a table when a row loop produces no rows', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::table :headers="['Name']">
            <!-- rows -->
            @foreach ($users as $user)
                <tr><td>{{ $user }}</td></tr>
            @endforeach
        </x-avian::table>
        BLADE, ['users' => []]);

    expect($html)->toContain('class="aui-table-empty"');
});

it('renders sortable headers as links that sort ascending by default', function () {
    $this->app->instance('request', Request::create('/users?page=3&search=ada'));

    $html = Blade::render('<x-avian::table :headers="[[\'label\' => \'Name\', \'sort\' => \'name\'], \'Role\']" />');

    expect($html)->toContain('aria-sort="none" class="aui-table-sortable"')
        ->toContain('class="aui-table-sort" href="http://localhost/users?search=ada&amp;sort=name&amp;direction=asc"')
        ->toContain('fas fa-sort"')
        ->toContain('<th>Role</th>');
});

it('marks the sorted column and flips its direction from the query string', function () {
    $this->app->instance('request', Request::create('/users?sort=name&direction=asc'));

    $html = Blade::render('<x-avian::table :headers="[[\'label\' => \'Name\', \'sort\' => \'name\'], [\'label\' => \'Email\', \'sort\' => \'email\']]" />');

    expect($html)->toContain('aria-sort="ascending" class="aui-table-sortable aui-table-sorted"')
        ->toContain('href="http://localhost/users?sort=name&amp;direction=desc"')
        ->toContain('fas fa-sort-up')
        ->toContain('href="http://localhost/users?sort=email&amp;direction=asc"');
});

it('reads the current sort from the table props over the query string', function () {
    $this->app->instance('request', Request::create('/users?sort=name&direction=asc'));

    $html = Blade::render('<x-avian::table sort-by="email" sort-direction="desc" :headers="[[\'label\' => \'Name\', \'sort\' => \'name\'], [\'label\' => \'Email\', \'sort\' => \'email\', \'align\' => \'right\']]" />');

    expect($html)->toContain('aria-sort="descending" class="aui-table-align-right aui-table-sortable aui-table-sorted"')
        ->toContain('fas fa-sort-down')
        ->toContain('href="http://localhost/users?sort=email&amp;direction=asc"');
});

it('drops the paginator page name from sort links', function () {
    $this->app->instance('request', Request::create('/users?members=4'));

    $paginator = new LengthAwarePaginator(
        items: ['Ada'],
        total: 20,
        perPage: 1,
        currentPage: 4,
        options: ['path' => '/users', 'pageName' => 'members'],
    );

    $html = Blade::render(
        '<x-avian::table :headers="[[\'label\' => \'Name\', \'sort\' => \'name\']]" :paginator="$paginator"><tr><td>Ada</td></tr></x-avian::table>',
        ['paginator' => $paginator],
    );

    expect($html)->toContain('href="http://localhost/users?sort=name&amp;direction=asc"');
});

it('renders table headings in a head slot that follow the table sort', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::table sort-by="name" sort-direction="desc" sort-param="order" direction-param="dir" :columns="2">
            <x-slot:head>
                <tr>
                    <x-avian::table.heading sort="name">Name</x-avian::table.heading>
                    <x-avian::table.heading align="right">Total</x-avian::table.heading>
                </tr>
            </x-slot:head>
        </x-avian::table>
    BLADE);

    expect($html)->toContain('aria-sort="descending"')
        ->toContain('?order=name&amp;dir=asc"')
        ->toContain('<th class="aui-table-align-right">');
});

it('renders sortable headers as livewire sortBy buttons', function () {
    $html = Blade::render('<x-avian::table :livewire="true" :headers="[[\'label\' => \'Name\', \'sort\' => \'name\']]" />');

    expect($html)->toContain('<button type="button" class="aui-table-sort" wire:click="sortBy(\'name\')">')
        ->not->toContain('href=');
});

it('renders pagination links for a length-aware paginator', function () {
    $paginator = new LengthAwarePaginator(
        items: ['Ada', 'Grace'],
        total: 42,
        perPage: 2,
        currentPage: 3,
        options: ['path' => '/users', 'pageName' => 'page'],
    );

    $html = Blade::render('<x-avian::pagination :paginator="$paginator" />', ['paginator' => $paginator]);

    expect($html)->toContain('class="aui-pagination"')
        ->toContain('Showing')
        ->toContain('<span class="aui-pagination-summary-strong">5</span>')
        ->toContain('<span class="aui-pagination-summary-strong">6</span>')
        ->toContain('<span class="aui-pagination-summary-strong">42</span>')
        ->toContain('href="/users?page=2"')
        ->toContain('href="/users?page=4"')
        ->toContain('aui-pagination-link-active" aria-current="page">3<');
});

it('disables the previous link on the first page and the next link on the last page', function () {
    $firstPage = new LengthAwarePaginator(
        items: ['Ada'],
        total: 25,
        perPage: 10,
        currentPage: 1,
        options: ['path' => '/users', 'pageName' => 'page'],
    );

    expect(Blade::render('<x-avian::pagination :paginator="$paginator" />', ['paginator' => $firstPage]))
        ->toContain('aui-pagination-link-disabled')
        ->toContain('rel="next"')
        ->not->toContain('rel="prev"');

    $lastPage = new LengthAwarePaginator(
        items: ['Ada'],
        total: 25,
        perPage: 10,
        currentPage: 3,
        options: ['path' => '/users', 'pageName' => 'page'],
    );

    expect(Blade::render('<x-avian::pagination :paginator="$paginator" />', ['paginator' => $lastPage]))
        ->toContain('aui-pagination-link-disabled')
        ->toContain('rel="prev"')
        ->not->toContain('rel="next"');
});

it('renders livewire gotoPage buttons instead of links in livewire mode', function () {
    $paginator = new LengthAwarePaginator(
        items: ['Ada', 'Grace'],
        total: 42,
        perPage: 2,
        currentPage: 3,
        options: ['path' => '/users', 'pageName' => 'usersPage'],
    );

    $html = Blade::render('<x-avian::pagination :paginator="$paginator" :livewire="true" />', ['paginator' => $paginator]);

    expect($html)->toContain('type="button" wire:click="gotoPage(2, &#039;usersPage&#039;)" class="aui-pagination-link" rel="prev"')
        ->toContain('wire:click="gotoPage(4, &#039;usersPage&#039;)" class="aui-pagination-link" rel="next"')
        ->toContain('wire:click="gotoPage(21, &#039;usersPage&#039;)"')
        ->not->toContain('href=');
});

it('renders nothing for a paginator without extra pages', function () {
    $paginator = new LengthAwarePaginator(
        items: ['Ada'],
        total: 1,
        perPage: 10,
        currentPage: 1,
    );

    expect(trim(Blade::render('<x-avian::pagination :paginator="$paginator" />', ['paginator' => $paginator])))->toBe('');
    expect(trim(Blade::render('<x-avian::pagination :paginator="null" />')))->toBe('');
});

it('renders a table with its paginator links underneath', function () {
    $paginator = new LengthAwarePaginator(
        items: ['Ada'],
        total: 20,
        perPage: 1,
        currentPage: 1,
        options: ['path' => '/users', 'pageName' => 'page'],
    );

    $html = Blade::render(
        '<x-avian::table :headers="[\'Name\']" :paginator="$paginator"><tr><td>Ada</td></tr></x-avian::table>',
        ['paginator' => $paginator],
    );

    expect($html)->toContain('class="aui-table-wrap"')
        ->toContain('class="aui-pagination"');
});

it('renders a modal wired to its alpine component', function () {
    $html = Blade::render('<x-avian::modal name="edit-user" title="Edit user" size="lg">Body</x-avian::modal>');

    expect($html)->toContain('x-data="auiModal(JSON.parse(')
        ->toContain('closeOnEscape')
        ->toContain('closeOnOverlay')
        ->toContain('x-on:keydown.escape.window="escape()"')
        ->toContain('x-on:click="overlay($event)"')
        ->toContain('aui-modal aui-modal-lg')
        ->toContain('data-modal="edit-user"')
        ->toContain('aria-modal="true"');
});

it('renders a dropdown with a default trigger and items', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::dropdown label="Actions" align="right">
            <x-avian::dropdown.item href="/edit" icon="fas fa-pen">Edit</x-avian::dropdown.item>
            <x-avian::dropdown.item danger>Delete</x-avian::dropdown.item>
        </x-avian::dropdown>
    BLADE);

    expect($html)->toContain("x-data=\"auiDropdown({ align: 'right' })\"")
        ->toContain('x-ref="trigger"')
        ->toContain('x-ref="menu"')
        ->toContain('x-on:click.outside="hide()"')
        ->toContain('aui-dropdown-menu aui-dropdown-menu-right')
        ->toContain('Actions')
        ->toContain('aui-dropdown-item')
        ->toContain('aui-dropdown-item-danger')
        ->toContain('href="/edit"');
});

it('renders tabs with the first tab active by default', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::tabs :tabs="['profile' => 'Profile', 'security' => 'Security']">
            <x-avian::tabs.panel name="profile">Profile panel</x-avian::tabs.panel>
        </x-avian::tabs>
    BLADE);

    expect($html)->toContain('auiTabs({ active: \'profile\' })')
        ->toContain('role="tablist"')
        ->toContain('x-on:click="select(\'security\')"')
        ->toContain('class="aui-tab-panel"')
        ->toContain('x-show="isActive(\'profile\')"');
});

it('defaults to the plain underlined tab list with no variant class', function () {
    expect(Blade::render('<x-avian::tabs :tabs="[\'a\' => \'A\']" />'))
        ->toContain('class="aui-tabs"')
        ->not->toContain('aui-tabs-line');
});

it('renders pill and segmented tab variants', function () {
    expect(Blade::render('<x-avian::tabs :tabs="[\'a\' => \'A\']" variant="pill" />'))
        ->toContain('class="aui-tabs aui-tabs-pill"');

    expect(Blade::render('<x-avian::tabs :tabs="[\'a\' => \'A\']" variant="segmented" />'))
        ->toContain('class="aui-tabs aui-tabs-segmented"');
});

it('renders a progress bar clamped to a percentage', function () {
    expect(Blade::render('<x-avian::progress :value="30" :max="60" label="Upload" show-value />'))
        ->toContain('width: 50%')
        ->toContain('aria-valuenow="50"')
        ->toContain('Upload')
        ->toContain('50%');
});

it('clamps progress values outside the range', function () {
    expect(Blade::render('<x-avian::progress :value="900" />'))->toContain('width: 100%');
    expect(Blade::render('<x-avian::progress :value="-5" />'))->toContain('width: 0%');
});

it('renders an avatar with derived initials', function () {
    expect(Blade::render('<x-avian::avatar name="Ada Lovelace" size="lg" />'))
        ->toContain('aui-avatar aui-avatar-lg')
        ->toContain('AL');
});

it('renders an avatar image when a source is given', function () {
    expect(Blade::render('<x-avian::avatar src="/me.png" name="Ada" />'))
        ->toContain('<img src="/me.png" alt="Ada">');
});

it('renders an empty state', function () {
    expect(Blade::render('<x-avian::empty title="No records" text="Try another filter." />'))
        ->toContain('class="aui-empty"')
        ->toContain('No records')
        ->toContain('Try another filter.');
});

it('renders a page header with action slot', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::page-header title="Dashboard" subtitle="Today">
            <x-slot:actions><span>Export</span></x-slot:actions>
        </x-avian::page-header>
    BLADE);

    expect($html)->toContain('<h1 class="aui-page-title">Dashboard</h1>')
        ->toContain('<p class="aui-page-subtitle">Today</p>')
        ->toContain('class="aui-page-actions"')
        ->toContain('Export');
});

it('renders a spinner', function () {
    expect(Blade::render('<x-avian::spinner size="lg" />'))->toContain('aui-spinner aui-spinner-lg');
});

it('renders the stylesheet tags with a cache busted url', function () {
    $html = Blade::render('<x-avian::styles />');

    expect($html)->toContain('avian-ui/css/avian-ui.css?id=')
        ->toContain('avian-ui/css/avian-ui-themes.css?id=');
});

it('omits the theme stylesheet when themes are turned off', function () {
    config()->set('avian-ui.assets.themes', false);

    expect(Blade::render('<x-avian::styles />'))->not->toContain('avian-ui-themes.css');
});

it('renders the script tag deferred', function () {
    expect(Blade::render('<x-avian::scripts />'))
        ->toContain('avian-ui/js/avian-ui.js?id=')
        ->toContain('defer');
});

it('holds a button click back behind the confirm dialog when asked', function () {
    expect(Blade::render('<x-avian::button wire:click="delete(1)" confirm="Delete this order?" data-aui-confirm-title="Delete order">Delete</x-avian::button>'))
        ->toContain('data-aui-confirm="Delete this order?"')
        ->toContain('data-aui-confirm-title="Delete order"')
        ->toContain('wire:click="delete(1)"');

    expect(Blade::render('<x-avian::button>Save</x-avian::button>'))
        ->not->toContain('data-aui-confirm');
});

it('renders the shared confirm dialog with translated defaults', function () {
    $html = Blade::render('<x-avian::confirm />');

    expect($html)->toContain('x-data="auiConfirm(')
        ->toContain('Are you sure?')
        ->toContain('confirmText')
        ->toContain('Confirm')
        ->toContain('Cancel')
        ->toContain('role="alertdialog"')
        ->toContain('x-ref="cancel"');

    expect(Blade::render('<x-avian::confirm title="Really?" confirm-text="Yes, delete" variant="warning" />'))
        ->toContain('Really?')
        ->toContain('Yes, delete')
        ->toContain('warning');
});

it('renders the confirm dialog with a loading state on the confirm button', function () {
    $html = Blade::render('<x-avian::confirm />');

    expect($html)->toContain('x-bind:disabled="busy"')
        ->toContain("busy ? 'aui-btn-loading' : ''")
        ->toContain('<span class="aui-spinner aui-spinner-sm" x-show="busy" aria-hidden="true"></span>')
        ->toContain('loading\u0022:false');

    expect(Blade::render('<x-avian::confirm loading />'))
        ->toContain('loading\u0022:true');
});

it('renders breadcrumbs from label and url pairs with the last one current', function () {
    $html = Blade::render('<x-avian::breadcrumbs :items="$items" navigate />', [
        'items' => ['Dashboard' => '/dashboard', 'Orders' => '/orders', 'ORD-1' => null],
    ]);

    expect($html)->toContain('aria-label="Breadcrumb"')
        ->toContain('class="aui-breadcrumbs"')
        ->toContain('href="/dashboard"')
        ->toContain('href="/orders"')
        ->toContain('wire:navigate')
        ->toContain('aria-current="page"')
        ->toMatch('/aria-current="page">\s*ORD-1/');
});

it('renders breadcrumbs from item arrays and never links the current page', function () {
    $html = Blade::render('<x-avian::breadcrumbs :items="$items" />', [
        'items' => [
            ['label' => 'Home', 'href' => '/', 'icon' => 'fas fa-house'],
            ['label' => 'Settings', 'href' => '/settings'],
        ],
    ]);

    expect($html)->toContain('<i class="fas fa-house"')
        ->toContain('href="/"')
        ->not->toContain('href="/settings"')
        ->not->toContain('wire:navigate');
});

it('renders breadcrumb items written by hand', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::breadcrumbs>
            <x-avian::breadcrumbs.item href="/">Home</x-avian::breadcrumbs.item>
            <x-avian::breadcrumbs.item>Profile</x-avian::breadcrumbs.item>
        </x-avian::breadcrumbs>
    BLADE);

    expect($html)->toContain('class="aui-breadcrumbs-link"')
        ->toContain('aui-breadcrumbs-item is-current');
});

it('renders an accordion with items wired to its alpine component', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::accordion multiple flush>
            <x-avian::accordion.item title="Shipping" subtitle="Where it goes" icon="fas fa-truck" open>Address</x-avian::accordion.item>
            <x-avian::accordion.item title="Billing" name="billing">Invoice</x-avian::accordion.item>
        </x-avian::accordion>
    BLADE);

    expect($html)->toContain('x-data="auiAccordion({ multiple: true })"')
        ->toContain('aui-accordion aui-accordion-flush')
        ->toContain('aui-accordion-item is-open')
        ->toContain('Where it goes')
        ->toContain('<i class="aui-accordion-icon fas fa-truck"')
        ->toContain("x-data=\"{ key: 'billing' ?? \$id('aui-accordion') }\"")
        ->toContain('x-on:click="toggle(key)"')
        ->toContain('style="display: none"');
});

it('renders a drawer driven by the modal alpine component', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::drawer name="filters" title="Filters" subtitle="Narrow the list" position="left" size="lg">
            Body
            <x-slot:footer>Footer</x-slot:footer>
        </x-avian::drawer>
    BLADE);

    expect($html)->toContain('x-data="auiModal(')
        ->toContain('data-modal="filters"')
        ->toContain('aui-drawer aui-drawer-left aui-drawer-lg')
        ->toContain('Narrow the list')
        ->toContain('<div class="aui-drawer-footer">Footer</div>')
        ->toContain('aria-label="Close"');

    expect(Blade::render('<x-avian::drawer name="x">Body</x-avian::drawer>'))
        ->toContain('aui-drawer aui-drawer-right aui-drawer-md');
});

it('renders a stat with its trend read from the sign of the change', function () {
    $up = Blade::render('<x-avian::stat label="Revenue" value="Rp 12 jt" change="+12.5%" description="vs last month" icon="fas fa-wallet" />');
    $down = Blade::render('<x-avian::stat label="Orders" value="320" change="-4%" />');
    $flat = Blade::render('<x-avian::stat label="Returns" value="3" change="0%" />');

    expect($up)->toContain('aui-stat-change aui-stat-change-good')
        ->toContain('fa-arrow-trend-up')
        ->toContain('vs last month')
        ->toContain('aui-stat-icon aui-tone-primary')
        ->and($down)->toContain('aui-stat-change-bad')
        ->toContain('fa-arrow-trend-down')
        ->not->toContain('aui-stat-icon')
        ->and($flat)->toContain('aui-stat-change-flat');
});

it('inverts the stat tone and renders it as a link when asked', function () {
    $html = Blade::render('<x-avian::stat label="Costs" value="Rp 4 jt" change="-8%" invert href="/costs" navigate />');

    expect($html)->toContain('<a')
        ->toContain('href="/costs"')
        ->toContain('wire:navigate')
        ->toContain('aui-stat aui-stat-link')
        ->toContain('aui-stat-change-good');

    expect(Blade::render('<x-avian::stat label="Users" value="12" />'))
        ->not->toContain('aui-stat-meta');
});

it('renders plain, labelled and vertical dividers', function () {
    expect(Blade::render('<x-avian::divider />'))->toContain('<hr class="aui-divider"');

    expect(Blade::render('<x-avian::divider label="or" />'))
        ->toContain('aui-divider aui-divider-labelled')
        ->toContain('<span class="aui-divider-label">or</span>');

    expect(Blade::render('<x-avian::divider align="left">Shipping</x-avian::divider>'))
        ->toContain('aui-divider-left')
        ->toContain('Shipping');

    expect(Blade::render('<x-avian::divider vertical />'))
        ->toContain('aui-divider-vertical')
        ->toContain('aria-orientation="vertical"');
});

it('renders the toast stack with its position and defaults', function () {
    $html = Blade::render('<x-avian::toasts position="bottom-left" :duration="3000" :max="3" />');

    expect($html)->toContain('aui-toasts aui-toasts-bottom-left')
        ->toContain('x-data="auiToasts(')
        ->toContain('role="region"')
        ->toContain('aria-label="Notifications"')
        ->toContain('\u0022duration\u0022:3000')
        ->toContain('\u0022max\u0022:3');
});

it('hands flashed session messages to the toast stack', function () {
    session()->flash('success', 'Order saved.');

    expect(Blade::render('<x-avian::toasts />'))->toContain('Order saved.');
});

it('leaves flashed messages alone when the toast stack is told to', function () {
    session()->flash('success', 'Order saved.');

    expect(Blade::render('<x-avian::toasts :flash="false" />'))->not->toContain('Order saved.');
});

it('takes a disabled or loading link button out of navigation and the tab order', function (string $state) {
    $html = Blade::render("<x-avian::button href=\"/export\" navigate {$state} confirm=\"Sure?\">Export</x-avian::button>");

    expect($html)->toContain('<a')
        ->toContain('aria-disabled="true"')
        ->toContain('tabindex="-1"')
        ->not->toContain('href=')
        ->not->toContain('wire:navigate');
})->with(['disabled', 'loading']);

it('renders previous and next links for a cursor paginator', function () {
    $paginator = new CursorPaginator(
        items: [['id' => 5], ['id' => 6], ['id' => 7]],
        perPage: 2,
        cursor: new Cursor(['id' => 4]),
        options: ['path' => '/orders', 'cursorName' => 'cursor', 'parameters' => ['id']],
    );

    $html = Blade::render('<x-avian::pagination :paginator="$paginator" />', ['paginator' => $paginator]);

    expect($html)->toContain('rel="prev"')
        ->toContain('rel="next"')
        ->toContain('href="/orders?cursor=')
        ->not->toContain('Showing')
        ->not->toContain('aria-current');
});

it('renders livewire setPage buttons for a cursor paginator', function () {
    $paginator = new CursorPaginator(
        items: [['id' => 5], ['id' => 6], ['id' => 7]],
        perPage: 2,
        cursor: null,
        options: ['path' => '/orders', 'cursorName' => 'ordersCursor', 'parameters' => ['id']],
    );

    $html = Blade::render('<x-avian::pagination :paginator="$paginator" :livewire="true" />', ['paginator' => $paginator]);

    expect($html)->toContain('wire:click="setPage(&#039;'.$paginator->nextCursor()->encode().'&#039;, &#039;ordersCursor&#039;)"')
        ->not->toContain('rel="prev"')
        ->not->toContain('href=');
});

it('lets escape inside a dropdown close only the dropdown', function () {
    expect(Blade::render('<x-avian::dropdown label="Actions"><x-avian::dropdown.item>Edit</x-avian::dropdown.item></x-avian::dropdown>'))
        ->toContain('x-on:keydown.escape="escape($event)"');
});
