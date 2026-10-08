---
name: avian-ui-development
description: >
  Build Laravel UI with the Avian Ui Blade component library: asset tags,
  theming tokens, form controls with validation wiring, paginated tables, and
  the Alpine-backed modal, dropdown, popover, tooltip, command palette, tree,
  searchable select, multi select, date range, wizard and tab components in Blade
  and Livewire applications.
license: MIT
metadata:
  author: Aldo Octavio Cahyadi
---

# Avian Ui

Use this skill when a Laravel application needs to build or restyle UI with the
`avia-avian/avian-ui` package.

## Primary Goal

- apply the `avia-avian/avian-ui` package's public API in the smallest correct way

## Workflow

### 1. Inspect the Laravel app context

- confirm the package is installed and the layout renders `<x-avian::styles />` and `<x-avian::scripts />`
- confirm Alpine is available and that `<x-avian::scripts />` runs before it: Livewire loads Alpine at the end of the page, otherwise the app's own Alpine tag must sit below the package script
- check `config/avian-ui.php` for a custom component `prefix` and for the `assets` strategy
- check whether the app already defines `--color-primary` and friends before adding theme CSS
- the package serves its own component documentation at `/avian-ui` (route `avian-ui.docs`, configured under `docs`); don't add an app route on that path

### 2. Apply the package's public API

**Assets.** Add the two tags once, in the layout head. The default asset route
serves the CSS and JS from the package, so nothing needs publishing or
building. Never add a CDN tag for the package assets.

**Components.** Use the anonymous components rather than hand-written markup:

- general: `button`, `button-group`, `toolbar`, `card`, `stat`, `badge`, `alert`, `table` (+ `table.row`), `datalist` (+ `datalist.item`), `timeline` (+ `timeline.item`), `pagination`, `page-header`, `breadcrumbs` (+ `breadcrumbs.item`), `empty`, `avatar`, `avatar-group`, `description-list` (+ `description-list.item`), `copy-button`, `tree`, `command` (+ `command.group`, `command.item`), `progress`, `spinner`, `skeleton` (+ `skeleton.table`), `kbd`, `stepper`, `divider`, `accordion` (+ `accordion.item`), `modal`, `drawer`, `confirm`, `toasts`, `dropdown` (+ `dropdown.item`), `popover`, `tooltip`, `tabs` (+ `tabs.panel`)
- form: `form`, `field`, `label`, `error`, `hint`, `input`, `textarea`, `select`, `searchable-select` (+ `searchable-select.option`), `multi-select` (+ `multi-select.option`), `checkbox`, `radio`, `switch`, `slider`, `filter-chip` (+ `filter-chip.group`), `file`, `datepicker`, `date-range`, `wizard` (+ `wizard.step`)

Form controls render their own label, hint and validation message from `name`
(or the `wire:model` property when there is no `name`), and repopulate from old
input, which wins over a passed `value`; enum values are accepted wherever a
value is:

```blade
<x-avian::input name="email" type="email" label="Email" required />
<x-avian::select name="role" label="Role" :options="$roles" placeholder="Choose" />
<x-avian::searchable-select name="country" label="Country" :options="$countries" />
<x-avian::multi-select name="tags" label="Tags" :options="$tags" :value="$post->tag_ids" />
```

Use `searchable-select` instead of `select` once an option list is too long
to scan in a native dropdown — it adds a search box and filters client-side by
default, so it needs no Livewire component of its own. Drop `options` and pass
`<x-avian::searchable-select.option>` children for custom row markup, or pass
`search-model` to hand filtering to the server instead (Livewire only, mirrors
how `wire:model` + `:value` already own the selected value). Add `clearable`
for a × reset button, and `taggable` to accept free-typed values that are not
in `options` (the value is its own label; validate it on the server since it is
arbitrary input).

With `search-model` (or any limited query), the trigger label is resolved from
`options`. When the current value has a default or saved selection the query
doesn't return, the placeholder shows instead. Labels are cached client-side,
so the option only has to appear once: in the computed options, prepend it while
the search term is blank, and merge with `+`, never `array_merge()`, which
renumbers integer keys:

