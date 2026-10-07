<?php

declare(strict_types=1);

namespace AvianUi\AvianUi;

use BackedEnum;
use DateTimeInterface;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;
use Illuminate\View\ComponentAttributeBag;
use UnitEnum;

class AvianUi
{
    /**
     * The cached asset version identifier.
     */
    protected ?string $version = null;

    /**
     * Get the absolute URL for the component stylesheet.
     */
    public function styleUrl(): string
    {
        return $this->assetUrl('css/avian-ui.css');
    }

    /**
     * Get the absolute URL for the optional theme stylesheet.
     */
    public function themeStyleUrl(): string
    {
        return $this->assetUrl('css/avian-ui-themes.css');
    }

    /**
     * Get the absolute URL for the Alpine behaviour script.
     */
    public function scriptUrl(): string
    {
        return $this->assetUrl('js/avian-ui.js');
    }

    /**
     * Build the absolute URL for a packaged asset.
     */
    public function assetUrl(string $file): string
    {
        $base = $this->assetBase();

        $url = Str::startsWith($base, ['http://', 'https://', '//'])
            ? rtrim($base, '/').'/'.$file
            : asset(trim($base, '/').'/'.$file);

        return $url.'?id='.$this->version();
    }

    /**
     * Get the base path or URL the assets are served from.
     */
    public function assetBase(): string
    {
        $url = config('avian-ui.assets.url');

        if (is_string($url) && $url !== '') {
            return $url;
        }

        if (config('avian-ui.assets.route', true) === false) {
            return 'vendor/avian-ui';
        }

        $path = config('avian-ui.assets.path', 'avian-ui');

        return is_string($path) && $path !== '' ? $path : 'avian-ui';
    }

    /**
     * Get the directory the packaged assets live in.
     */
    public function assetDirectory(): string
    {
        return dirname(__DIR__).'/public';
    }

    /**
     * Resolve a packaged asset to an absolute file path.
     */
    public function assetPath(string $file): ?string
    {
        if (! (bool) preg_match('#^(css/[A-Za-z0-9._-]+\.css|js/[A-Za-z0-9._-]+\.js)$#', $file)) {
            return null;
        }

        $path = $this->assetDirectory().'/'.$file;

        return is_file($path) ? $path : null;
    }

    /**
     * Get a version identifier that changes whenever the assets change.
     */
    public function version(): string
    {
        if ($this->version !== null) {
            return $this->version;
        }

        $stamp = '';

        foreach (['css/avian-ui.css', 'css/avian-ui-themes.css', 'js/avian-ui.js'] as $file) {
            $path = $this->assetPath($file);

            $stamp .= $path === null ? '0' : (string) filemtime($path);
        }

        return $this->version = substr(hash('xxh128', $stamp), 0, 12);
    }

    /**
     * Get the flashed old input for the given field name.
     */
    public function oldValue(?string $name): mixed
    {
        if ($name === null || $name === '') {
            return null;
        }

        $session = app()->bound('session.store') ? app('session.store') : null;

        if (! $session instanceof Store) {
            return null;
        }

        $key = $this->fieldKey($name);

        return $session->hasOldInput($key) ? $session->getOldInput($key) : null;
    }

    /**
     * Get the flashed old input for the given field, or the default when the
     * field is missing from the old input.
     *
     * Unlike oldValue(), a flashed value always wins over the default, so a
     * user's edit to a field that also has a saved value survives a failed
     * validation, including a field the user cleared.
     */
    public function old(?string $name, mixed $default = null): mixed
    {
        $session = $this->session();

        if ($name === null || $name === '' || $session === null) {
            return $default;
        }

        $old = $session->get('_old_input', []);
        $key = $this->fieldKey($name);

        return is_array($old) && Arr::has($old, $key) ? Arr::get($old, $key) : $default;
    }

    /**
     * Decide whether a checkbox, radio or switch should render checked.
     *
     * After a failed submit an unticked box is simply absent from the old
     * input, so any old input at all overrides the default.
     */
    public function oldChecked(?string $name, mixed $value, bool $default): bool
    {
        $session = $this->session();

        if ($name === null || $name === '' || $session === null || ! $session->hasOldInput()) {
            return $default;
        }

        $old = $session->getOldInput($this->fieldKey($name));
        $value = (string) $this->scalar($value);

        if (is_array($old)) {
            return in_array($value, array_map(fn (mixed $item): string => is_scalar($item) ? (string) $item : '', $old), true);
        }

        return is_scalar($old) && (string) $old === $value;
    }

