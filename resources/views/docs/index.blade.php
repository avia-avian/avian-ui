@php
    // Each sidebar entry is a page under resources/views/docs/, keyed by its view name.
    $nav = [
        'General' => [
            'getting-started' => ['label' => 'Getting started', 'icon' => 'fas fa-house'],
        ],
        'Actions & display' => [
            'components.button' => ['label' => 'Button', 'icon' => 'fas fa-hand-pointer'],
            'components.button-group' => ['label' => 'Button group & toolbar', 'icon' => 'fas fa-grip-lines-vertical'],
            'components.badge' => ['label' => 'Badge', 'icon' => 'fas fa-certificate'],
            'components.avatar' => ['label' => 'Avatar', 'icon' => 'fas fa-circle-user'],
            'components.progress' => ['label' => 'Progress', 'icon' => 'fas fa-chart-simple'],
            'components.spinner' => ['label' => 'Spinner', 'icon' => 'fas fa-spinner'],
        ],
        'Layout' => [
            'components.page-header' => ['label' => 'Page header', 'icon' => 'fas fa-heading'],
            'components.breadcrumbs' => ['label' => 'Breadcrumbs', 'icon' => 'fas fa-angles-right'],
            'components.card' => ['label' => 'Card', 'icon' => 'fas fa-square'],
            'components.stat' => ['label' => 'Stat', 'icon' => 'fas fa-chart-line'],
            'components.accordion' => ['label' => 'Accordion', 'icon' => 'fas fa-bars-staggered'],
            'components.divider' => ['label' => 'Divider', 'icon' => 'fas fa-grip-lines'],
        ],
        'Forms' => [
            'forms.form' => ['label' => 'Form & layout', 'icon' => 'fas fa-pen-to-square'],
            'forms.field' => ['label' => 'Field, label & error', 'icon' => 'fas fa-tag'],
            'forms.input' => ['label' => 'Input', 'icon' => 'fas fa-i-cursor'],
            'forms.textarea' => ['label' => 'Textarea', 'icon' => 'fas fa-align-left'],
            'forms.select' => ['label' => 'Select', 'icon' => 'fas fa-list'],
            'forms.searchable-select' => ['label' => 'Searchable select', 'icon' => 'fas fa-magnifying-glass'],
            'forms.multi-select' => ['label' => 'Multi select', 'icon' => 'fas fa-list-check'],
            'forms.datepicker' => ['label' => 'Datepicker', 'icon' => 'fas fa-calendar-days'],
            'forms.file' => ['label' => 'File', 'icon' => 'fas fa-paperclip'],
            'forms.checkbox' => ['label' => 'Checkbox', 'icon' => 'fas fa-square-check'],
            'forms.radio' => ['label' => 'Radio', 'icon' => 'fas fa-circle-dot'],
            'forms.switch' => ['label' => 'Switch', 'icon' => 'fas fa-toggle-on'],
            'forms.slider' => ['label' => 'Slider', 'icon' => 'fas fa-sliders'],
            'forms.filter-chip' => ['label' => 'Filter chip', 'icon' => 'fas fa-filter'],
        ],
        'Data & navigation' => [
            'components.table' => ['label' => 'Table', 'icon' => 'fas fa-table'],
            'components.datalist' => ['label' => 'Datalist', 'icon' => 'fas fa-grip'],
            'components.timeline' => ['label' => 'Timeline', 'icon' => 'fas fa-timeline'],
            'components.pagination' => ['label' => 'Pagination', 'icon' => 'fas fa-ellipsis'],
            'components.tabs' => ['label' => 'Tabs', 'icon' => 'fas fa-folder'],
            'components.dropdown' => ['label' => 'Dropdown', 'icon' => 'fas fa-caret-down'],
        ],
        'Overlays & feedback' => [
            'components.modal' => ['label' => 'Modal', 'icon' => 'fas fa-window-restore'],
            'components.drawer' => ['label' => 'Drawer', 'icon' => 'fas fa-table-columns'],
            'components.confirm' => ['label' => 'Confirm dialog', 'icon' => 'fas fa-circle-question'],
            'components.toast' => ['label' => 'Toast', 'icon' => 'fas fa-bell'],
            'components.alert' => ['label' => 'Alert', 'icon' => 'fas fa-circle-info'],
            'components.empty' => ['label' => 'Empty state', 'icon' => 'fas fa-inbox'],
        ],
    ];

    // The same pages as one flat list, for the previous / next links.
    $pages = [];

    foreach ($nav as $group => $items) {
        foreach ($items as $key => $item) {
            $pages[] = [$group, $key, $item];
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="emerald-green">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Avian UI</title>

    {{--
        Fonts, icons and Alpine are pulled from a CDN for this documentation
        page only. The components themselves never load anything remote: they
        ship their own CSS and JS, and leave fonts, icons and Alpine to the host app.
    --}}
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|outfit:600,700,800">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <x-avian::styles />

    {{-- The package script registers its Alpine components, so it loads first. --}}
    <x-avian::scripts />
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- The datepicker leaves flatpickr to the host app; the workbench loads it the documented way. --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.flatpickr-input').forEach((input) => {
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
            });
        });
    </script>

    <script>
        // Copy button on every example code block. Clipboard API first; the
        // textarea fallback covers plain-http hosts other than localhost and
        // browsers that deny the clipboard permission.
        function copyWithTextarea(text) {
            const area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();

            const ok = document.execCommand('copy');
            area.remove();

            return ok ? Promise.resolve() : Promise.reject(new Error('Copy failed'));
        }

        document.addEventListener('alpine:init', () => {
            Alpine.data('showcaseCopy', () => ({
                copied: false,
                timer: null,

                copy() {
                    const text = this.$refs.code.textContent;
                    const write = navigator.clipboard && window.isSecureContext
                        ? navigator.clipboard.writeText(text).catch(() => copyWithTextarea(text))
                        : copyWithTextarea(text);

                    write.then(() => {
                        this.copied = true;
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.copied = false, 2000);
                    });
                },
            }));

            // The page shell: which section is showing, and jumping to a
            // search hit inside it.
            Alpine.data('showcase', () => ({
                section: 'getting-started',
                menu: false,

                go(detail) {
                    this.section = detail.key;
                    this.menu = false;

                    // Wait for x-show to reveal the section before measuring it.
                    this.$nextTick(() => requestAnimationFrame(() => {
                        const target = detail.target;

                        if (! target) {
                            window.scrollTo(0, 0);

                            return;
                        }

                        target.scrollIntoView({ block: 'center' });
                        target.classList.remove('is-search-hit');
                        void target.offsetWidth; // restart the highlight animation
                        target.classList.add('is-search-hit');
                        setTimeout(() => target.classList.remove('is-search-hit'), 1600);
                    }));
                },
            }));

            /*
             * Ctrl+Space search palette. Every section is already in the DOM
             * (only hidden by x-show), so the index is read from it once:
             * page names, example titles and prop names.
             */
            Alpine.data('showcaseSearch', () => {
                // Kept out of the reactive state: it never changes and holds DOM nodes.
                const index = [];
                const weight = { page: 0, example: 1, prop: 2 };

                return {
                    open: false,
                    query: '',
                    active: 0,
                    returnFocus: null,

                    init() {
                        document.querySelectorAll('[data-search-key]').forEach((section) => {
                            const page = {
                                key: section.dataset.searchKey,
                                page: section.dataset.searchPage,
                                group: section.dataset.searchGroup,
                            };

                            index.push({ ...page, type: 'page', label: page.page, target: null });

                            section.querySelectorAll('[data-search-example]').forEach((el) => {
                                index.push({ ...page, type: 'example', label: el.dataset.searchExample, target: el });
                            });

                            section.querySelectorAll('[data-search-prop]').forEach((el) => {
                                index.push({ ...page, type: 'prop', label: el.dataset.searchProp, target: el });
                            });
                        });

                        index.forEach((item, i) => {
                            item.id = 'aui-showcase-search-' + i;
                            item.order = i;
                        });
                    },

                    /* Pages first, then exact example/prop names, then other
                       examples, then other props; within each, a match at the
                       start of the label beats one in the middle. */
                    get results() {
                        const term = this.query.trim().toLowerCase();

                        if (term === '') {
                            return index.filter((item) => item.type === 'page');
                        }

                        return index
                            .map((item) => {
                                const label = item.label.toLowerCase();
                                const at = label.indexOf(term);
                                // A page also matches on its group, so "forms" lists every form control.
                                const inGroup = item.type === 'page' && item.group.toLowerCase().includes(term);

                                if (at === -1 && ! inGroup) {
                                    return null;
                                }

                                const tier = item.type === 'page' ? 0 : label === term ? 1 : weight[item.type] + 1;

                                return { item, score: tier * 3 + (at === 0 ? 0 : at > 0 ? 1 : 2) };
                            })
                            .filter(Boolean)
                            .sort((a, b) => a.score - b.score || a.item.order - b.item.order)
                            .slice(0, 30)
                            .map((result) => result.item);
                    },

                    show() {
                        this.returnFocus = document.activeElement;
                        this.query = '';
                        this.active = 0;
                        this.open = true;
                        this.$nextTick(() => this.$refs.input.focus());
                    },

                    hide() {
                        this.open = false;

                        if (this.returnFocus && this.returnFocus.focus) {
                            this.returnFocus.focus();
                        }
                    },

                    toggle() {
                        this.open ? this.hide() : this.show();
                    },

                    move(step) {
                        const results = this.results;

                        if (results.length === 0) {
                            return;
                        }

                        this.active = (this.active + step + results.length) % results.length;

                        this.$nextTick(() => {
                            const row = document.getElementById(results[this.active].id);

                            if (row) {
                                row.scrollIntoView({ block: 'nearest' });
                            }
                        });
                    },

                    choose(item) {
                        if (! item) {
                            return;
                        }

                        this.open = false;
                        this.$dispatch('showcase-go', { key: item.key, target: item.target });
                    },
                };
            });
        });
    </script>

    <style>
        body { margin: 0; padding: 0; background: #ffffff; color: #1f2937; font-family: var(--aui-font-sans); -webkit-font-smoothing: antialiased; }
        [x-cloak] { display: none !important; }

        .aui-showcase { display: flex; align-items: flex-start; min-height: 100vh; }

        /* Sidebar */
        .aui-showcase-sidebar {
            position: sticky;
            top: 0;
            flex: 0 0 264px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            height: 100vh;
            background: #fafbfc;
            border-right: 1px solid #eceff3;
        }
        .aui-showcase-brand { display: flex; align-items: center; gap: 10px; padding: 22px 20px 18px; }
        .aui-showcase-logo {
            display: grid;
            place-items: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--aui-primary, #16a34a);
            color: #fff;
            font-family: var(--aui-font-display);
            font-size: 15px;
            font-weight: 800;
        }
        .aui-showcase-brand div strong { display: block; font-family: var(--aui-font-display); font-size: 16px; line-height: 1.2; color: #111827; }
        .aui-showcase-brand div span { font-size: 12px; color: #6b7280; }

        .aui-showcase-search-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0 16px 8px;
            padding: 8px 10px;
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #fff;
            color: #6b7280;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
            transition: border-color .15s, color .15s;
        }
        .aui-showcase-search-trigger:hover { border-color: #cbd5e1; color: #374151; }
        .aui-showcase-search-trigger span { flex: 1 1 auto; text-align: left; }

        .aui-showcase-nav { flex: 1 1 auto; overflow-y: auto; padding: 8px 12px 28px; }
        .aui-showcase-nav-group {
            display: block;
            padding: 18px 10px 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #9ca3af;
        }
        .aui-showcase-nav button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            margin-top: 1px;
            padding: 7px 10px;
            border: 0;
            border-radius: 7px;
            background: transparent;
            font-family: inherit;
            font-size: 13.5px;
            text-align: left;
            color: #4b5563;
            cursor: pointer;
        }
        .aui-showcase-nav button i { width: 16px; font-size: 13px; text-align: center; color: #9ca3af; }
        .aui-showcase-nav button:hover { background: #f1f3f6; color: #111827; }
        .aui-showcase-nav button.is-active {
            background: var(--aui-primary-light, #dcfce7);
            color: var(--aui-primary-darker, #166534);
            font-weight: 600;
        }
        .aui-showcase-nav button.is-active i { color: var(--aui-primary, #16a34a); }

        /* Mobile top bar (hidden on desktop) */
        .aui-showcase-topbar { display: none; }
        .aui-showcase-backdrop { display: none; }

        /* Content */
        .aui-showcase-content { flex: 1 1 auto; min-width: 0; }
        .aui-showcase-section { box-sizing: border-box; max-width: 920px; margin: 0 auto; padding: 48px 56px 64px; }

        .aui-showcase-header { margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid #eef1f5; }
        .aui-showcase-eyebrow {
            margin: 0 0 8px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--aui-primary, #16a34a);
        }
        .aui-showcase-title { margin: 0; font-family: var(--aui-font-display); font-size: 32px; font-weight: 800; line-height: 1.2; letter-spacing: -.01em; color: #111827; }
        .aui-showcase-subtitle { margin: 8px 0 0; font-size: 16px; line-height: 1.5; color: #6b7280; }

        .aui-showcase-lead { margin: 0 0 24px; font-size: 15px; line-height: 1.7; color: #374151; }
        .aui-showcase-text { margin: 0 0 12px; font-size: 14px; line-height: 1.65; color: #4b5563; }
        .aui-showcase-note { margin: 12px 0 0; font-size: 13px; color: #6b7280; }
        .aui-showcase-lead code, .aui-showcase-text code, .aui-showcase-list code, .aui-showcase-note code, .aui-showcase-props code, .aui-showcase-demo .aui-label code {
            padding: 1px 6px;
            background: #f1f5f9;
            border: 1px solid #e8edf3;
            border-radius: 5px;
            font-size: .88em;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            color: #0f172a;
        }
        .aui-showcase-props code.aui-showcase-type { background: transparent; border: 0; padding: 0; color: #64748b; }
        .aui-showcase-props td { vertical-align: top; font-size: 13.5px; line-height: 1.55; }
        .aui-showcase-props td:nth-child(-n+3) { white-space: nowrap; }

        .aui-showcase-theme.is-active,
        .aui-showcase-theme.is-active:hover { background: var(--aui-primary); border-color: var(--aui-primary); color: #fff; }

        /* Live demo surface */
        .aui-showcase-demo {
            padding: 28px;
            background-color: #fbfcfd;
            background-image: radial-gradient(#e6eaf0 1px, transparent 1px);
            background-size: 18px 18px;
            border: 1px solid #e5e9f0;
            border-radius: 12px;
        }

        /* Sections: How it works, Props, Examples… */
        .aui-showcase-block { margin-top: 48px; }
        .aui-showcase-heading {
            margin: 0 0 16px;
            font-family: var(--aui-font-display);
            font-size: 20px;
            font-weight: 700;
            color: #111827;
        }
        .aui-showcase-list { margin: 0; padding-left: 20px; font-size: 14px; line-height: 1.7; color: #4b5563; }
        .aui-showcase-list li + li { margin-top: 6px; }
        .aui-showcase-list li::marker { color: #9ca3af; }

        .aui-showcase-example + .aui-showcase-example { margin-top: 32px; }
        .aui-showcase-example-title { margin: 0 0 6px; font-size: 15px; font-weight: 600; color: #111827; }
        .aui-showcase-example-title + .aui-showcase-code-wrap { margin-top: 10px; }

        /* Code blocks */
        .aui-showcase-code-wrap { overflow: hidden; border: 1px solid #1e293b; border-radius: 10px; background: #0f172a; }
        .aui-showcase-code-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 6px 8px 6px 14px;
            background: #1e293b;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .aui-showcase-code {
            margin: 0;
            padding: 16px 18px;
            color: #e2e8f0;
            overflow-x: auto;
            font-size: 12.5px;
            line-height: 1.65;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        .aui-showcase-copy {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 9px;
            border: 0;
            border-radius: 6px;
            background: transparent;
            color: #cbd5e1;
            font-family: inherit;
            font-size: 12px;
            font-weight: 500;
            letter-spacing: 0;
            text-transform: none;
            line-height: 1;
            cursor: pointer;
        }
        .aui-showcase-copy:hover { background: #334155; color: #fff; }
        .aui-showcase-copy:focus-visible { outline: 2px solid var(--aui-primary, #16a34a); outline-offset: 2px; }
        .aui-showcase-copy.is-copied { color: #86efac; }

        /* Previous / next page */
        .aui-showcase-pager { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 64px; padding-top: 24px; border-top: 1px solid #eef1f5; }
        .aui-showcase-pager button {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 14px 16px;
            border: 1px solid #e5e9f0;
            border-radius: 10px;
            background: #fff;
            font-family: inherit;
            text-align: left;
            cursor: pointer;
            transition: border-color .15s;
        }
        .aui-showcase-pager button:hover { border-color: var(--aui-primary, #16a34a); }
        .aui-showcase-pager button.is-next { grid-column: 2; text-align: right; }
        .aui-showcase-pager small { font-size: 12px; color: #6b7280; }
        .aui-showcase-pager strong { font-size: 14.5px; color: #111827; }

        .aui-showcase kbd, .aui-showcase-search kbd {
            padding: 1px 5px;
            border: 1px solid #e2e8f0;
            border-bottom-width: 2px;
            border-radius: 4px;
            background: #fff;
            font-family: inherit;
            font-size: 11px;
            color: #64748b;
        }

        /* Search palette */
        .aui-showcase-search-overlay {
            position: fixed;
            inset: 0;
            z-index: 1000;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 12vh 16px 16px;
            background: rgba(15, 23, 42, .45);
        }
        .aui-showcase-search {
            width: 100%;
            max-width: 640px;
            overflow: hidden;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 24px 60px rgba(15, 23, 42, .3);
        }
        .aui-showcase-search-field {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid #eef1f5;
            color: #9ca3af;
        }
        .aui-showcase-search-field input {
            flex: 1 1 auto;
            min-width: 0;
            border: 0;
            outline: none;
            font-family: inherit;
            font-size: 15px;
            color: #111827;
        }
        .aui-showcase-search-results { margin: 0; padding: 6px; list-style: none; max-height: 360px; overflow-y: auto; }
        .aui-showcase-search-results li[role="option"] {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 13.5px;
            color: #111827;
            cursor: pointer;
        }
        .aui-showcase-search-results li.is-active { background: #f1f5f9; }
        .aui-showcase-search-type { font-size: 10.5px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #94a3b8; }
        .aui-showcase-search-label { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .aui-showcase-search-page { font-size: 12px; color: #9ca3af; white-space: nowrap; }
        .aui-showcase-search-empty { padding: 18px 10px; text-align: center; font-size: 13.5px; color: #6b7280; }
        .aui-showcase-search-foot {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            padding: 8px 14px;
            border-top: 1px solid #eef1f5;
            font-size: 11.5px;
            color: #6b7280;
        }

        @keyframes aui-showcase-hit-row { from { background: #fef08a; } to { background: transparent; } }
        @keyframes aui-showcase-hit-block { from { outline-color: #facc15; } to { outline-color: transparent; } }
        tr.is-search-hit > td { animation: aui-showcase-hit-row 1.6s ease-out; }
        .aui-showcase-example.is-search-hit {
            outline: 3px solid transparent;
            outline-offset: 6px;
            border-radius: 6px;
            animation: aui-showcase-hit-block 1.6s ease-out;
        }

        @media (max-width: 1100px) {
            .aui-showcase-section { padding: 40px 36px 56px; }
        }

        @media (max-width: 860px) {
            .aui-showcase { display: block; }

            .aui-showcase-topbar {
                position: sticky;
                top: 0;
                z-index: 40;
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                background: rgba(255, 255, 255, .92);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid #eceff3;
            }
            .aui-showcase-topbar strong { flex: 1 1 auto; font-family: var(--aui-font-display); font-size: 15px; }
            .aui-showcase-topbar button {
                display: grid;
                place-items: center;
                width: 36px;
                height: 36px;
                border: 1px solid #e5e9f0;
                border-radius: 8px;
                background: #fff;
                color: #374151;
                cursor: pointer;
            }

            .aui-showcase-sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                z-index: 60;
                width: 280px;
                max-width: 85vw;
                visibility: hidden;
                transform: translateX(-100%);
                transition: transform .2s ease, visibility .2s;
            }
            .aui-showcase.is-menu-open .aui-showcase-sidebar { visibility: visible; transform: none; box-shadow: 0 20px 50px rgba(15, 23, 42, .25); }
            .aui-showcase.is-menu-open .aui-showcase-backdrop {
                position: fixed;
                inset: 0;
                z-index: 50;
                display: block;
                background: rgba(15, 23, 42, .4);
            }

            .aui-showcase-section { padding: 28px 16px 48px; }
            .aui-showcase-title { font-size: 26px; }
            .aui-showcase-demo { padding: 18px; }
            .aui-showcase-content .aui-form-grid { grid-template-columns: minmax(0, 1fr); }
            .aui-showcase-pager { grid-template-columns: 1fr; }
            .aui-showcase-pager button.is-next { grid-column: auto; }
        }
    </style>
</head>
<body>
    <div
        class="aui-showcase"
        x-data="showcase"
        x-bind:class="{ 'is-menu-open': menu }"
        x-on:showcase-go.window="go($event.detail)"
        x-on:keydown.escape.window="menu = false"
    >
        <div class="aui-showcase-topbar">
            <button type="button" x-on:click="menu = true" aria-label="Open navigation">
                <i class="fas fa-bars" aria-hidden="true"></i>
            </button>
            <strong>Avian UI</strong>
            <button type="button" x-on:click="$dispatch('showcase-search-open')" aria-label="Search">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
            </button>
        </div>

        <div class="aui-showcase-backdrop" x-on:click="menu = false"></div>

        <aside class="aui-showcase-sidebar">
            <div class="aui-showcase-brand">
                <span class="aui-showcase-logo" aria-hidden="true">A</span>
                <div>
                    <strong>Avian UI</strong>
                    <span>Blade component library</span>
                </div>
            </div>

            <button type="button" class="aui-showcase-search-trigger" x-on:click="$dispatch('showcase-search-open')">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <span>Search docs</span>
                <kbd>Ctrl</kbd><kbd>Space</kbd>
            </button>

            <nav class="aui-showcase-nav">
                @foreach ($nav as $group => $items)
                    <span class="aui-showcase-nav-group">{{ $group }}</span>

                    @foreach ($items as $key => $item)
                        <button
                            type="button"
                            x-on:click="go({ key: '{{ $key }}' })"
                            x-bind:class="section === '{{ $key }}' ? 'is-active' : ''"
                            x-bind:aria-current="section === '{{ $key }}' ? 'page' : null"
                        >
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                @endforeach
            </nav>
        </aside>

        <main class="aui-showcase-content">
            @foreach ($pages as $index => [$group, $key, $item])
                <section
                    class="aui-showcase-section"
                    x-show="section === '{{ $key }}'"
                    x-cloak
                    data-search-key="{{ $key }}"
                    data-search-page="{{ $item['label'] }}"
                    data-search-group="{{ $group }}"
                >
                    @include('avian-ui::docs.'.$key)

                    <nav class="aui-showcase-pager" aria-label="Pages">
                        @if ($previous = $pages[$index - 1] ?? null)
                            <button type="button" x-on:click="go({ key: '{{ $previous[1] }}' })">
                                <small><i class="fas fa-arrow-left" aria-hidden="true"></i> Previous</small>
                                <strong>{{ $previous[2]['label'] }}</strong>
                            </button>
                        @endif

                        @if ($next = $pages[$index + 1] ?? null)
                            <button type="button" class="is-next" x-on:click="go({ key: '{{ $next[1] }}' })">
                                <small>Next <i class="fas fa-arrow-right" aria-hidden="true"></i></small>
                                <strong>{{ $next[2]['label'] }}</strong>
                            </button>
                        @endif
                    </nav>
                </section>
            @endforeach
        </main>
    </div>

    <div
        x-data="showcaseSearch"
        x-on:keydown.ctrl.space.window.prevent="toggle()"
        x-on:keydown.escape.window="if (open) hide()"
        x-on:showcase-search-open.window="show()"
    >
        <div class="aui-showcase-search-overlay" x-show="open" x-cloak x-on:click.self="hide()">
            <div class="aui-showcase-search" role="dialog" aria-modal="true" aria-label="Search the showcase">
                <div class="aui-showcase-search-field">
                    <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                    <input
                        type="text"
                        x-ref="input"
                        x-model="query"
                        x-on:input="active = 0"
                        x-on:keydown.down.prevent="move(1)"
                        x-on:keydown.up.prevent="move(-1)"
                        x-on:keydown.enter.prevent="choose(results[active])"
                        placeholder="Search components, props and examples"
                        role="combobox"
                        aria-expanded="true"
                        aria-controls="aui-showcase-search-results"
                        aria-autocomplete="list"
                        x-bind:aria-activedescendant="results[active] ? results[active].id : null"
                        autocomplete="off"
                        spellcheck="false"
                    >
                    <kbd>Esc</kbd>
                </div>

                <ul id="aui-showcase-search-results" class="aui-showcase-search-results" role="listbox">
                    <template x-for="(item, i) in results" :key="item.id">
                        <li
                            role="option"
                            x-bind:id="item.id"
                            x-bind:aria-selected="i === active"
                            x-bind:class="{ 'is-active': i === active }"
                            x-on:mousemove="active = i"
                            x-on:click="choose(item)"
                        >
                            <span class="aui-showcase-search-type" x-text="{ page: 'Page', example: 'Example', prop: 'Prop' }[item.type]"></span>
                            <span class="aui-showcase-search-label" x-text="item.label"></span>
                            <span class="aui-showcase-search-page" x-text="item.type === 'page' ? item.group : item.page"></span>
                        </li>
                    </template>

                    <li class="aui-showcase-search-empty" role="presentation" x-show="results.length === 0">
                        No results for "<span x-text="query.trim()"></span>"
                    </li>
                </ul>

                <div class="aui-showcase-search-foot">
                    <span><kbd>↑</kbd> <kbd>↓</kbd> to move</span>
                    <span><kbd>Enter</kbd> to open</span>
                    <span><kbd>Ctrl</kbd> + <kbd>Space</kbd> to toggle</span>
                </div>
            </div>
        </div>
    </div>

    <x-avian::modal name="demo" title="New record" size="lg">
        <x-avian::input name="title" label="Title" />
        <x-avian::textarea name="description" label="Description" rows="3" />

        <x-slot:footer>
            <x-avian::button variant="light" x-on:click="hide()">Cancel</x-avian::button>
            <x-avian::button icon="fas fa-check">Create</x-avian::button>
        </x-slot:footer>
    </x-avian::modal>

    {{-- One shared confirm dialog for the whole page, as an app layout would have. --}}
    <x-avian::confirm />
    <x-avian::toasts />
</body>
</html>