```php
if ($this->customerId && blank($this->customerSearch) && ! array_key_exists($this->customerId, $options)) {
    $options = [$this->customerId => Customer::find($this->customerId)?->name] + $options;
}
```

In slot mode, skip that value in the `@foreach` so it isn't rendered twice:
option `wire:key`s are derived from the value.

Use `multi-select` when several values can be picked. It submits `name[]` (an
array: validate `tags` and `tags.*`), shows picks as removable chips, accepts
`max`, `clearable` and `taggable` (free-typed tags; validate `tags.*`), and
binds the whole array with `wire:model` through `x-modelable`. Chip labels come
from `options`, and a pick missing from them shows its raw value. When `options`
comes from a limited query, merge the missing picks in, again with `+`:
`User::whereKey(array_diff($this->userIds, array_keys($options)))->pluck('name', 'id')->all() + $options`.

Pass `numeric` to `input` for a money-masked amount field
(`<x-avian::input name="budget" numeric />`) — it renders as a text field
wired to Alpine's `x-mask:dynamic="$money($input)"`. This requires the
`@alpinejs/mask` plugin loaded alongside Alpine (loaded before Alpine core,
same as any Alpine plugin); the package does not bundle it.

`datepicker` renders a plain text input carrying a `flatpickr-input` hook
class and `data-fp-*` attributes (`mode`, `enable-time`, `date-format`,
`min-date`, `max-date`, and for a clock `time-24hr`, `min-time`, `max-time`;
`mode="time"` is time-only, formatted `H:i` by default):
`<x-avian::datepicker name="start_date" label="Start date" />`. Like
`numeric`, [flatpickr](https://flatpickr.js.org) is not bundled — the host
app loads it and upgrades every `.flatpickr-input` on page load, reading its
config from the `data-fp-*` attributes.

Use `date-range` for a from–to filter or period instead of two datepickers: it
submits `name[from]` and `name[to]` in `value-format` (`Y-m-d` by default)
whatever `date-format` it shows, so validate `period.from` / `period.to`.
`presets` adds quick ranges (`true`, or keys out of `today`, `yesterday`,
`last_7_days`, `last_30_days`, `this_month`, `last_month`, `this_year`),
`clearable` a Clear button, and `wire:model` binds `['from' => ..., 'to' => ...]`.
It needs flatpickr exactly like `datepicker`.

Use `wizard` with `wizard.step` children (each a `title`, optional
`description`) to split a long form into steps; keep the wizard inside one
`<form>` (or `wire:submit` form) so Finish submits every step at once. Next
relies on native validation attributes (`required`, `type`, `min`, `pattern`)
on the step's fields, so put them on the inputs; `wire:model` on the wizard
binds the step number, which lets the server jump back to a step that failed
validation. For a read-only progress indicator use `stepper` (`steps`,
`current`, `vertical`) instead.

For destructive actions, place `<x-avian::confirm />` once in the layout and
add `confirm="message"` to the `button` (or `data-aui-confirm` to any element
or form) — never hand-roll a confirm modal or use `wire:confirm`. From
Livewire: `$this->dispatch('aui-confirm', message: '...', event: 'x', params: [...])`
dispatches `x` back on a yes; from JS: `AvianUI.confirm({...})` returns a
promise of a boolean. For slow actions add `data-aui-confirm-loading` (or
`loading: true`, or a promise-returning `action` in JS) so the dialog stays
open with a spinner until the Livewire request finishes — don't add a
separate loading modal.

For action feedback, place `<x-avian::toasts />` once in the layout and flash
`success`, `error`, `warning` or `info` on the redirect
(`back()->with('success', 'Saved.')`), or `toast` with
`['variant', 'title', 'message']`. From Livewire:
`$this->dispatch('aui-toast', message: '...', variant: 'success')`; from JS:
`AvianUI.toast('...', 'success')`. Never hand-roll a flash-message banner.

Use `drawer` (same events as `modal`: `modal="name"` on a button,
`aui-modal-open`, `hide()`) for filters or quick-edit side panels, `stat` for
dashboard KPI tiles (pre-formatted `value`, `change` sign picks the arrow,
`invert` when less is better), `breadcrumbs` (`label => url` items, last one
current) right above `page-header`, `accordion` for collapsible sections and
`divider` between blocks.

Wrap an element in `tooltip` (`text`, `placement`) to label it on hover and
focus; an `icon-only` button still needs its own `label`. Use `popover`
(`trigger` slot, `title`, `footer` slot, `width`) for small interactive panels
such as quick filters; call `hide()` from inside to close it. Prefer
`dropdown` for a menu of actions and `modal` for anything that needs focus.

Use `description-list` for the label / value block of a show page instead of a
hand-written `<dl>` or table: items take `label` plus `value` (dates, enums and
booleans are formatted) or a slot, `copyable` for ids and numbers, `full` for
long text. Use `avatar-group :users="..." :max="3"` for assignees or members.

Use `tree` for nested data (categories, folders, permission sets) rather than
nested lists: pass `items` with `id`, `label` and `children` (eager-load the
relation). With `selectable` and `name` it is a form field submitting
`name[]` (validate `name` and `name.*`); checking a branch checks all of it.

Place one `command` in the layout for a Cmd/Ctrl+K palette; items with `href`
(+ `navigate`), `modal`, or their own `wire:click`. For record search, pass
`search-model` and render the items from a Livewire computed property. Never
hand-roll a keyboard-shortcut search overlay.

Use `skeleton` (`variant` `text` with `lines`, `circle`, `rect`, `button`) or
`skeleton.table` (`rows`, `columns`, `label`) as a Livewire lazy component's
`placeholder()` or inside `wire:loading`, and `kbd` (`keys="Ctrl+K"`) to show
keyboard shortcuts.

Pass `icon-only` to `button` for a square, icon-only button (table row
actions, a toolbar) — it has no visible text, so it needs `label` for an
accessible name: `<x-avian::button icon="fas fa-pen" icon-only label="Edit" />`.

Pass `navigate` to a `button` with `href` to add `wire:navigate` (Livewire
SPA-style page swap). It is opt-in, not automatic just because `href` is set —
never add it for an external link, `mailto:`/`tel:`, or an on-page `#anchor`.

Pass `variant` to `tabs` to style the tab list: omit it (or pass `line`) for
the default underlined tabs, `pill` for standalone rounded buttons, or
`segmented` for a grouped segmented-control look.

Extra attributes pass through to the control, so `wire:model`, `x-on:*` and
native attributes work unchanged. With `wire:model` the old-input fallback is
skipped on purpose.

**Paginated tables.** Pass a paginator straight to `table` to render
Previous/Next and numbered page links underneath it, or render
`<x-avian::pagination :paginator="$items" />` on its own:

```blade
<x-avian::table :headers="['Name', 'Role']" :paginator="$users">
    @foreach ($users as $user)
        <tr><td>{{ $user->name }}</td><td>{{ $user->role }}</td></tr>
    @endforeach
</x-avian::table>
```

Works with `paginate()` (numbered links plus a result count),
`simplePaginate()` and `cursorPaginate()` (Previous/Next only).

With no rows, `table` renders an empty state across every column. Set
`empty`, `empty-text`, `empty-icon`, pass an `empty` slot, or disable it with
`:empty="false"`; pass `:columns` when the header comes from a `head` slot.

For rows that reveal more on demand, use `<x-avian::table.row>` instead of
`<tr>` and put the hidden content in its `details` slot; it adds a toggle
cell (give the table an empty heading for it), `expanded` starts it open and
`clickable` toggles on a row click. Give it a `wire:key` under Livewire.

**Paginated lists and grids.** For records that read better as cards
(products, files, people), use `datalist`: same `paginator` and empty-state
props as `table`, plus a list/grid toggle. `view` sets the initial layout,
`:columns` the cards per row (1–4), `persist="key"` remembers the choice in
localStorage, `:toggle="false"` hides the switch and `wire:model` binds the
layout to a Livewire property:

```blade
<x-avian::datalist :paginator="$products" view="grid" :columns="3" persist="products">
    @foreach ($products as $product)
        <x-avian::datalist.item :title="$product->name" :subtitle="$product->sku"
            :image="$product->image_url" :href="route('products.show', $product)">
            {{ $product->summary }}
            <x-slot:meta>{{ $product->price }}</x-slot:meta>
            <x-slot:actions>...</x-slot:actions>
        </x-avian::datalist.item>
    @endforeach
</x-avian::datalist>
```

**Interactive components.** Open a named modal with the button's `modal` prop
(`<x-avian::button modal="edit">`), from Livewire
(`$this->dispatch('aui-modal-open', name: 'edit')`) or from JavaScript
(`window.AvianUI.openModal('edit')`). A hand-written trigger needs its own
`x-data="{}"` scope before `$dispatch` resolves, because Alpine only
initialises elements inside an `x-data` tree.

**Styling.** Compose with the `aui-*` classes (`aui-form-grid`, `aui-stack`,
`aui-row`, `aui-grid`, `aui-form-actions`, `aui-table-align-right`). Override
design tokens (`--aui-primary`, `--aui-radius-lg`, `--aui-font-sans`) in the
app's own CSS instead of restyling components with new rules. Every colored
component shares one palette — `primary`, `secondary`, `success`, `warning`,
`danger`, `info`, `neutral`, `dark`, `purple`, `indigo`, `teal`, `orange`,
`pink` — so any of them works as a badge, alert, toast, progress or timeline
`variant`, a button `variant` (or `color` with `outline` / `ghost`), a stat
`color` and a confirm `variant`. Recolor one with its tokens:
`--aui-{color}` plus `-hover`, `-soft`, `-strong` and `-border` (primary uses
`--aui-primary-dark`, `-light` and `-darker`). Pick a bundled
palette with `data-theme` on `<html>` only when the app has no `--color-*`
theme system of its own.

## Rules, References, and Templates

Read before executing:

- the package README `Usage` section for the full prop tables and examples

## Examples

- Replace a hand-written form with `<x-avian::form>` plus `<x-avian::input>` controls so labels, hints, required markers and validation messages come from the component instead of repeated markup.
- Add a Livewire-driven edit dialog by rendering `<x-avian::modal name="edit-user">` once and dispatching `aui-modal-open` from the Livewire component.
- Swap a long `<x-avian::select>` option list for `<x-avian::searchable-select>` so users can filter it instead of scrolling a native dropdown.
- Replace a `<select multiple>` with `<x-avian::multi-select>` so users can search the options and see their picks as chips.
- Pass a `paginate()` result to `<x-avian::table :paginator="$items">` instead of hand-rolling Previous/Next links.
- Replace hand-styled status toggles above a list with `<x-avian::filter-chip>` checkboxes (or `href` links for query-string filters) inside `<x-avian::filter-chip.group>`.
- Use `<x-avian::slider range>` for a min/max filter such as a price bracket; it submits `name[min]` and `name[max]`.
- Replace two separate start/end datepickers with `<x-avian::date-range name="period" presets>` and validate `period.from` / `period.to`.
- Split a long signup form into `<x-avian::wizard>` steps inside the existing `<form>`, keeping native `required` attributes so Next validates each step.
- Replace a hand-written details table on a show page with `<x-avian::description-list>` items, marking the invoice number `copyable`.
- Render a role's permission groups as `<x-avian::tree name="permissions" selectable>` instead of nested checkbox lists.
- Add `<x-avian::command>` to the app layout with the main pages and a Livewire `search-model` for records.
- Return `<x-avian::skeleton.table>` from a lazy Livewire component's `placeholder()` instead of a spinner.
- Render an audit log or order history with `<x-avian::timeline>` and `timeline.item`, passing the model's date as `time` with `relative`.
- Theme an application by defining `--aui-primary` in the app stylesheet rather than editing the package CSS.

## Anti-patterns

- do not document package internals here; keep the skill focused on adoption in Laravel apps
- do not bundle or load a second copy of Alpine in a Livewire application
- do not place `<x-avian::scripts />` after the application's own Alpine tag
- do not hardcode brand colors in views; use the design tokens
- do not add per-component color classes (`.aui-badge-brand`); pick a palette color or override its `--aui-{color}` tokens
- do not publish the package views to tweak one component when a prop, a slot or a token override does the job
