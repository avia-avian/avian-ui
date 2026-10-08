<?php

declare(strict_types=1);

use AvianUi\AvianUi\AvianUiServiceProvider;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/**
 * Reload the package routes after a config change, like an app booted with it.
 */
function reloadAvianUiRoutes(): void
{
    Route::setRoutes(new RouteCollection);

    (new AvianUiServiceProvider(app()))->boot();

    Route::getRoutes()->refreshNameLookups();
}

it('serves the documentation with every component on it', function () {
    $response = $this->get('/avian-ui');

    $response->assertOk();

    expect($response->getContent())->toContain('aui-page-title')
        ->toContain('aui-btn-solid aui-tone-primary')
        ->toContain('aui-badge aui-tone-success')
        ->toContain('aui-btn-group aui-btn-group-attached')
        ->toContain('aui-toolbar-end')
        ->toContain('aui-input-addon aui-input-addon-append')
        ->toContain('aui-form-grid')
        ->toContain('aui-table')
        ->toContain('auiModal(')
        ->toContain('auiTabs(')
        ->toContain('auiDropdown({')
        ->toContain('auiMultiSelect(')
        ->toContain('auiDatalist(')
        ->toContain('auiSearchableSelect(')
        ->toContain('auiFile(')
        ->toContain('flatpickr-input')
        ->toContain('data-fp-no-calendar="true"')
        ->toContain('auiConfirm(')
        ->toContain('auiToasts(')
        ->toContain('data-aui-confirm=')
        ->toContain('auiAccordion(')
        ->toContain('aui-drawer aui-drawer-right')
        ->toContain('aui-breadcrumbs')
        ->toContain('aui-stat-change-good')
        ->toContain('aui-divider-labelled')
        ->toContain('auiSlider(')
        ->toContain('auiTableRow(')
        ->toContain('aui-filter-chips')
        ->toContain('aui-timeline-marker-icon')
        ->toContain('avian-ui/css/avian-ui.css')
        ->toContain('avian-ui/js/avian-ui.js');
});

it('names the documentation route', function () {
    expect(route('avian-ui.docs', absolute: false))->toBe('/avian-ui');
});

it('serves the documentation from the configured path', function () {
    config()->set('avian-ui.docs.path', 'ui-docs');
    reloadAvianUiRoutes();

    $this->get('/ui-docs')->assertOk();
    $this->get('/avian-ui')->assertNotFound();
});

it('does not register the documentation when it is disabled', function () {
    config()->set('avian-ui.docs.enabled', false);
    reloadAvianUiRoutes();

    expect(Route::has('avian-ui.docs'))->toBeFalse();
    $this->get('/avian-ui')->assertNotFound();
});

/**
 * Every page in the documentation sidebar, as [group, view key, label].
 *
 * @return array<string, array{string, string, string}>
 */