    /**
     * Get the name a field is known by for ids and validation errors: its
     * `name`, or the property bound with `wire:model` when it has none.
     */
    public function fieldName(?string $name, ComponentAttributeBag $attributes): ?string
    {
        if ($name !== null && $name !== '') {
            return $name;
        }

        $model = $attributes->whereStartsWith('wire:model')->first();

        return is_string($model) && $model !== '' ? $model : null;
    }

    /**
     * Reduce an enum to the value it is stored as, leaving anything else as is.
     */
    public function scalar(mixed $value): mixed
    {
        return match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof UnitEnum => $value->name,
            default => $value,
        };
    }

    /**
     * Format a date for a flatpickr input written in flatpickr's format tokens.
     *
     * Strings pass through untouched; a list of dates (range or multiple
     * mode) is joined with the given separator.
     */
    public function formatDate(mixed $value, string $format, string $separator = ', '): mixed
    {
        if (is_array($value)) {
            $dates = array_map(fn (mixed $date): mixed => $this->formatDate($date, $format), $value);

            return implode($separator, array_filter($dates, fn (mixed $date): bool => is_string($date) && $date !== ''));
        }

        if (! $value instanceof DateTimeInterface) {
            return $value;
        }

        return $value->format(strtr($format, [
            'D' => 'D', 'l' => 'l', 'd' => 'd', 'j' => 'j', 'J' => 'jS', 'w' => 'w',
            'F' => 'F', 'm' => 'm', 'n' => 'n', 'M' => 'M',
            'U' => 'U', 'y' => 'y', 'Y' => 'Y', 'Z' => 'c',
            'H' => 'H', 'h' => 'g', 'G' => 'h', 'i' => 'i', 'S' => 's', 's' => 's', 'K' => 'A',
        ]));
    }

    /**
     * Get the first validation message for the given field name.
     */
    public function errorFor(?string $name, ?string $bag = null): ?string
    {
        if ($name === null || $name === '') {
            return null;
        }

        $errors = View::shared('errors');

        if (! $errors instanceof ViewErrorBag) {
            return null;
        }

        $bag = $bag === null || $bag === '' ? 'default' : $bag;

        if (! $errors->hasBag($bag)) {
            return null;
        }

        $message = $errors->getBag($bag)->first($this->fieldKey($name));

        return $message === '' ? null : $message;
    }

    /**
     * Get the toasts flashed to the session for the current request.
     *
     * Reads `success`, `error`, `warning` and `info` messages, plus a `toast`
     * entry holding a message, an options array or a list of options arrays.
     *
     * @return list<array{variant: string, message: string, title: string|null}>
     */
    public function flashedToasts(): array
    {
        $session = app()->bound('session.store') ? app('session.store') : null;

        if (! $session instanceof Store) {
            return [];
        }

        $toasts = [];

        foreach (['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info'] as $key => $variant) {
            $message = $session->get($key);

            if (is_string($message) && $message !== '') {
                $toasts[] = ['variant' => $variant, 'message' => $message, 'title' => null];
            }
        }

        $flashed = $session->get('toast');

        if (is_string($flashed) || (is_array($flashed) && ! array_is_list($flashed))) {
            $flashed = [$flashed];
        }

        foreach (is_array($flashed) ? $flashed : [] as $toast) {
            $toast = is_string($toast) ? ['message' => $toast] : $toast;

            if (! is_array($toast) || ! is_string($toast['message'] ?? null) || $toast['message'] === '') {
                continue;
            }

            $toasts[] = [
                'variant' => is_string($toast['variant'] ?? null) ? $toast['variant'] : 'success',
                'message' => $toast['message'],
                'title' => is_string($toast['title'] ?? null) ? $toast['title'] : null,
            ];
        }

        return $toasts;
    }

    /**
     * Get the session store, when the application has one.
     */
    protected function session(): ?Store
    {
        $session = app()->bound('session.store') ? app('session.store') : null;

        return $session instanceof Store ? $session : null;
    }

    /**
     * Normalize an HTML field name into a validation error key.
     */
    public function fieldKey(string $name): string
    {
        return trim(str_replace(['[]', '][', '[', ']'], ['', '.', '.', ''], $name), '.');
    }
}
