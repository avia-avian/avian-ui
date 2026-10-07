<?php

declare(strict_types=1);

use AvianUi\AvianUi\AvianUiServiceProvider;
use Illuminate\Routing\RouteCollection;
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
        ->toContain('aui-btn-primary')
        ->toContain('aui-badge-success')
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
