<div align="center">
    <h1>Avian Ui</h1>
</div>

<p align="center">
    <a href="https://packagist.org/packages/avia-avian/avian-ui"><img src="https://img.shields.io/packagist/v/avia-avian/avian-ui.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/avia-avian/avian-ui"><img src="https://img.shields.io/packagist/php-v/avia-avian/avian-ui.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/avia-avian/avian-ui"><img src="https://badge.laravel.cloud/badge/avia-avian/avian-ui?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/avia-avian/avian-ui/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/avia-avian/avian-ui/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/avia-avian/avian-ui"><img src="https://img.shields.io/packagist/dt/avia-avian/avian-ui.svg?style=flat-square" alt="Total Downloads"></a>
</p>



## Installation

Requires PHP 8.3+ and Laravel 12 or 13. The package is not on Packagist, so
point Composer at the GitHub repository first — add this to the application's
`composer.json`:

```json
"repositories": [
    {
        "type": "vcs",
        "url": "https://github.com/avia-avian/avian-ui"
    }
]
```

Then require it (use a tag such as `^1.0` once one is released, or
`dev-master` to track the main branch):

```bash
composer require avia-avian/avian-ui:dev-master
```

To work on the package and an application side by side, use a path
repository instead (`"type": "path", "url": "../avian-ui"`) and require
`avia-avian/avian-ui:@dev`; Composer symlinks it, so edits show up
immediately.