function avianUiDocsPages(): array
{
    return [
        'getting-started' => ['General', 'getting-started', 'Getting started'],
        'components.button' => ['Actions & display', 'components.button', 'Button'],
        'components.button-group' => ['Actions & display', 'components.button-group', 'Button group & toolbar'],
        'components.badge' => ['Actions & display', 'components.badge', 'Badge'],
        'components.avatar' => ['Actions & display', 'components.avatar', 'Avatar'],
        'components.progress' => ['Actions & display', 'components.progress', 'Progress'],
        'components.spinner' => ['Actions & display', 'components.spinner', 'Spinner'],
        'components.skeleton' => ['Actions & display', 'components.skeleton', 'Skeleton'],
        'components.kbd' => ['Actions & display', 'components.kbd', 'Kbd'],
        'components.page-header' => ['Layout', 'components.page-header', 'Page header'],
        'components.breadcrumbs' => ['Layout', 'components.breadcrumbs', 'Breadcrumbs'],
        'components.card' => ['Layout', 'components.card', 'Card'],
        'components.stat' => ['Layout', 'components.stat', 'Stat'],
        'components.accordion' => ['Layout', 'components.accordion', 'Accordion'],
        'components.divider' => ['Layout', 'components.divider', 'Divider'],
        'forms.form' => ['Forms', 'forms.form', 'Form & layout'],
        'forms.field' => ['Forms', 'forms.field', 'Field, label & error'],
        'forms.input' => ['Forms', 'forms.input', 'Input'],
        'forms.textarea' => ['Forms', 'forms.textarea', 'Textarea'],
        'forms.select' => ['Forms', 'forms.select', 'Select'],
        'forms.searchable-select' => ['Forms', 'forms.searchable-select', 'Searchable select'],
        'forms.multi-select' => ['Forms', 'forms.multi-select', 'Multi select'],
        'forms.datepicker' => ['Forms', 'forms.datepicker', 'Datepicker'],
        'forms.date-range' => ['Forms', 'forms.date-range', 'Date range'],
        'forms.file' => ['Forms', 'forms.file', 'File'],
        'forms.checkbox' => ['Forms', 'forms.checkbox', 'Checkbox'],
        'forms.radio' => ['Forms', 'forms.radio', 'Radio'],
        'forms.switch' => ['Forms', 'forms.switch', 'Switch'],
        'forms.slider' => ['Forms', 'forms.slider', 'Slider'],
        'forms.filter-chip' => ['Forms', 'forms.filter-chip', 'Filter chip'],
        'forms.wizard' => ['Forms', 'forms.wizard', 'Wizard'],
        'components.table' => ['Data & navigation', 'components.table', 'Table'],
        'components.datalist' => ['Data & navigation', 'components.datalist', 'Datalist'],
        'components.timeline' => ['Data & navigation', 'components.timeline', 'Timeline'],
        'components.pagination' => ['Data & navigation', 'components.pagination', 'Pagination'],
        'components.tabs' => ['Data & navigation', 'components.tabs', 'Tabs'],
        'components.stepper' => ['Data & navigation', 'components.stepper', 'Stepper'],
        'components.dropdown' => ['Data & navigation', 'components.dropdown', 'Dropdown'],
        'components.modal' => ['Overlays & feedback', 'components.modal', 'Modal'],
        'components.drawer' => ['Overlays & feedback', 'components.drawer', 'Drawer'],
        'components.confirm' => ['Overlays & feedback', 'components.confirm', 'Confirm dialog'],
        'components.popover' => ['Overlays & feedback', 'components.popover', 'Popover'],
        'components.tooltip' => ['Overlays & feedback', 'components.tooltip', 'Tooltip'],
        'components.toast' => ['Overlays & feedback', 'components.toast', 'Toast'],
        'components.alert' => ['Overlays & feedback', 'components.alert', 'Alert'],
        'components.empty' => ['Overlays & feedback', 'components.empty', 'Empty state'],
    ];
}

dataset('docs pages', avianUiDocsPages());

it('lists every documentation page in the sidebar and renders its section', function () {
    $html = $this->get('/avian-ui')->assertOk()->getContent();

    foreach (avianUiDocsPages() as [$group, $key, $label]) {
        expect($html)->toContain("x-on:click=\"go({ key: '{$key}' })\"")
            ->toContain('data-search-key="'.$key.'"')
            ->toContain('data-search-page="'.e($label).'"')
            ->toContain('data-search-group="'.e($group).'"')
            ->toContain('<span class="aui-showcase-nav-group">'.e($group).'</span>');
    }
});

it('renders every documentation page on its own', function (string $group, string $key) {
    $html = view('avian-ui::docs.'.$key, ['group' => $group])->render();

    expect($html)->toContain('<h1 class="aui-showcase-title">')
        ->toContain('<p class="aui-showcase-eyebrow">'.e($group).'</p>');
})->with('docs pages');

