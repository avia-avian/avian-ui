# Release Notes

## Unreleased

### Added

- `tooltip` and `popover` components, positioned with `position: fixed` so scrolling containers never clip them, flipping to the other side when there is no room.
- `skeleton` (text, circle, rect, button) and `skeleton.table` loading placeholders, for Livewire lazy components.
- `stepper` progress indicator, horizontal or vertical, with per-step status and links.
- `wizard` (+ `wizard.step`): a form split into steps with native per-step validation and `wire:model` support.
- `date-range`: a flatpickr range picker that submits `name[from]` / `name[to]` in a fixed value format, with presets and a clear button.
- `kbd` for keyboard keys and shortcuts.
- Denser spacing and type on phones (640px and below) across cards, modals, drawers, tables, stats, tabs, alerts and badges.

### Fixed

- The small badge (`size="sm"`) is now visibly smaller than the default one.
- Checkbox, radio and filter chip ids generated from values with spaces or symbols are now valid HTML ids, so their labels toggle them.

## [v0.1.0](https://github.com/avia-avian/avian-ui/compare/...v0.1.0) - 202x-xx-xx

Initial pre-release.
