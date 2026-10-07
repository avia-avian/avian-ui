@php
    // Each sidebar entry is a page under resources/views/showcase/, keyed by its view name.
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

                go(detail) {
                    this.section = detail.key;

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
        body { margin: 0; padding: 0; background: #f5f7fa; font-family: var(--aui-font-sans); }
        [x-cloak] { display: none !important; }

        .aui-showcase { display: flex; align-items: flex-start; min-height: 100vh; }

        .aui-showcase-sidebar {
            position: sticky;
            top: 0;
            flex: 0 0 250px;
            box-sizing: border-box;
            height: 100vh;
            overflow-y: auto;
            padding: 24px 14px;
            background: #ffffff;
            border-right: 1px solid #e5e9f0;
        }
        .aui-showcase-brand { padding: 0 10px 18px; }
        .aui-showcase-brand strong { display: block; font-family: var(--aui-font-display); font-size: 18px; }
        .aui-showcase-brand span { color: #6b7280; font-size: 13px; }

        .aui-showcase-nav { display: flex; flex-direction: column; gap: 2px; }
        .aui-showcase-nav-group {
            padding: 16px 10px 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #9ca3af;
        }
        .aui-showcase-nav-group:first-child { padding-top: 0; }
        .aui-showcase-nav button {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            border: 0;
            background: transparent;
            text-align: left;
            padding: 9px 10px;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            color: #374151;
            cursor: pointer;
        }
        .aui-showcase-nav button i { width: 16px; text-align: center; color: #9ca3af; }
        .aui-showcase-nav button:hover { background: #f3f4f6; }
        .aui-showcase-nav button.is-active { background: var(--aui-primary, #16a34a); color: #fff; }
        .aui-showcase-nav button.is-active i { color: #fff; }

        .aui-showcase-content { flex: 1 1 auto; min-width: 0; padding: 30px; max-width: 1080px; margin: 0 auto; }
        .aui-showcase-content > * + * { margin-top: 24px; }
        .aui-showcase-section > * + * { margin-top: 24px; }

        .aui-showcase-code {
            margin: 0;
            padding: 14px 16px;
            background: #0f172a;
            color: #e2e8f0;
            border-radius: 10px;
            overflow-x: auto;
            font-size: 12.5px;
            line-height: 1.6;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }

        .aui-showcase-code-wrap { position: relative; }
        /* Room for the copy button, so a long first line never runs under it. */
        .aui-showcase-code-wrap .aui-showcase-code { padding-right: 96px; }
        .aui-showcase-copy {
            position: absolute;
            top: 8px;
            right: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 9px;
            border: 1px solid #334155;
            border-radius: 6px;
            background: #1e293b;
            color: #cbd5e1;
            font-family: inherit;
            font-size: 12px;
            line-height: 1;
            cursor: pointer;
        }
        .aui-showcase-copy:hover { background: #334155; color: #fff; }
        .aui-showcase-copy:focus-visible { outline: 2px solid var(--aui-primary, #16a34a); outline-offset: 2px; }
        .aui-showcase-copy.is-copied { border-color: var(--aui-primary, #16a34a); color: #fff; }

        .aui-showcase-search-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            width: calc(100% - 20px);
            margin: 0 10px 18px;
            padding: 8px 10px;
            border: 1px solid #e5e9f0;
            border-radius: 8px;
            background: #f8fafc;
            color: #6b7280;
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
        }
        .aui-showcase-search-trigger:hover { border-color: #cbd5e1; color: #374151; }
        .aui-showcase-search-trigger span { flex: 1 1 auto; text-align: left; }

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

        .aui-showcase-lead { margin: 0 0 20px; font-size: 14px; line-height: 1.65; color: #4b5563; }
        .aui-showcase-text { margin: 0 0 10px; font-size: 13.5px; line-height: 1.6; color: #4b5563; }
        .aui-showcase-note { margin: 10px 0 0; font-size: 12.5px; color: #6b7280; }
        .aui-showcase-lead code, .aui-showcase-text code, .aui-showcase-list code, .aui-showcase-note code, .aui-showcase-props code {
            padding: 1px 5px;
            background: #f1f5f9;
            border-radius: 4px;
            font-size: .92em;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
            color: #0f172a;
        }
        .aui-showcase-props code.aui-showcase-type { background: transparent; padding: 0; color: #64748b; }
        .aui-showcase-props td { vertical-align: top; }
        .aui-showcase-props td:nth-child(-n+3) { white-space: nowrap; }

        .aui-showcase-theme.is-active,
        .aui-showcase-theme.is-active:hover { background: var(--aui-primary); border-color: var(--aui-primary); color: #fff; }

        .aui-showcase-demo {
            padding: 22px;
            background: #f8fafc;
            border: 1px dashed #dbe1ea;
            border-radius: 12px;
        }

        .aui-showcase-block { margin-top: 30px; }
        .aui-showcase-heading {
            margin: 0 0 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eef1f5;
            font-family: var(--aui-font-display);
            font-size: 15px;
            color: #111827;
        }
        .aui-showcase-list { margin: 0; padding-left: 20px; font-size: 13.5px; line-height: 1.7; color: #4b5563; }
        .aui-showcase-list li + li { margin-top: 4px; }

        .aui-showcase-example + .aui-showcase-example { margin-top: 22px; }
        .aui-showcase-example-title { margin: 0 0 6px; font-size: 13.5px; font-weight: 600; color: #111827; }
        .aui-showcase-example .aui-showcase-code { margin-top: 0; }

        @media (max-width: 860px) {
            .aui-showcase { display: block; }
            .aui-showcase-sidebar { position: static; height: auto; width: auto; border-right: 0; border-bottom: 1px solid #e5e9f0; }
            .aui-showcase-content { max-width: none; padding: 16px; }
            .aui-showcase-content .aui-form-grid { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
</head>
<body>
    <div class="aui-showcase" x-data="showcase" x-on:showcase-go.window="go($event.detail)">
        <aside class="aui-showcase-sidebar">
            <div class="aui-showcase-brand">
                <strong>Avian UI</strong>
                <span>Component showcase</span>
            </div>

            <button type="button" class="aui-showcase-search-trigger" x-on:click="$dispatch('showcase-search-open')">
                <i class="fas fa-magnifying-glass" aria-hidden="true"></i>
                <span>Search</span>
                <kbd>Ctrl</kbd><kbd>Space</kbd>
            </button>

            <nav class="aui-showcase-nav">
                @foreach ($nav as $group => $items)
                    <span class="aui-showcase-nav-group">{{ $group }}</span>

                    @foreach ($items as $key => $item)
                        <button
                            type="button"
                            x-on:click="section = '{{ $key }}'; window.scrollTo(0, 0)"
                            x-bind:class="section === '{{ $key }}' ? 'is-active' : ''"
                        >
                            <i class="{{ $item['icon'] }}" aria-hidden="true"></i>
                            {{ $item['label'] }}
                        </button>
                    @endforeach
                @endforeach
            </nav>
        </aside>

        <main class="aui-showcase-content">
            @foreach ($nav as $group => $items)
                @foreach ($items as $key => $item)
                    <section
                        class="aui-showcase-section"
                        x-show="section === '{{ $key }}'"
                        x-cloak
                        data-search-key="{{ $key }}"
                        data-search-page="{{ $item['label'] }}"
                        data-search-group="{{ $group }}"
                    >
                        @include('avian-ui::docs.'.$key)
                    </section>
                @endforeach
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
