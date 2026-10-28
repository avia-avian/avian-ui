<?php

declare(strict_types=1);

namespace AvianUi\AvianUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AvianUiServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/avian-ui.php', 'avian-ui');

        $this->app->singleton(AvianUi::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('avian-ui.assets.route', true) !== false) {
            $this->loadRoutesFrom(__DIR__.'/../routes/avian-ui.php');
        }

        if (config('avian-ui.docs.enabled') ?? ! $this->app->isProduction()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/avian-ui-docs.php');
        }

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'avian-ui');

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'avian-ui');

        $this->registerBladeComponents();

        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/avian-ui.php' => config_path('avian-ui.php'),
        ], ['avian-ui', 'avian-ui-config']);

        $this->publishes([
            // Only the components: the docs pages are not meant to be overridden.
            __DIR__.'/../resources/views/components' => resource_path('views/vendor/avian-ui/components'),
        ], ['avian-ui', 'avian-ui-views']);

        $this->publishes([
            __DIR__.'/../lang' => $this->app->langPath('vendor/avian-ui'),
        ], ['avian-ui', 'avian-ui-lang']);

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/avian-ui'),
        ], ['avian-ui', 'avian-ui-assets']);
    }

    /**
     * Register the anonymous Blade component namespaces.
     */
    protected function registerBladeComponents(): void
    {
        Blade::anonymousComponentNamespace('avian-ui::components', 'avian-ui');

        $prefix = config('avian-ui.prefix', 'avian');

        if (is_string($prefix) && $prefix !== '' && $prefix !== 'avian-ui') {
            Blade::anonymousComponentNamespace('avian-ui::components', $prefix);
        }
    }
}