That is all the setup there is. The service provider is auto-discovered, the
components are available straight away as `<x-avian::button>`, and the CSS
and JS are served by the package's own route — nothing to publish or build.
Add the asset tags to your layout (see [Include the assets](#1-include-the-assets))
and start using the components.

Open `/avian-ui` in your app to browse the documentation: every component with
a live demo, its props and copy-ready examples. See
[Previewing the library](#7-previewing-the-library) to move or turn it off.

Publishing is optional, only for customising. You may publish all of the
package's resources at once:

```bash
php artisan vendor:publish --tag="avian-ui"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="avian-ui-config"
```

### Publishing the Views

```bash
php artisan vendor:publish --tag="avian-ui-views"
```

### Publishing the Translations

```bash
php artisan vendor:publish --tag="avian-ui-lang"
```

### Publishing the Public Assets

```bash
php artisan vendor:publish --tag="avian-ui-assets"
```

## Usage

Avian UI is a Blade component library: a small design system (`aui-*` CSS
classes driven by CSS custom properties) plus anonymous Blade components. The
interactive components are built on Alpine, so they work in a plain Blade app
and in a Livewire app without any change.

### 1. Include the assets

```blade
<head>
    <x-avian::styles />
    <x-avian::scripts />
</head>
```

Alpine is **not** bundled. `<x-avian::scripts />` only registers components on
Alpine, so it has to run before Alpine starts:

- **With Livewire** nothing else is needed. Livewire loads Alpine at the end of
  the page, so the tag in `<head>` is already early enough.
- **Without Livewire** load your own Alpine tag *below* `<x-avian::scripts />`:

```blade
<x-avian::scripts />
<script defer src="/js/alpine.min.js"></script>
```

By default the CSS and JS are served straight from the package at
`/avian-ui/...`, so there is nothing to publish or build. To serve them from
`public/` instead, publish them and turn the route off:

```bash
php artisan vendor:publish --tag="avian-ui-assets"
```

```php
// config/avian-ui.php
'assets' => ['route' => false],
```

Or point `assets.url` at a bundler output (or any base URL) and take over
completely. Every URL is suffixed with a content version, so the browser
refetches the files whenever the package is updated.

To bundle the source through Vite instead:

```js
import '../../vendor/avia-avian/avian-ui/public/css/avian-ui.css';
import '../../vendor/avia-avian/avian-ui/public/js/avian-ui.js';
```

### 2. Theming

Every color is a CSS custom property, and each one falls back to the host
application's `--color-*` variable:

```css
--aui-primary: var(--color-primary, #008d4c);
```

So an application that already defines `--color-primary` is themed
automatically. If it does not, the package ships seven ready-made palettes
(`emerald-green`, `sky-blue`, `steel-blue`, `brown-gold`, `teal-maroon`,
`golden-yellow`, `navy-blue`) selected with a `data-theme` attribute:

```blade
<html data-theme="sky-blue">
```

Set `assets.themes` to `false` to skip that stylesheet, or override any token
in your own CSS:

```css
:root {
    --aui-primary: #7c3aed;
    --aui-radius-lg: 16px;
    --aui-font-sans: 'Plus Jakarta Sans', sans-serif;
}
```

Icons are passed through as class strings (`icon="fas fa-plus"`), so the
package does not depend on any particular icon set.

### 3. Form components

Form controls render a label, the control, a hint and the validation message
for their `name`, resolved from the standard error bag. They also repopulate
themselves from old input after a failed validation round trip: the old input
wins over a `value` you pass, so an edit form keeps the user's changes, and
checkboxes, radios and switches are re-ticked (or left unticked) to match what
was submitted. A Livewire control with only `wire:model` and no `name` uses the
bound property for its id and its error lookup.

```blade
<x-avian::form action="{{ route('users.store') }}" method="POST">
    <div class="aui-form-grid">
        <x-avian::input name="name" label="Full name" required />
        <x-avian::input name="email" type="email" label="Email" icon="fas fa-envelope" />

        <x-avian::select
            name="role"
            label="Role"
            placeholder="Choose a role"
            :options="['admin' => 'Administrator', 'viewer' => 'Viewer']"
        />

        <x-avian::searchable-select
            name="country"
            label="Country"
            placeholder="Choose a country"
            :options="['us' => 'United States', 'id' => 'Indonesia', 'jp' => 'Japan']"
        />

        <x-avian::input name="budget" label="Budget" prefix="Rp" hint="Rounded to thousands." />
        <x-avian::textarea class="aui-form-full" name="notes" label="Notes" rows="4" />
    </div>

    <x-avian::checkbox name="terms" label="I accept the terms" />
    <x-avian::switch name="active" label="Active" checked />
    <x-avian::radio name="plan" value="pro" label="Pro" inline />
    <x-avian::file name="attachment" label="Attachment" />

    <div class="aui-form-actions">
        <x-avian::button variant="light" type="reset">Cancel</x-avian::button>
        <x-avian::button type="submit" icon="fas fa-check">Save</x-avian::button>
    </div>
</x-avian::form>
```

| Prop | Applies to | Purpose |
| --- | --- | --- |
| `name` | all controls | Drives the id, the error lookup and old input (falls back to the `wire:model` property for the id and errors) |
| `label`, `hint` | all controls | Field chrome around the control |
| `error` | all controls | Override the resolved validation message |
| `error-bag` | all controls | Read from a named error bag |
| `field` | all controls | `false` renders the bare control with no wrapper |
| `size` | input, select | `sm` or `lg` |
| `prefix`, `suffix`, `icon` | input | Input group affixes |
| `prepend`, `append` (slots) | input | Buttons joined to the input's edges |
| `numeric` | input | Money-masked text field (see below) |
| `options`, `placeholder` | select, searchable-select, multi-select | Options as `value => label` |
| `search-placeholder`, `empty-text` | searchable-select, multi-select | Copy for the search box and empty state |
| `clearable`, `taggable`, `create-text` | searchable-select, multi-select | Reset button; accept free-typed values (`:term` in `create-text` is replaced) |
| `max` | multi-select | Cap how many values can be picked |
| `inline` | checkbox, radio | Lay several out on one line |
| `mode` (incl. `time`), `enable-time`, `time-24hr`, `date-format`, `min-date`, `max-date`, `min-time`, `max-time` | datepicker | Flatpickr config, read from `data-fp-*` attributes |

Put buttons in the `prepend` or `append` slot to attach them to the input,
for example a search box with its submit button. Give the button the same
`size` as the input:

```blade
<x-avian::input name="q" icon="fas fa-search" placeholder="Search orders" :field="false">
    <x-slot:append>
        <x-avian::button type="submit">Search</x-avian::button>
    </x-slot:append>
</x-avian::input>
```

`numeric` renders the input as a plain text field wired to Alpine's dynamic
money mask (`x-mask:dynamic="$money($input)"`), formatting thousands
separators and a decimal point as the user types:

```blade
<x-avian::input name="budget" label="Budget" prefix="Rp" numeric />
```

This needs the [`@alpinejs/mask`](https://alpinejs.dev/plugins/mask) plugin
loaded alongside Alpine — it is not bundled by the package:

```blade
<x-avian::scripts />
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
```

`searchable-select` behaves like `select` but replaces the native dropdown
with a searchable one — handy once an option list gets too long to scan.
Filtering happens client-side, in the browser, against the rendered option
labels, so it works without a Livewire component:

```blade
<x-avian::searchable-select
    name="country"
    label="Country"
    placeholder="Choose a country"
    :options="['us' => 'United States', 'id' => 'Indonesia', 'jp' => 'Japan']"
    wire:model="country"
/>
```

Drop `options` and pass `<x-avian::searchable-select.option>` children instead
for custom row markup:

```blade
<x-avian::searchable-select wire:model="itemNo" :value="$itemNo">
    @foreach ($items as $item)
        <x-avian::searchable-select.option :value="$item->id" :label="$item->name" :selected="$itemNo">
            <strong>{{ $item->id }}</strong> <small>{{ $item->name }}</small>
        </x-avian::searchable-select.option>
    @endforeach
</x-avian::searchable-select>
```

By default filtering runs client-side, in the browser, against the rendered
option labels, so it works without a Livewire component. Pass `search-model`
to hand filtering to the server instead (Livewire only) — the parent owns the
search property and returns an already-filtered `options` list, exactly like
it already owns the selected value through `wire:model` + `:value`:

```blade
<x-avian::searchable-select
    wire:model.live="filter.status"
    :value="$filter['status']"
    :options="$this->statusOptions"
    search-model="filter.statusSearch"
/>
```

The trigger label comes from `options`. So when a value is already selected
(a default set in `mount()`, or a saved record being edited) but your query
doesn't return it — it's past the `limit()`, or a search filtered it out — the
trigger falls back to the placeholder. The component caches labels on the
client, though, so it only needs to see the selected option **once**. Put it
at the front of the list while the search term is empty:

```php
public ?int $customerId = null;
public string $customerSearch = '';

public function mount(): void
{
    $this->customerId ??= auth()->user()->default_customer_id;
}

#[Computed]
public function customerOptions(): array
{
    $options = Customer::query()
        ->when($this->customerSearch, fn ($query, $term) => $query->where('name', 'like', "%{$term}%"))
        ->orderBy('name')
        ->limit(20)
        ->pluck('name', 'id')
        ->all();

    if ($this->customerId && blank($this->customerSearch) && ! array_key_exists($this->customerId, $options)) {
        $options = [$this->customerId => Customer::find($this->customerId)?->name] + $options;
    }

    return $options;
}
```

```blade
<x-avian::searchable-select
    wire:model.live="customerId"
    :value="$customerId"
    :options="$this->customerOptions"
    search-model="customerSearch"
/>
```

Merge with `+`, not `array_merge()`: `array_merge()` renumbers integer keys,
which breaks the ID-to-label mapping. The `blank()` check keeps search results
honest, since the pinned row goes away once the user types. The label stays
because it is already cached. If you build the rows yourself as
`searchable-select.option` children, don't render the same value twice: each
row's `wire:key` is derived from its value.

Add `clearable` for a × button that resets the value, and `taggable` to let
users submit a value that is not in the list — when the search term matches no
option label, an `Add "…"` row (`create-text`, with `:term` replaced) picks the
typed text as both value and label. Reopening a taggable select prefills the
search box with the current label so it can be edited in place:

```blade
<x-avian::searchable-select name="city" :options="$cities" clearable taggable />
```

`multi-select` is the multiple-choice version of `searchable-select`: the same
searchable dropdown, with each pick shown as a removable chip in the trigger.
The dropdown stays open while picking, and Backspace in an empty search box
removes the last chip. Values submit as `name[]`, so the request receives an
array, and validation messages for both `tags` and `tags.*` are shown:

```blade
<x-avian::multi-select
    name="tags"
    label="Tags"
    placeholder="Pick a few tags"
    :options="['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go']"
    :value="['php']"
    max="3"
/>
```

`clearable` and `taggable` work here too: × on the trigger removes every pick,
and a typed term that matches no option can be added as a chip (click the
`Add "…"` row or press Enter). Backspace in an empty search box takes a typed
tag back into the box for editing. Validate each item (`tags.*`) on the server.

With Livewire, `wire:model` binds the whole array (through Alpine's
`x-modelable`), so `.live` and the other modifiers work as usual. Drop
`options` and pass `<x-avian::multi-select.option>` children for custom row
markup:

```blade
<x-avian::multi-select wire:model.live="userIds">
    @foreach ($users as $user)
        <x-avian::multi-select.option :value="$user->id" :label="$user->name" :selected="$userIds">
            <strong>{{ $user->name }}</strong> <small>{{ $user->email }}</small>
        </x-avian::multi-select.option>
    @endforeach
</x-avian::multi-select>
```

Chip labels come from `options` too, and a pick missing from them shows as its
raw value (`42` instead of `Jane Doe`). If `options` comes from a limited query,
merge the current picks in so each one gets its label on the first render.
`multi-select` filters client-side, so the extra rows can stay in the list:

```php
#[Computed]
public function userOptions(): array
{
    $options = User::query()->orderBy('name')->limit(50)->pluck('name', 'id')->all();

    $missing = array_diff($this->userIds, array_keys($options));

    return $missing === []
        ? $options
        : User::whereKey($missing)->pluck('name', 'id')->all() + $options;
}
```

```blade
<x-avian::multi-select wire:model.live="userIds" :value="$userIds" :options="$this->userOptions" />
```

All extra attributes land on the control itself, so `wire:model`, `x-on:*`,
`placeholder`, `min`, `step` and the rest behave exactly as expected:

```blade
<x-avian::input name="search" :field="false" wire:model.live.debounce.300ms="search" />
```

When a control is bound with `wire:model`, the old-input fallback is skipped so
Livewire stays the single source of truth.

`datepicker` renders a plain text input carrying a `flatpickr-input` hook class
and `data-fp-*` attributes (`data-fp-mode`, `data-fp-date-format`,
`data-fp-enable-time`, `data-fp-no-calendar`, `data-fp-time-24hr`,
`data-fp-min-date`, `data-fp-max-date`, `data-fp-min-time`, `data-fp-max-time`).
Like `numeric`,
[flatpickr](https://flatpickr.js.org) itself is not bundled by the package —
the host application loads it and upgrades every `.flatpickr-input` on page
load (and again after `livewire:navigated`, for a Livewire SPA-style page),
reading its config out of those `data-fp-*` attributes. Flatpickr marks the
input `readonly` by default, so it renders with the same dimmed styling as a
`disabled` field unless the host app's config passes `allowInput: true`:

```blade
<x-avian::datepicker name="start_date" label="Start date" />
<x-avian::datepicker name="range" label="Date range" mode="range" />
<x-avian::datepicker name="datetime" label="Appointment" enable-time date-format="Y-m-d H:i" />
<x-avian::datepicker name="opens_at" label="Opens at" mode="time" min-time="08:00" max-time="17:00" />
```

`mode="time"` is a time-only picker (flatpickr's `noCalendar`): the format
defaults to `H:i`, and `time-24hr` (on by default) picks a 24-hour clock — pass
`:time24hr="false"` with `date-format="h:i K"` for AM/PM. The host app's init
script maps the new attributes like the others:

```js
flatpickr(input, {
    mode: input.dataset.fpMode,
    dateFormat: input.dataset.fpDateFormat,
    enableTime: input.dataset.fpEnableTime === 'true',
    noCalendar: input.dataset.fpNoCalendar === 'true',
    time_24hr: input.dataset.fpTime24hr === 'true',
    minDate: input.dataset.fpMinDate || null,
    maxDate: input.dataset.fpMaxDate || null,
    minTime: input.dataset.fpMinTime || null,
    maxTime: input.dataset.fpMaxTime || null,
});
```

`<x-avian::slider>` is a styled range input. `show-value` prints the current
value next to the track (with an optional `prefix` / `suffix`), and `range`
adds a second thumb for a low and a high bound. A range submits `name[min]` and
`name[max]`, reads its error from `name`, `name.min` or `name.max`, and binds
to a `['min' => ..., 'max' => ...]` array with `wire:model`:

```blade
<x-avian::slider name="volume" label="Volume" :value="40" show-value suffix="%" />
<x-avian::slider name="price" label="Price" :max="1000" :step="10" :value="[100, 500]" range show-value prefix="$" />
<x-avian::slider wire:model.live.debounce.300ms="price" :max="1000" range />
```

`<x-avian::filter-chip>` is a toggleable pill for narrowing a list. It is a
checkbox underneath (`type="radio"` for one pick at a time), so a row of them
submits and repopulates like checkboxes; `count` adds a number pill. Given an
`href` it renders a link instead, with `active` marking the current filter.
Wrap chips in `<x-avian::filter-chip.group>` for a label and the group's
validation message:

```blade
<x-avian::filter-chip.group name="status" label="Status">
    <x-avian::filter-chip name="status[]" value="open" label="Open" :count="24" />
    <x-avian::filter-chip name="status[]" value="closed" label="Closed" />
</x-avian::filter-chip.group>

<x-avian::filter-chip :href="request()->fullUrlWithQuery(['status' => 'overdue'])" :active="request('status') === 'overdue'" label="Overdue" />
```

### 4. General components

```blade
<x-avian::page-header title="Users" subtitle="Everyone with access">
    <x-slot:actions>
        <x-avian::button icon="fas fa-plus" x-on:click="$dispatch('aui-modal-open', { name: 'create-user' })">
            New user
        </x-avian::button>
    </x-slot:actions>
</x-avian::page-header>

<x-avian::alert variant="warning" dismissible>Two records need review.</x-avian::alert>

<x-avian::card title="Members" subtitle="Active this month" :padded="false">
    <x-avian::table :headers="['Name', 'Role', '']">
        @foreach ($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td><x-avian::badge variant="success" dot>{{ $user->role }}</x-avian::badge></td>
                <td class="aui-table-align-right">
                    <x-avian::dropdown align="right" size="sm" label="Actions">
                        <x-avian::dropdown.item icon="fas fa-pen" :href="route('users.edit', $user)">Edit</x-avian::dropdown.item>
                        <div class="aui-dropdown-divider"></div>
                        <x-avian::dropdown.item icon="fas fa-trash" danger wire:click="delete({{ $user->id }})">Delete</x-avian::dropdown.item>
                    </x-avian::dropdown>
                </td>
            </tr>
        @endforeach
    </x-avian::table>
</x-avian::card>
```

Available components: `accordion` (+ `accordion.item`), `alert`, `avatar`,
`badge`, `breadcrumbs` (+ `breadcrumbs.item`), `button`, `button-group`, `card`, `confirm`,
`divider`, `drawer`, `dropdown` (+ `dropdown.item`), `empty`, `page-header`,
`pagination`, `progress`, `scripts`, `spinner`, `stat`, `styles`, `table` (+ `table.row`), `timeline` (+ `timeline.item`), `toasts`, `toolbar`,
`datalist` (+ `datalist.item`), `tabs` (+ `tabs.panel`),
`modal`, plus the form set `form`, `field`, `label`, `error`, `hint`, `input`,
`textarea`, `select`, `searchable-select` (+ `searchable-select.option`),
`multi-select` (+ `multi-select.option`), `checkbox`, `radio`, `switch`,
`slider`, `filter-chip` (+ `filter-chip.group`), `file`, `datepicker`.

Pass `collapsible` to let the viewer fold a card into its header. A chevron
appears in the header, and clicking the header outside its actions toggles it
too. `collapsed` starts it folded, and `persist` remembers the viewer's choice
in localStorage under that key:

```blade
<x-avian::card title="Advanced settings" collapsible collapsed persist="advanced-settings">
    ...
</x-avian::card>
```

The card dispatches `aui-card-toggled` with `{ collapsed }` whenever it opens
or closes.

`button-group` lines up buttons in a row. Pass `attached` to join them into a
segmented control, and mark the selected button with `active` (or
`aria-pressed="true"`). `toolbar` goes above a table: filters sit in the
default slot and actions in the `end` slot, pushed to the right:

```blade
<x-avian::toolbar label="Orders">
    <x-avian::input name="q" icon="fas fa-search" placeholder="Search" :field="false" />

    <x-avian::button-group attached label="Status">
        <x-avian::button variant="light" active>All</x-avian::button>
        <x-avian::button variant="light">Paid</x-avian::button>
    </x-avian::button-group>

    <x-slot:end>
        <x-avian::button icon="fas fa-plus">New order</x-avian::button>
    </x-slot:end>
</x-avian::toolbar>
```

An `icon-only` button uses its `label` as both the `aria-label` and the native
`title` tooltip. Add `rounded` to make it a circle, and use `size="xs"` in
dense table rows.

Breadcrumbs go right above the page header. Pass `label => url` pairs (the last
one is the current page) or a list of `['label', 'href', 'icon']` arrays:

```blade
<x-avian::breadcrumbs navigate :items="[
    'Dashboard' => route('dashboard'),
    'Orders' => route('orders.index'),
    $order->number => null,
]" />
```

`stat` is a KPI tile: a label, a pre-formatted value and how it moved. The
sign of `change` picks the arrow; up is green and down red, and `invert`
swaps them for numbers where less is better:

```blade
<div class="aui-grid aui-grid-4">
    <x-avian::stat label="Revenue" value="Rp 1,28 M" change="+12.5%" description="vs last month" icon="fas fa-wallet" />
    <x-avian::stat label="Returns" value="18" change="-22%" invert icon="fas fa-rotate-left" color="warning" />
</div>
```

`accordion` stacks collapsible sections — one open at a time unless
`multiple` is set; `flush` drops the border for use inside a card. `divider`
draws a rule, optionally labelled (`label="or"`, `align="left"`) or `vertical`
inside a flex row:

```blade
<x-avian::accordion>
    <x-avian::accordion.item title="Shipping address" icon="fas fa-truck" open>...</x-avian::accordion.item>
    <x-avian::accordion.item title="Billing address">...</x-avian::accordion.item>
</x-avian::accordion>

<x-avian::divider label="or" />
```

Pass `icon-only` for a square, icon-only button (a table row action, a
toolbar) — it has no visible text, so pass `label` for an accessible name:

```blade
<x-avian::button icon="fas fa-pen" icon-only label="Edit" size="sm" variant="light" />
<x-avian::button icon="fas fa-trash" icon-only label="Delete" size="sm" variant="light" wire:click="delete" />
```

Pass `navigate` on an `href` button to add `wire:navigate`, for Livewire's
SPA-style page swap. It is opt-in, not automatic just because `href` is set —
an external link, a `mailto:`/`tel:` link or an on-page `#anchor` would break
under `wire:navigate`, so only reach for it on same-app links:

```blade
<x-avian::button href="{{ route('dashboard') }}" navigate>Dashboard</x-avian::button>
```

Pass a paginator straight to the table to get Previous/Next and numbered page
links rendered underneath it:

```blade
<x-avian::table :headers="['Name', 'Role']" :paginator="$users">
    @foreach ($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->role }}</td>
        </tr>
    @endforeach
</x-avian::table>
```

`$users` can come from `paginate()` (numbered links plus a "Showing X to Y of
Z results" summary), `simplePaginate()` or `cursorPaginate()` (Previous/Next
only). The same
markup is available on its own as `<x-avian::pagination :paginator="$users" />`
for a paginator you render outside a table.

Inside a Livewire component (using `WithPagination`), the page links render as
buttons that call `gotoPage()`, so paging updates the component in place
instead of following a URL. Pass `:livewire="false"` to force plain links.

When the table has no rows it renders an empty state spanning every column
("No data found" by default). Customize it with `empty`, `empty-text` and
`empty-icon`, replace it entirely with an `empty` slot, or turn it off with
`:empty="false"`:

```blade
<x-avian::table :headers="['Name', 'Role']" empty="No users yet" empty-text="Invite someone to get started.">
    @foreach ($users as $user)
        <tr><td>{{ $user->name }}</td><td>{{ $user->role }}</td></tr>
    @endforeach
</x-avian::table>
```

The colspan comes from `headers`; when you build the header with a `head`
slot instead, pass `:columns="3"` so the empty row spans the whole table.

To make a column sortable, pass its header as an array with a `sort` key (and
optionally `align` => `'right'` or `'center'`). The heading becomes a link that
sets `?sort=<key>&direction=asc`, flips to `desc` when clicked again, keeps the
rest of the query string and drops the page parameter. The table reads the
current sort from the query string; you apply it to the query yourself, and
should check the key against an allowlist:

```blade
{{-- Controller:
    $sort = in_array($request->query('sort'), ['name', 'created_at'], true) ? $request->query('sort') : 'name';
    $direction = $request->query('direction') === 'desc' ? 'desc' : 'asc';
    $users = User::orderBy($sort, $direction)->paginate(15)->withQueryString();
--}}

<x-avian::table
    :headers="[
        ['label' => 'Name', 'sort' => 'name'],
        ['label' => 'Joined', 'sort' => 'created_at', 'align' => 'right'],
        '',
    ]"
    :paginator="$users"
>
    ...
</x-avian::table>
```

Pass `sort-by` and `sort-direction` to show a sort that isn't in the query
string (a default sort, say), and `sort-param` / `direction-param` to rename
the query parameters. In a `head` slot, use `<x-avian::table.heading
sort="name">Name</x-avian::table.heading>`; it picks up the table's sort props.

Inside a Livewire component, sortable headings render as buttons that call
`sortBy('<key>')`. Define that method and pass the current state to the table:

```php
public string $sort = 'name';
public string $direction = 'asc';

public function sortBy(string $column): void
{
    $this->direction = $this->sort === $column && $this->direction === 'asc' ? 'desc' : 'asc';
    $this->sort = $column;
    $this->resetPage();
}
```

```blade
<x-avian::table :headers="$headers" :sort-by="$sort" :sort-direction="$direction" :paginator="$users">
```

Swap a `<tr>` for `<x-avian::table.row>` to make it expandable: the
`details` slot renders in a hidden row underneath that spans every column, and
a chevron button toggles it. The row adds its own toggle cell, so give the
table an empty heading for it (first by default, last with `toggle="end"`).
`expanded` starts it open and `clickable` lets a click anywhere on the row
toggle it. Stripes and hover treat the row and its details as one row, and the
open state survives Livewire re-renders (give the row a `wire:key`):

```blade
<x-avian::table :headers="['', 'Invoice', 'Customer', 'Total']" striped>
    @foreach ($invoices as $invoice)
        <x-avian::table.row wire:key="invoice-{{ $invoice->id }}" clickable>
            <td>{{ $invoice->number }}</td>
            <td>{{ $invoice->customer->name }}</td>
            <td>{{ $invoice->total }}</td>

            <x-slot:details>
                @include('invoices.partials.lines', ['invoice' => $invoice])
            </x-slot:details>
        </x-avian::table.row>
    @endforeach
</x-avian::table>
```

For records that read better as cards than as rows (products, files, people),
use `<x-avian::datalist>`. It takes the same `paginator` and empty-state props
(`empty`, `empty-text`, `empty-icon`, an `empty` slot) as the table, and shows
its items either as a list or as a grid of cards, with a toggle between the two:

```blade
<x-avian::datalist :paginator="$products" view="grid" :columns="3" persist="products">
    <x-slot:toolbar>
        <span>{{ $products->total() }} products</span>
    </x-slot:toolbar>

    @foreach ($products as $product)
        <x-avian::datalist.item
            :title="$product->name"
            :subtitle="$product->sku"
            :image="$product->image_url"
            :href="route('products.show', $product)"
        >
            {{ $product->summary }}

            <x-slot:meta>
                <x-avian::badge variant="success" dot>In stock</x-avian::badge>
            </x-slot:meta>

            <x-slot:actions>
                <x-avian::button icon="fas fa-pen" icon-only label="Edit" size="sm" variant="light" />
            </x-slot:actions>
        </x-avian::datalist.item>
    @endforeach
</x-avian::datalist>
```

- `view` is the initial layout (`list` or `grid`), `:columns` the cards per row
  in grid view (1–4, fewer on small screens).
- `persist="products"` remembers the viewer's choice in localStorage;
  `:toggle="false"` hides the switch for a fixed layout.
- With Livewire, `wire:model="view"` binds the layout to a property.
  Every change also dispatches an `aui-view-changed` browser event.
- Each item takes `title`, `subtitle`, `image` or `icon`, and `href` (the whole
  item becomes clickable, while `actions` stay separately clickable), plus
  `media`, `meta` and `actions` slots. The same item renders as a row in list
  view and as a card in grid view.

`<x-avian::timeline>` lists events in order — an order's history, an audit
log. Each `timeline.item` takes a `title`, a `time` (a string, or a date
printed with `time-format`, or as "3 hours ago" with `relative`), and an
`icon` or `variant` (`success`, `warning`, `danger`, `info`, `neutral`) for
its marker; the slot holds the details:

```blade
<x-avian::timeline>
    @foreach ($order->events as $event)
        <x-avian::timeline.item :title="$event->title" :time="$event->created_at" relative icon="fas fa-truck">
            {{ $event->note }}
        </x-avian::timeline.item>
    @endforeach
</x-avian::timeline>
```

### 5. Modals, dropdowns and tabs

```blade
<x-avian::modal name="create-user" title="New user" size="lg">
    <x-avian::input name="name" label="Name" />

    <x-slot:footer>
        <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
        <x-avian::button wire:click="save">Save</x-avian::button>
    </x-slot:footer>
</x-avian::modal>
```

A named modal listens for browser events, so anything on the page can open it.
The button component takes a `modal` prop for exactly this:

```blade
<x-avian::button modal="create-user">New user</x-avian::button>
```

From your own markup, dispatch the event yourself. Alpine only initialises
elements inside an `x-data` tree, so a standalone trigger needs its own scope:

```blade
<button x-data="{}" x-on:click="$dispatch('aui-modal-open', { name: 'create-user' })">Open</button>
```

```php
// Livewire
$this->dispatch('aui-modal-open', name: 'create-user');
$this->dispatch('aui-modal-close', name: 'create-user');
```

```js
// Plain JavaScript
window.AvianUI.openModal('create-user');
```

Tabs keep their state in Alpine. `variant` styles the tab list itself: omit it
(or pass `line`) for the default underlined tabs, `pill` for standalone
rounded buttons, or `segmented` for a grouped segmented-control look:

```blade
<x-avian::tabs :tabs="['overview' => 'Overview', 'activity' => 'Activity']" variant="segmented">
    <x-avian::tabs.panel name="overview">...</x-avian::tabs.panel>
    <x-avian::tabs.panel name="activity">...</x-avian::tabs.panel>
</x-avian::tabs>
```

A `drawer` is a side panel driven by the same Alpine component and events as
the modal, so `modal="filters"`, `aui-modal-open` and `hide()` all work.
`position` is `right` (default) or `left`, `size` is `sm`, `md`, `lg` or `xl`:

```blade
<x-avian::button variant="light" icon="fas fa-filter" modal="filters">Filters</x-avian::button>

<x-avian::drawer name="filters" title="Filters">
    ...
    <x-slot:footer>
        <x-avian::button wire:click="applyFilters" x-on:click="hide()">Apply</x-avian::button>
    </x-slot:footer>
</x-avian::drawer>
```

For "Are you sure?" prompts, place `<x-avian::confirm />` once in the layout.
Then give a button `confirm="…"` (or any element / form `data-aui-confirm`)
and its click or submit only goes through after a yes — `wire:click`, `href`
and form submits keep working unchanged. Tune the dialog with
`data-aui-confirm-title`, `data-aui-confirm-text`, `data-aui-cancel-text` and
`data-aui-confirm-variant`:

```blade
<x-avian::button variant="danger" wire:click="delete({{ $order->id }})"
    confirm="This cannot be undone." data-aui-confirm-title="Delete order?">
    Delete
</x-avian::button>
```

```php
// Livewire: dispatch `event` back on a yes, for an #[On('order-delete')] listener
$this->dispatch('aui-confirm', message: 'Delete this order?', event: 'order-delete', params: ['id' => 5]);
```

```js
// Plain JavaScript: resolves to true or false
window.AvianUI.confirm({ title: 'Discard draft?' }).then((ok) => ok && discard());
```

Add `data-aui-confirm-loading` (or `loading` from Livewire and JS) and a yes
keeps the dialog open, with a spinner on the confirm button, until the
Livewire requests it triggered finish or the page navigates away.
`<x-avian::confirm loading />` makes that the default. From JavaScript, an
`action` that returns a promise is waited on too:

```blade
<x-avian::button variant="danger" wire:click="delete({{ $order->id }})"
    confirm="This cannot be undone." data-aui-confirm-loading>
    Delete
</x-avian::button>
```

```js
window.AvianUI.confirm({ message: 'Delete this order?', action: () => $wire.delete(5) });
```

For notifications, place `<x-avian::toasts />` once in the layout as well.
It shows `success`, `error`, `warning` and `info` messages flashed to the
session on its own, and `toast` accepts a title and other variants. Set
`position` (`top-right` by default, or `top-left`, `top-center`,
`bottom-right`, `bottom-left`, `bottom-center`), `duration` in ms (`0` keeps
toasts open), `max`, and `:flash="false"` to ignore the session:

```php
return back()->with('success', 'Order saved.');
return back()->with('toast', ['variant' => 'warning', 'title' => 'Low stock', 'message' => 'Only 3 left.']);

// Livewire, without a redirect
$this->dispatch('aui-toast', message: 'Order saved.', variant: 'success');
```

```js
// Plain JavaScript; returns the toast id
window.AvianUI.toast('Link copied.', 'info');
window.AvianUI.toast({ title: 'Export ready', message: 'Check your inbox.', duration: 0 });
```

Hovering or focusing a toast pauses its timer. Messages are rendered as text,
so HTML in them is escaped.

The Alpine components registered by the package are `auiModal`, `auiConfirm`, `auiToasts`,
`auiDropdown`, `auiTabs`, `auiAccordion`, `auiDismiss`, `auiFile`,
`auiDatalist`, `auiSearchableSelect`, `auiMultiSelect`, `auiSlider` and `auiTableRow`. The modal
releases the body scroll lock on `livewire:navigating`, so `wire:navigate`
never strands a locked page.

### 6. Configuration

```php
return [
    'prefix' => 'avian',   // <x-avian::button>; <x-avian-ui::button> always works too

    'assets' => [
        'route' => true,       // serve CSS/JS from the package, no publish needed
        'path' => 'avian-ui',  // URL prefix for that route
        'url' => null,         // or a base URL/path you serve the assets from
        'themes' => true,      // include the theme palette stylesheet
    ],

    'docs' => [
        'enabled' => true,          // serve the documentation page
        'path' => 'avian-ui',       // at /avian-ui
        'middleware' => ['web'],    // add 'auth' to keep it private
    ],
];
```

### 7. Previewing the library

Once installed, the package serves its documentation at `/avian-ui` (route name
`avian-ui.docs`): every component with a live demo, its props and copy-ready
examples. The page loads fonts, Font Awesome, Alpine and flatpickr from a CDN
for itself only.

Publish the config to move it, protect it or switch it off, for example in
production:

```php
'docs' => [
    'enabled' => env('AVIAN_UI_DOCS', true),
    'path' => 'avian-ui',
    'middleware' => ['web', 'auth'],
],
```

When working on the package itself, `composer serve` opens the same page in
the workbench.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Avian Ui! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [Aldo Octavio Cahyadi](https://github.com/aldocahyadi30)
- [All Contributors](../../contributors)

## License

Avian Ui is open-sourced software licensed under the [MIT license](LICENSE.md).