it('renders one section per documentation page with previous and next links between them', function () {
    $html = $this->get('/avian-ui')->getContent();
    $pages = count(avianUiDocsPages());

    expect(substr_count($html, 'data-search-key="'))->toBe($pages)
        ->and(substr_count($html, '<small><i class="fas fa-arrow-left" aria-hidden="true"></i> Previous</small>'))->toBe($pages - 1)
        ->and(substr_count($html, '<button type="button" class="is-next"'))->toBe($pages - 1)
        ->and($html)->toContain('<title>Avian UI</title>')
        ->toContain("section: 'getting-started'");
});

it('renders the props table of a component on the documentation page', function () {
    expect($this->get('/avian-ui')->getContent())
        ->toContain('data-search-prop="show-value"')
        ->toContain('data-search-prop="time-format"')
        ->toContain('data-search-prop="navigate"');
});

it('serves the documentation through the web middleware by default', function () {
    expect(Route::getRoutes()->getByName('avian-ui.docs')?->gatherMiddleware())->toBe(['web']);
});

it('serves the documentation through the configured middleware', function () {
    config()->set('avian-ui.docs.middleware', ['web', 'can:viewAvianUiDocs']);
    reloadAvianUiRoutes();

    expect(Route::getRoutes()->getByName('avian-ui.docs')?->gatherMiddleware())->toBe(['web', 'can:viewAvianUiDocs']);

    Gate::define('viewAvianUiDocs', fn (?Authenticatable $user = null): bool => false);
    $this->get('/avian-ui')->assertForbidden();

    Gate::define('viewAvianUiDocs', fn (?Authenticatable $user = null): bool => true);
    $this->get('/avian-ui')->assertOk();
});

it('serves the documentation from a nested configured path', function () {
    config()->set('avian-ui.docs.path', 'admin/ui-docs');
    reloadAvianUiRoutes();

    $this->get('/admin/ui-docs')->assertOk();
    expect(route('avian-ui.docs', absolute: false))->toBe('/admin/ui-docs');
});

it('falls back to the default documentation path when the configured one is empty', function () {
    config()->set('avian-ui.docs.path', '');
    reloadAvianUiRoutes();

    $this->get('/avian-ui')->assertOk();
});

it('keeps serving the assets when the documentation is disabled', function () {
    config()->set('avian-ui.docs.enabled', false);
    reloadAvianUiRoutes();

    $this->get('/avian-ui/css/avian-ui.css')->assertOk();
});

it('serves the stylesheet and script the documentation links to', function () {
    $html = $this->get('/avian-ui')->getContent();

    preg_match('#href="http://localhost(/avian-ui/css/avian-ui\.css)\?id=[^"]+"#', $html, $style);
    preg_match('#src="http://localhost(/avian-ui/js/avian-ui\.js)\?id=[^"]+"#', $html, $script);

    expect($style)->toHaveCount(2)
        ->and($script)->toHaveCount(2);

    expect($this->get($style[1])->assertOk()->headers->get('Content-Type'))->toStartWith('text/css')
        ->and($this->get($script[1])->assertOk()->headers->get('Content-Type'))->toStartWith('text/javascript');
});

it('links the documentation to the assets on the configured asset path', function () {
    config()->set('avian-ui.assets.path', 'ui-assets');
    reloadAvianUiRoutes();

    expect($this->get('/avian-ui')->getContent())->toContain('/ui-assets/css/avian-ui.css?id=')
        ->toContain('/ui-assets/js/avian-ui.js?id=');

    $this->get('/ui-assets/css/avian-ui.css')->assertOk();
    $this->get('/avian-ui/css/avian-ui.css')->assertNotFound();
});

it('links the documentation to the published assets when the asset route is off', function () {
    config()->set('avian-ui.assets.route', false);
    reloadAvianUiRoutes();

    expect($this->get('/avian-ui')->getContent())->toContain('/vendor/avian-ui/css/avian-ui.css?id=')
        ->toContain('/vendor/avian-ui/js/avian-ui.js?id=');

    expect(Route::has('avian-ui.asset'))->toBeFalse();
});
