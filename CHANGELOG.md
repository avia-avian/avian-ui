# Release Notes

## Unreleased

## [v1.2.0](https://github.com/avia-avian/avian-ui/compare/v1.1.0...v1.2.0) - 2026-10-28

### Added

- `table` freezes rows and columns: `sticky-header` and `sticky-footer` pin the head and foot while the rows scroll inside `max-height` (70vh by default), and `sticky-columns` / `sticky-columns-end` pin the first or last columns while the table scrolls sideways. Grouped headings with `colspan` / `rowspan` freeze with their columns.
- `modal` and `drawer` take a `label` to name a dialog whose header slot replaces the title.

### Changed

- The documentation page is off in production by default. `docs.enabled` now defaults to `null` (on everywhere but production); set it to `true` to keep serving it there.

### Fixed

- `modal`, `drawer` and `confirm` move focus into the dialog when it opens, keep Tab inside it, and hand focus back to what opened it when it closes. Modals and drawers are named by their title through `aria-labelledby`, and a drawer is described by its subtitle.
- The README now says Font Awesome 6 has to be loaded for the icons.

## [v1.1.0](https://github.com/avia-avian/avian-ui/compare/v1.0.0...v1.1.0) - 2026-10-21

### Added

- `multi-select` accepts `search-model` and `search-debounce` for server-side filtering (Livewire only), like `searchable-select`: the search box writes to the property and the component returns the matching `options`. Chips keep their labels after a search filters their option out, and a typed tag still goes back into the search box on Backspace.

## [v1.0.0](https://github.com/avia-avian/avian-ui/compare/v0.1.0...v1.0.0) - 2026-10-19

### Added

- `tooltip` and `popover` components, positioned with `position: fixed` so scrolling containers never clip them, flipping to the other side when there is no room.
- `skeleton` (text, circle, rect, button) and `skeleton.table` loading placeholders, for Livewire lazy components.
- `stepper` progress indicator, horizontal or vertical, with per-step status and links.
- `wizard` (+ `wizard.step`): a form split into steps with native per-step validation and `wire:model` support.
- `date-range`: a flatpickr range picker that submits `name[from]` / `name[to]` in a fixed value format, with presets and a clear button.
- `kbd` for keyboard keys and shortcuts.
- `description-list` (+ `description-list.item`) for a record's label / value details, with date, enum and boolean formatting and copyable values; `copy-button` on its own.
- `avatar-group`: overlapping avatars with a "+N" overflow chip and name tooltips.
- `tree`: nested data with the WAI-ARIA tree keyboard pattern, links, badges, and a `selectable` mode with tri-state cascading checkboxes that works as a form field and with `wire:model`.
- `command` (+ `command.group`, `command.item`): a Cmd/Ctrl+K command palette with client-side filtering or Livewire `search-model` results; `AvianUI.openCommand()`.
- `editor`: a Tiptap rich text editor with a configurable toolbar (presets, a string or an array) whose buttons decide which nodes and marks the content may hold, plus `nodes` for extras. Tables, text colour and size, task lists, images and file uploads through a Livewire method (`upload`) or an endpoint (`upload-url`). Submits HTML and binds with `wire:model`; `aui-editor-set` / `aui-editor-insert` events (and `AvianUI.setEditor()` / `AvianUI.insertIntoEditor()`) replace the content or insert at the cursor. Tiptap is bundled in a separate `avian-ui-editor.js`, loaded with `<x-avian::scripts editor />`. `details` (collapsible sections), `hard-break`, `subscript` and `superscript` toolbar items; a size picker for `table` and `table-resizable` columns; `max` / `counter` for a character limit with a counter under the editor; alt text for images; a one-Tab-stop toolbar with arrow keys, Alt+F10 and Ctrl/Cmd+K for links. Syntax highlighting (`code-highlight`) and an `emoji` picker come as add-on scripts, `<x-avian::scripts editor="code emoji" />`.
- Form fields give their hint and error ids (`{id}-hint`, `{id}-error`); the editor points `aria-describedby` at them.
- Denser spacing and type on phones (640px and below) across cards, modals, drawers, tables, stats, tabs, alerts and badges.

### Fixed

- The small badge (`size="sm"`) is now visibly smaller than the default one.
- Checkbox, radio and filter chip ids generated from values with spaces or symbols are now valid HTML ids, so their labels toggle them.

## [v0.1.0](https://github.com/avia-avian/avian-ui/releases/tag/v0.1.0) - 2026-09-24

Initial pre-release.
