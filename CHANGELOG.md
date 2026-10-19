# Release Notes

## Unreleased

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
- `editor`: a Tiptap rich text editor with a configurable toolbar (presets, a string or an array) whose buttons decide which nodes and marks the content may hold, plus `nodes` for extras. Tables, text colour and size, task lists, images and file uploads through a Livewire method (`upload`) or an endpoint (`upload-url`). Submits HTML and binds with `wire:model`; `aui-editor-set` / `aui-editor-insert` events (and `AvianUI.setEditor()` / `AvianUI.insertIntoEditor()`) replace the content or insert at the cursor. Tiptap is bundled in a separate `avian-ui-editor.js`, loaded with `<x-avian::scripts editor />`. `details` (collapsible sections) and `hard-break` toolbar items, and `max` / `counter` for a character limit with a counter under the editor.
- Denser spacing and type on phones (640px and below) across cards, modals, drawers, tables, stats, tabs, alerts and badges.

### Fixed

- The small badge (`size="sm"`) is now visibly smaller than the default one.
- Checkbox, radio and filter chip ids generated from values with spaces or symbols are now valid HTML ids, so their labels toggle them.

## [v0.1.0](https://github.com/avia-avian/avian-ui/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
