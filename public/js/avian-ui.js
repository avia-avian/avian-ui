/*!
 * Avian UI - Alpine behaviours
 *
 * Alpine itself is never bundled here. This file only adds components to it, so
 * it has to run BEFORE Alpine starts: put this script tag above Alpine's own.
 * A Livewire application loads Alpine itself, at the end of the page, so a tag
 * in <head> is already early enough.
 *
 * The factories are also published as plain globals, which is what x-data falls
 * back to for markup that Alpine initialises later (a lazily injected fragment,
 * for instance).
 */
(function () {
    'use strict';

    /* Body scroll locking, reference counted so nested overlays behave. */
    var locks = 0;

    function lockScroll() {
        locks += 1;
        document.body.classList.add('aui-scroll-locked');
    }

    function unlockScroll() {
        locks = Math.max(0, locks - 1);

        if (locks === 0) {
            document.body.classList.remove('aui-scroll-locked');
        }
    }

    function releaseScroll() {
        locks = 0;
        overlays.length = 0;
        document.body.classList.remove('aui-scroll-locked');
    }

    /* Read the modal name out of a browser event, however it was dispatched. */
    function eventName(event) {
        var detail = event.detail;

        if (typeof detail === 'string') {
            return detail;
        }

        if (detail && typeof detail === 'object') {
            if (typeof detail.name === 'string') {
                return detail.name;
            }

            /* Livewire wraps positional dispatch params in an array. */
            if (Array.isArray(detail.params) && typeof detail.params[0] === 'string') {
                return detail.params[0];
            }
        }

        return null;
    }

    /* Ids of the open modals and drawers, oldest first, so Esc only closes the
       top one. Ids rather than the components themselves: Alpine hands each
       method a different proxy, so `this` never compares equal. */
    var overlays = [];
    var overlayIds = 0;

    /* The mounted <x-avian::confirm>, if the layout has one. */
    var confirmHost = null;

    function confirmDialog(options) {
        options = options || {};

        if (confirmHost) {
            return confirmHost.ask(options);
        }

        return Promise.resolve(window.confirm(options.message || options.title || 'Are you sure?'));
    }

    /*
     * The mounted <x-avian::toasts>, if the layout has one. Toasts raised
     * before it starts (a script that runs ahead of Alpine) wait in a queue.
     */
    var toastHost = null;
    var toastQueue = [];

    function toast(options, variant) {
        options = typeof options === 'string' ? { message: options } : Object.assign({}, options || {});

        if (variant) {
            options.variant = variant;
        }

        if (toastHost) {
            return toastHost.add(options);
        }

        toastQueue.push(options);

        return null;
    }

    /*
     * Livewire requests in flight, so a busy confirm dialog can wait for the
     * work its yes started. Livewire is optional: without it nothing counts.
     */
    var livewireBusy = 0;
    var livewireIdle = [];
    var livewireTracked = false;

    function trackLivewire() {
        if (livewireTracked || ! window.Livewire || typeof window.Livewire.hook !== 'function') {
            return;
        }

        livewireTracked = true;

        window.Livewire.hook('request', function (request) {
            var settled = false;

            livewireBusy++;

            function done() {
                if (settled) {
                    return;
                }

                settled = true;
                livewireBusy--;

                if (livewireBusy === 0) {
                    livewireIdle.splice(0).forEach(function (callback) {
                        callback();
                    });
                }
            }

            if (request && typeof request.succeed === 'function') {
                request.succeed(done);
            }

            if (request && typeof request.fail === 'function') {
                request.fail(done);
            }
        });
    }

    document.addEventListener('livewire:init', trackLivewire);
    trackLivewire();

    /* Resolves once no Livewire request is running. The short wait first lets
       a request the caller just triggered (Livewire batches them) get going. */
    function livewireSettled() {
        return new Promise(function (resolve) {
            setTimeout(function () {
                if (livewireBusy === 0) {
                    resolve();
                } else {
                    livewireIdle.push(resolve);
                }
            }, 50);
        });
    }

    /* data-aui-confirm-* attributes → confirm() options. */
    function confirmOptions(element) {
        var data = element.dataset;

        return {
            message: data.auiConfirm,
            title: data.auiConfirmTitle,
            confirmText: data.auiConfirmText,
            cancelText: data.auiCancelText,
            variant: data.auiConfirmVariant,
            loading: data.auiConfirmLoading === undefined ? undefined : data.auiConfirmLoading !== 'false',
        };
    }

    /*
     * Hold back clicks on [data-aui-confirm] and submits of form[data-aui-confirm]
     * until the user agrees, then replay them. Both listeners run in the capture
     * phase on the document, so they fire before any handler on the element
     * itself (wire:click, wire:submit, x-on:click, a link's navigation).
     */
    document.addEventListener('click', function (event) {
        var element = event.target && event.target.closest ? event.target.closest('[data-aui-confirm]') : null;

        if (! element || element.tagName === 'FORM' || element.disabled || element.getAttribute('aria-disabled') === 'true') {
            return;
        }

        if (element._auiConfirmed) {
            element._auiConfirmed = false;

            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        confirmDialog(confirmOptions(element)).then(function (ok) {
            if (ok) {
                element._auiConfirmed = true;
                element.click();
            }
        });
    }, true);

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (! form || ! form.matches || ! form.matches('form[data-aui-confirm]')) {
            return;
        }

        if (form._auiConfirmed) {
            form._auiConfirmed = false;

            return;
        }

        var submitter = event.submitter || null;

        event.preventDefault();
        event.stopImmediatePropagation();

        confirmDialog(confirmOptions(form)).then(function (ok) {
            if (! ok) {
                return;
            }

            form._auiConfirmed = true;

            if (form.requestSubmit) {
                form.requestSubmit(submitter && submitter.form === form ? submitter : undefined);
            } else {
                form.submit();
                form._auiConfirmed = false;
            }
        });
    }, true);

    /* Fixed-position placement for a combobox dropdown anchored to its trigger.
       Opens below unless the list does not fit there and there is more room
       above, and caps the height to the room on the chosen side so the list
       scrolls instead of running off the viewport. The natural height is read
       from the list's scrollHeight, which ignores the current cap. */
    var DROPDOWN_GAP = 6;
    var DROPDOWN_MARGIN = 8;
    var DROPDOWN_MAX_HEIGHT = 280;

    function placeDropdown(trigger, dropdown, list) {
        var rect = trigger.getBoundingClientRect();
        var natural = DROPDOWN_MAX_HEIGHT;

        if (dropdown && list) {
            var chrome = dropdown.offsetHeight - list.offsetHeight;
            natural = Math.min(DROPDOWN_MAX_HEIGHT, chrome + list.scrollHeight);
        }

        var below = window.innerHeight - rect.bottom - DROPDOWN_GAP - DROPDOWN_MARGIN;
        var above = rect.top - DROPDOWN_GAP - DROPDOWN_MARGIN;
        var upwards = natural > below && above > below;
        var maxHeight = Math.max(0, Math.min(DROPDOWN_MAX_HEIGHT, upwards ? above : below));

        return {
            top: upwards ? rect.top - DROPDOWN_GAP - Math.min(natural, maxHeight) : rect.bottom + DROPDOWN_GAP,
            left: rect.left,
            width: rect.width,
            maxHeight: maxHeight,
        };
    }

    /* Keeps an open dropdown anchored while its content changes size: a
       client-side filter, a server search result morphing in, chips wrapping. */
    function observeDropdown(component) {
        if (typeof ResizeObserver === 'undefined' || ! component.$refs.list) {
            return null;
        }

        var observer = new ResizeObserver(function () {
            if (component.open) {
                component.reposition();
            }
        });

        observer.observe(component.$refs.list);

        return observer;
    }

    /* Fixed-position placement for a floating panel (tooltip, popover)
       anchored to its trigger. Tries the asked side first, flips to the
       opposite one when it does not fit, then keeps the panel inside the
       viewport. The arrow offset is where the trigger's centre lands on the
       panel, so the arrow still points at it after the panel was nudged. */
    var FLOATING_MARGIN = 8;
    var FLOATING_OPPOSITE = { top: 'bottom', bottom: 'top', left: 'right', right: 'left' };

    function placeFloating(trigger, panel, placement, gap) {
        /* Called right after opening, x-show may not have revealed the panel
           yet; a hidden panel measures 0 x 0, so reveal it first. */
        if (panel.style.display === 'none') {
            panel.style.removeProperty('display');
        }

        var rect = trigger.getBoundingClientRect();
        var width = panel.offsetWidth;
        var height = panel.offsetHeight;
        var viewWidth = window.innerWidth;
        var viewHeight = window.innerHeight;
        var side = FLOATING_OPPOSITE[placement] ? placement : 'top';

        function fits(candidate) {
            switch (candidate) {
                case 'top': return rect.top - gap - height >= FLOATING_MARGIN;
                case 'bottom': return rect.bottom + gap + height <= viewHeight - FLOATING_MARGIN;
                case 'left': return rect.left - gap - width >= FLOATING_MARGIN;
                default: return rect.right + gap + width <= viewWidth - FLOATING_MARGIN;
            }
        }

        if (! fits(side) && fits(FLOATING_OPPOSITE[side])) {
            side = FLOATING_OPPOSITE[side];
        }

        var top;
        var left;

        if (side === 'top' || side === 'bottom') {
            top = side === 'top' ? rect.top - gap - height : rect.bottom + gap;
            left = rect.left + rect.width / 2 - width / 2;
        } else {
            left = side === 'left' ? rect.left - gap - width : rect.right + gap;
            top = rect.top + rect.height / 2 - height / 2;
        }

        left = Math.max(FLOATING_MARGIN, Math.min(left, viewWidth - width - FLOATING_MARGIN));
        top = Math.max(FLOATING_MARGIN, Math.min(top, viewHeight - height - FLOATING_MARGIN));

        return {
            top: top,
            left: left,
            placement: side,
            arrow: side === 'top' || side === 'bottom'
                ? rect.left + rect.width / 2 - left
                : rect.top + rect.height / 2 - top,
        };
    }

    /* Keeps a fixed panel on its trigger while anything scrolls (capture
       phase, since scroll events do not bubble) or the window resizes. */
    function followAnchor(component) {
        component.follow = function () {
            if (component.open) {
                component.reposition();
            }
        };

        window.addEventListener('scroll', component.follow, true);
        window.addEventListener('resize', component.follow);
    }

    function unfollowAnchor(component) {
        window.removeEventListener('scroll', component.follow, true);
        window.removeEventListener('resize', component.follow);
    }

    /* Date helpers for the date range presets, all at local midnight. */
    function startOfDay(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    function addDays(date, days) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate() + days);
    }

    function pad(number) {
        return (number < 10 ? '0' : '') + number;
    }

    /* Fallback formatter for when flatpickr is not on the page: handles the
       Y, m, d, H and i tokens, which is what a value format is made of. */
    function formatDateValue(date, format) {
        return format.replace(/[YmdHi]/g, function (token) {
            switch (token) {
                case 'Y': return String(date.getFullYear());
                case 'm': return pad(date.getMonth() + 1);
                case 'd': return pad(date.getDate());
                case 'H': return pad(date.getHours());
                default: return pad(date.getMinutes());
            }
        });
    }

    var DATE_PRESETS = {
        today: function (today) {
            return [today, today];
        },
        yesterday: function (today) {
            var day = addDays(today, -1);

            return [day, day];
        },
        last_7_days: function (today) {
            return [addDays(today, -6), today];
        },
        last_30_days: function (today) {
            return [addDays(today, -29), today];
        },
        this_month: function (today) {
            return [new Date(today.getFullYear(), today.getMonth(), 1), new Date(today.getFullYear(), today.getMonth() + 1, 0)];
        },
        last_month: function (today) {
            return [new Date(today.getFullYear(), today.getMonth() - 1, 1), new Date(today.getFullYear(), today.getMonth(), 0)];
        },
        this_year: function (today) {
            return [new Date(today.getFullYear(), 0, 1), new Date(today.getFullYear(), 11, 31)];
        },
    };

    /* Clipboard API first; the textarea fallback covers plain-http hosts
       other than localhost and browsers that deny the clipboard permission. */
    function copyWithTextarea(text) {
        var area = document.createElement('textarea');

        area.value = text;
        area.setAttribute('readonly', '');
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild(area);
        area.select();

        var ok = false;

        try {
            ok = document.execCommand('copy');
        } catch (e) {}

        area.remove();

        return ok ? Promise.resolve() : Promise.reject(new Error('Copy failed'));
    }

    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text).catch(function () {
                return copyWithTextarea(text);
            });
        }

        return copyWithTextarea(text);
    }

    /* "mod+k", "ctrl+shift+p", "alt+/" → { key, ctrl, meta, shift, alt, mod }.
       `mod` is Cmd on a Mac and Ctrl elsewhere. */
    function parseShortcut(text) {
        if (typeof text !== 'string' || text.trim() === '') {
            return null;
        }

        var parts = text.toLowerCase().split('+').map(function (part) {
            return part.trim();
        });
        var key = parts.pop() || '+';

        return {
            key: key === 'space' ? ' ' : key,
            ctrl: parts.indexOf('ctrl') !== -1,
            meta: parts.indexOf('meta') !== -1 || parts.indexOf('cmd') !== -1,
            shift: parts.indexOf('shift') !== -1,
            alt: parts.indexOf('alt') !== -1,
            mod: parts.indexOf('mod') !== -1,
        };
    }

    var IS_MAC = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent || '');

    function matchesShortcut(event, shortcut) {
        var ctrl = shortcut.ctrl || (shortcut.mod && ! IS_MAC);
        var meta = shortcut.meta || (shortcut.mod && IS_MAC);

        return (event.key || '').toLowerCase() === shortcut.key
            && event.ctrlKey === ctrl
            && event.metaKey === meta
            && event.shiftKey === shortcut.shift
            && event.altKey === shortcut.alt;
    }

    /*
     * The editor runs on Tiptap from window.AvianUITiptap = { Editor, StarterKit, ... }:
     * either the bundled avian-ui-editor.js (<x-avian::scripts editor />) or
     * the host app's own Tiptap. Editors that start before it is there wait
     * for the assignment, so the order of the script tags does not matter.
     * An editor that needs an add-on (code, emoji) also waits for its keys,
     * until the page has loaded: then it starts without them.
     */
    var tiptapKit = window.AvianUITiptap || null;
    var tiptapWaiting = [];

    function tiptapReady(keys) {
        return !! (tiptapKit && tiptapKit.Editor) && keys.every(function (key) {
            return !! tiptapKit[key];
        });
    }

    function tiptapFlush(force) {
        tiptapWaiting = tiptapWaiting.filter(function (waiting) {
            if (! tiptapReady(force ? [] : waiting.keys)) {
                return true;
            }

            waiting.callback(tiptapKit);

            return false;
        });
    }

    try {
        Object.defineProperty(window, 'AvianUITiptap', {
            configurable: true,
            get: function () {
                return tiptapKit;
            },
            set: function (kit) {
                tiptapKit = kit;
                tiptapFlush(false);
            },
        });
    } catch (e) {}

    window.addEventListener('load', function () {
        tiptapFlush(true);
    });

    function withTiptap(callback, keys) {
        keys = keys || [];

        if (tiptapReady(keys) || (document.readyState === 'complete' && tiptapReady([]))) {
            callback(tiptapKit);

            return;
        }

        tiptapWaiting.push({ callback: callback, keys: keys });
    }

    /* The window.AvianUITiptap keys an editor's add-on items need. */
    function editorAddonKeys(config) {
        var keys = [];

        if (config.codeHighlight) {
            keys.push('CodeBlockLowlight', 'lowlight');
        }

        if ((config.nodes || []).indexOf('emoji') !== -1) {
            keys.push('Emoji', 'emojis');
        }

        return keys;
    }

    /*
     * What each toolbar item runs on a Tiptap chain, the command it needs and
     * how it reads as active. `run` gets the chain and the editor. Only some items ask Tiptap whether they can run
     * (`dryRun`): a dry run of a toggle chain skips its own clearNodes step,
     * so it reports a list or heading toggle as impossible when it is not.
     */
    var EDITOR_COMMANDS = {
        paragraph: { needs: 'setParagraph', run: function (c) { return c.setParagraph(); }, active: ['paragraph'] },
        bold: { needs: 'toggleBold', run: function (c) { return c.toggleBold(); }, active: ['bold'] },
        italic: { needs: 'toggleItalic', run: function (c) { return c.toggleItalic(); }, active: ['italic'] },
        underline: { needs: 'toggleUnderline', run: function (c) { return c.toggleUnderline(); }, active: ['underline'] },
        strike: { needs: 'toggleStrike', run: function (c) { return c.toggleStrike(); }, active: ['strike'] },
        code: { needs: 'toggleCode', run: function (c) { return c.toggleCode(); }, active: ['code'] },
        highlight: { needs: 'toggleHighlight', run: function (c) { return c.toggleHighlight(); }, active: ['highlight'] },
        subscript: { needs: 'toggleSubscript', run: function (c) { return c.toggleSubscript(); }, active: ['subscript'] },
        superscript: { needs: 'toggleSuperscript', run: function (c) { return c.toggleSuperscript(); }, active: ['superscript'] },
        emoji: { needs: 'setEmoji' },
        color: { needs: 'setColor' },
        'font-size': { needs: 'setFontSize' },
        'bullet-list': { needs: 'toggleBulletList', run: function (c) { return c.toggleBulletList(); }, active: ['bulletList'] },
        'ordered-list': { needs: 'toggleOrderedList', run: function (c) { return c.toggleOrderedList(); }, active: ['orderedList'] },
        'task-list': { needs: 'toggleTaskList', run: function (c) { return c.toggleTaskList(); }, active: ['taskList'] },
        blockquote: { needs: 'toggleBlockquote', run: function (c) { return c.toggleBlockquote(); }, active: ['blockquote'] },
        'code-block': { needs: 'toggleCodeBlock', run: function (c) { return c.toggleCodeBlock(); }, active: ['codeBlock'] },
        'horizontal-rule': { needs: 'setHorizontalRule', run: function (c) { return c.setHorizontalRule(); } },
        'hard-break': { needs: 'setHardBreak', run: function (c) { return c.setHardBreak(); } },
        details: { needs: 'setDetails', run: function (c, editor) { return editor.isActive('details') ? c.unsetDetails() : c.setDetails(); }, active: ['details'] },
        'align-left': { needs: 'setTextAlign', run: function (c) { return c.setTextAlign('left'); }, active: [{ textAlign: 'left' }] },
        'align-center': { needs: 'setTextAlign', run: function (c) { return c.setTextAlign('center'); }, active: [{ textAlign: 'center' }] },
        'align-right': { needs: 'setTextAlign', run: function (c) { return c.setTextAlign('right'); }, active: [{ textAlign: 'right' }] },
        'align-justify': { needs: 'setTextAlign', run: function (c) { return c.setTextAlign('justify'); }, active: [{ textAlign: 'justify' }] },
        link: { needs: 'setLink', active: ['link'] },
        image: { needs: 'setImage', active: ['image'] },
        file: { needs: 'insertContentAt' },
        table: { needs: 'insertTable', active: ['table'] },
        'table-row-before': { needs: 'addRowBefore', dryRun: true, run: function (c) { return c.addRowBefore(); } },
        'table-row-after': { needs: 'addRowAfter', dryRun: true, run: function (c) { return c.addRowAfter(); } },
        'table-column-before': { needs: 'addColumnBefore', dryRun: true, run: function (c) { return c.addColumnBefore(); } },
        'table-column-after': { needs: 'addColumnAfter', dryRun: true, run: function (c) { return c.addColumnAfter(); } },
        'table-delete-row': { needs: 'deleteRow', dryRun: true, run: function (c) { return c.deleteRow(); } },
        'table-delete-column': { needs: 'deleteColumn', dryRun: true, run: function (c) { return c.deleteColumn(); } },
        'table-header-row': { needs: 'toggleHeaderRow', dryRun: true, run: function (c) { return c.toggleHeaderRow(); } },
        'table-merge': { needs: 'mergeOrSplit', dryRun: true, run: function (c) { return c.mergeOrSplit(); } },
        'table-delete': { needs: 'deleteTable', dryRun: true, run: function (c) { return c.deleteTable(); } },
        clear: { needs: 'unsetAllMarks', run: function (c) { return c.unsetAllMarks().clearNodes(); } },
        undo: { needs: 'undo', dryRun: true, run: function (c) { return c.undo(); } },
        redo: { needs: 'redo', dryRun: true, run: function (c) { return c.redo(); } },
    };

    function editorCommand(item) {
        var heading = /^h([1-6])$/.exec(item);

        if (heading) {
            var level = Number(heading[1]);

            return {
                needs: 'toggleHeading',
                run: function (c) { return c.toggleHeading({ level: level }); },
                active: ['heading', { level: level }],
            };
        }

        return EDITOR_COMMANDS[item] || null;
    }

    /* The Tiptap extensions holding exactly the nodes and marks asked for:
       StarterKit with everything else switched off, plus the optional extras.
       `onFiles` receives dropped and pasted files when uploads are on. */
    /* The add-on script (<x-avian::scripts editor="...">) behind an extra. */
    var ADDONS = { 'code-highlight': 'code', emoji: 'emoji' };

    function editorExtensions(kit, config, onFiles) {
        var nodes = config.nodes || [];
        var has = function (node) {
            return nodes.indexOf(node) !== -1;
        };
        var on = function (node, options) {
            return has(node) ? (options || {}) : false;
        };
        var lists = has('bullet-list') || has('ordered-list');
        var extensions = [];
        var missing = [];
        var addons = [];

        extensions.push(kit.StarterKit.configure({
            heading: config.headings && config.headings.length ? { levels: config.headings } : false,
            bold: on('bold'),
            italic: on('italic'),
            underline: on('underline'),
            strike: on('strike'),
            code: on('code'),
            link: on('link', { openOnClick: false, autolink: true, defaultProtocol: 'https' }),
            bulletList: on('bullet-list'),
            orderedList: on('ordered-list'),
            listItem: lists ? {} : false,
            listKeymap: lists ? {} : false,
            blockquote: on('blockquote'),
            codeBlock: config.codeHighlight && kit.CodeBlockLowlight ? false : on('code-block'),
            horizontalRule: on('horizontal-rule'),
        }));

        var extras = [
            ['highlight', ['Highlight'], function () { return [kit.Highlight]; }],
            ['subscript', ['Subscript'], function () { return [kit.Subscript]; }],
            ['superscript', ['Superscript'], function () { return [kit.Superscript]; }],
            ['color', ['TextStyle', 'Color'], function () { return [kit.TextStyle, kit.Color]; }],
            ['font-size', ['TextStyle', 'FontSize'], function () { return [kit.TextStyle, kit.FontSize]; }],
            ['task-list', ['TaskList', 'TaskItem'], function () { return [kit.TaskList, kit.TaskItem.configure({ nested: true })]; }],
            ['image', ['Image'], function () { return [kit.Image]; }],
            ['details', ['Details', 'DetailsSummary', 'DetailsContent'], function () {
                var messages = config.messages || {};

                return [kit.Details.configure({
                    persist: true,
                    renderToggleButton: function (options) {
                        options.element.setAttribute('aria-label', (options.isOpen ? messages.collapse : messages.expand) || '');
                        options.element.setAttribute('aria-expanded', options.isOpen ? 'true' : 'false');
                    },
                }), kit.DetailsSummary, kit.DetailsContent];
            }],
            ['text-align', ['TextAlign'], function () { return [kit.TextAlign.configure({ types: ['heading', 'paragraph'] })]; }],
            ['table', ['Table', 'TableRow', 'TableHeader', 'TableCell'], function () {
                return [kit.Table.configure({ resizable: !! config.tableResizable }), kit.TableRow, kit.TableHeader, kit.TableCell];
            }],
            ['emoji', ['Emoji', 'emojis'], function () {
                return [kit.Emoji.configure({
                    emojis: kit.emojis,
                    enableEmoticons: true,
                    suggestion: emojiSuggestion(kit.emojis),
                })];
            }],
        ];

        /* Syntax highlighting replaces StarterKit's code block. */
        if (config.codeHighlight && has('code-block')) {
            extras.push(['code-highlight', ['CodeBlockLowlight', 'lowlight'], function () {
                return [kit.CodeBlockLowlight.configure({ lowlight: kit.lowlight, defaultLanguage: null })];
            }]);
        }

        if (config.counter) {
            extras.push(['counter', ['CharacterCount'], function () {
                return [kit.CharacterCount.configure({ limit: config.max || null })];
            }]);
        }

        if (config.uploads) {
            extras.push(['uploads', ['FileHandler'], function () {
                return [kit.FileHandler.configure({
                    onDrop: function (editor, files, pos) {
                        onFiles(files, pos);
                    },
                    onPaste: function (editor, files) {
                        onFiles(files, null);
                    },
                })];
            }]);
        }

        extras.forEach(function (extra) {
            if (['uploads', 'counter', 'code-highlight'].indexOf(extra[0]) === -1 && ! has(extra[0])) {
                return;
            }

            var absent = extra[1].filter(function (key) {
                return ! kit[key];
            });

            if (absent.length) {
                missing = missing.concat(absent);

                if (ADDONS[extra[0]]) {
                    addons.push(ADDONS[extra[0]]);
                }

                return;
            }

            /* Color and font size share TextStyle: add it once. */
            extra[2]().forEach(function (extension) {
                if (extensions.indexOf(extension) === -1) {
                    extensions.push(extension);
                }
            });
        });

        if (config.placeholder && kit.Placeholder) {
            extensions.push(kit.Placeholder.configure({ placeholder: config.placeholder }));
        }

        if (missing.length) {
            console.warn('[Avian UI] The editor needs ' + missing.join(', ') + ' in window.AvianUITiptap. Load <x-avian::scripts editor'
                + (addons.length ? '="' + addons.join(' ') + '"' : '') + ' /> or add them to your own Tiptap.');
        }

        return extensions;
    }

    /* Emoji whose shortcode starts with the query, then those tagged with it,
       as { name, emoji, code }: `code` is the shortcode that matched. */
    function findEmojis(emojis, query, limit) {
        query = (query || '').toLowerCase().replace(/^:/, '');

        var starts = [];
        var tagged = [];

        for (var i = 0; i < emojis.length && starts.length < limit; i++) {
            var item = emojis[i];

            /* The letter tiles (regional indicators) only make sense as flags. */
            if (! item.emoji || item.name.indexOf('regional_indicator_') === 0) {
                continue;
            }

            var code = item.shortcodes.filter(function (shortcode) { return shortcode.indexOf(query) === 0; })[0];

            if (code !== undefined) {
                starts.push({ name: item.name, emoji: item.emoji, code: code });
            } else if (tagged.length < limit && (item.tags || []).some(function (tag) { return tag.indexOf(query) === 0; })) {
                tagged.push({ name: item.name, emoji: item.emoji, code: item.shortcodes[0] });
            }
        }

        return starts.concat(tagged).slice(0, limit);
    }

    /*
     * The list that opens on ":smi" while typing: arrows move, Enter or Tab
     * picks, Escape closes. Plain DOM, placed under the cursor.
     */
    function emojiSuggestion(emojis) {
        return {
            items: function (props) {
                return props.query ? findEmojis(emojis, props.query, 8) : [];
            },
            render: function () {
                var list = null;
                var items = [];
                var active = 0;
                var pick = null;

                function draw() {
                    list.innerHTML = '';
                    list.hidden = items.length === 0;

                    items.forEach(function (item, index) {
                        var option = document.createElement('button');

                        option.type = 'button';
                        option.tabIndex = -1;
                        option.className = 'aui-editor-suggest-item' + (index === active ? ' is-active' : '');
                        option.setAttribute('role', 'option');
                        option.setAttribute('aria-selected', index === active ? 'true' : 'false');
                        option.textContent = item.emoji + ' :' + item.code + ':';
                        option.addEventListener('mousedown', function (event) {
                            event.preventDefault();
                            pick(item);
                        });
                        list.appendChild(option);
                    });
                }

                function place(rect) {
                    if (! rect) {
                        return;
                    }

                    list.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - list.offsetWidth - 8)) + 'px';
                    list.style.top = (rect.bottom + 4) + 'px';
                }

                function update(props) {
                    items = props.items;
                    active = 0;
                    pick = function (item) {
                        props.command({ name: item.name });
                    };
                    draw();
                    place(props.clientRect && props.clientRect());
                }

                return {
                    onStart: function (props) {
                        list = document.createElement('div');
                        list.className = 'aui-editor-suggest';
                        list.setAttribute('role', 'listbox');
                        document.body.appendChild(list);
                        update(props);
                    },
                    onUpdate: update,
                    onKeyDown: function (props) {
                        var key = props.event.key;

                        if (! items.length) {
                            return false;
                        }

                        if (key === 'ArrowDown' || key === 'ArrowUp') {
                            active = (active + (key === 'ArrowDown' ? 1 : items.length - 1)) % items.length;
                            draw();

                            return true;
                        }

                        if (key === 'Enter' || key === 'Tab') {
                            pick(items[active]);

                            return true;
                        }

                        if (key === 'Escape') {
                            items = [];
                            draw();

                            return true;
                        }

                        return false;
                    },
                    onExit: function () {
                        if (list) {
                            list.remove();
                            list = null;
                        }
                    },
                };
            },
        };
    }

    /* Does a file match an accept list like "image/*, application/pdf, .docx"? */
    function acceptsFile(file, accept) {
        if (! accept || ! accept.length) {
            return true;
        }

        var type = (file.type || '').toLowerCase();
        var name = (file.name || '').toLowerCase();

        return accept.some(function (rule) {
            rule = rule.toLowerCase();

            if (rule.charAt(0) === '.') {
                return name.slice(-rule.length) === rule;
            }

            if (rule.slice(-2) === '/*') {
                return type.indexOf(rule.slice(0, -1)) === 0;
            }

            return type === rule;
        });
    }

    /* { name, html } out of an aui-editor-set / -insert event, or null.
       Livewire puts named dispatch params straight into the detail. */
    function editorEventDetail(event) {
        var detail = event.detail;

        if (! detail || typeof detail !== 'object' || typeof detail.name !== 'string' || detail.name === '') {
            return null;
        }

        return { name: detail.name, html: typeof detail.html === 'string' ? detail.html : '' };
    }

    /* The CSRF token for a plain fetch upload: the meta tag, the form's
       _token field, or Laravel's XSRF-TOKEN cookie. */
    function csrfHeaders(el) {
        var meta = document.querySelector('meta[name="csrf-token"]');
        var form = el.closest('form');
        var field = form ? form.querySelector('input[name="_token"]') : null;
        var token = (meta && meta.content) || (field && field.value);

        if (token) {
            return { 'X-CSRF-TOKEN': token };
        }

        var cookie = document.cookie.split('; ').filter(function (part) {
            return part.indexOf('XSRF-TOKEN=') === 0;
        })[0];

        return cookie ? { 'X-XSRF-TOKEN': decodeURIComponent(cookie.slice(11)) } : {};
    }

    var commandIds = 0;

    var components = {
        /**
         * Rich text editor on Tiptap (see withTiptap).
         *
         * The Tiptap instance lives in this closure, never on the Alpine data:
         * a reactive proxy around ProseMirror's state breaks it. `tick` goes up
         * on every transaction so the toolbar bindings read its state again.
         *
         * With `config.uploads`, picked, dropped and pasted files go to a
         * Livewire method (through a WithFileUploads property) or are POSTed
         * to a URL; each is inserted once the server answers with its URL.
         *
         * Change the content from outside with a browser event naming the
         * editor (its `name`, or the property it is wire:model'd to):
         *   window.AvianUI.setEditor('body', html)                           // plain JS
         *   $dispatch('aui-editor-insert', { name: 'body', html: html })     // Alpine
         *   $this->dispatch('aui-editor-set', name: 'body', html: $html);    // Livewire
         * "set" replaces everything; "insert" adds at the cursor.
         */
        auiEditor: function (config) {
            config = config || {};

            var editor = null;
            var destroyed = false;
            var focused = false;
            var uploads = config.uploads || null;
            var messages = config.messages || {};

            /* Where each pending upload goes, kept in step with the edits made meanwhile. */
            var pending = [];

            /* Livewire uploads take turns: they all go through the one upload property. */
            var wireQueue = Promise.resolve();

            /* Watches data-aui-disabled, which a Livewire morph can change. */
            var observer = null;

            /* data-aui-* on the content element → attributes of the editable. */
            var ATTRIBUTES = {
                auiId: 'id',
                auiLabel: 'aria-label',
                auiInvalid: 'aria-invalid',
                auiDescribedby: 'aria-describedby',
                auiRequired: 'aria-required',
            };

            /* The emoji kept in the picker before anything is searched for. */
            var EMOJI_LIMIT = 64;

            /* The table picker's largest grid. */
            var TABLE_MAX = 8;

            function message(key, name) {
                return (messages[key] || key).replace(':name', name || '');
            }

            return {
                value: '',
                tick: 0,
                panel: null,
                panelUrl: '',
                panelAlt: '',
                colorOpen: false,
                tableOpen: false,
                tableRows: 1,
                tableCols: 1,
                emojiOpen: false,
                emojiQuery: '',
                uploading: 0,
                uploadError: '',

                init: function () {
                    var self = this;

                    try {
                        this.value = JSON.parse(this.$el.dataset.auiValue || '""') || '';
                    } catch (e) {}

                    observer = new MutationObserver(function () {
                        if (editor && editor.isEditable === self.disabled()) {
                            editor.setEditable(! self.disabled());
                            self.tick++;
                        }
                    });

                    observer.observe(this.$el, { attributes: true, attributeFilter: ['data-aui-disabled'] });

                    withTiptap(function (kit) {
                        if (! destroyed) {
                            self.mount(kit);
                        }
                    }, editorAddonKeys(config));

                    /* A server-side change arrives here through x-modelable. */
                    this.$watch('value', function (value) {
                        if (editor && (value || '') !== self.html()) {
                            editor.commands.setContent(value || '', { emitUpdate: false });
                        }
                    });

                    this.onSet = function (event) {
                        var detail = editorEventDetail(event);

                        if (detail && detail.name === config.name) {
                            self.setContent(detail.html);
                        }
                    };

                    this.onInsert = function (event) {
                        var detail = editorEventDetail(event);

                        if (detail && detail.name === config.name) {
                            self.insertContent(detail.html);
                        }
                    };

                    window.addEventListener('aui-editor-set', this.onSet);
                    window.addEventListener('aui-editor-insert', this.onInsert);
                },

                destroy: function () {
                    destroyed = true;
                    pending.length = 0;

                    if (observer) {
                        observer.disconnect();
                        observer = null;
                    }

                    window.removeEventListener('aui-editor-set', this.onSet);
                    window.removeEventListener('aui-editor-insert', this.onInsert);

                    if (editor) {
                        editor.destroy();
                        editor = null;
                    }
                },

                mount: function (kit) {
                    var self = this;
                    var content = this.$refs.content;
                    var attributes = { class: 'aui-editor-prose', role: 'textbox', 'aria-multiline': 'true' };

                    Object.keys(ATTRIBUTES).forEach(function (key) {
                        if (content.dataset[key]) {
                            attributes[ATTRIBUTES[key]] = content.dataset[key];
                        }
                    });

                    var refresh = function () {
                        self.tick++;
                    };

                    editor = new kit.Editor({
                        element: content,
                        extensions: editorExtensions(kit, config, function (files, pos) {
                            self.uploadFiles(files, pos);
                        }),
                        content: this.value || '',
                        editable: ! this.disabled(),
                        editorProps: {
                            attributes: attributes,
                            handleKeyDown: function (view, event) {
                                return self.shortcut(event);
                            },
                        },
                        onUpdate: function () {
                            self.value = self.html();
                        },
                        onTransaction: function (props) {
                            pending.forEach(function (upload) {
                                upload.pos = props.transaction.mapping.map(upload.pos);
                            });

                            refresh();
                        },
                        onFocus: function () {
                            focused = true;
                            refresh();
                        },
                        onBlur: refresh,
                    });

                    refresh();

                    this.$nextTick(function () {
                        self.syncTabStops(null);
                    });
                },

                /* Mod-K opens the link bar; Alt-F10 moves to the toolbar. */
                shortcut: function (event) {
                    var mod = IS_MAC ? event.metaKey : event.ctrlKey;

                    if (mod && ! event.altKey && ! event.shiftKey && (event.key || '').toLowerCase() === 'k' && this.hasButton('link')) {
                        event.preventDefault();
                        this.openPanel('link');

                        return true;
                    }

                    if (event.altKey && event.key === 'F10' && this.$refs.toolbar) {
                        event.preventDefault();
                        this.focusToolbar();

                        return true;
                    }

                    return false;
                },

                hasButton: function (item) {
                    return !! (this.$refs.toolbar && this.$refs.toolbar.querySelector('[data-aui-command="' + item + '"]'));
                },

                /* Toolbar keyboard: one Tab stop, arrows (and Home / End) move
                   between the buttons, Escape goes back to the text. */
                toolbarItems: function () {
                    return this.$refs.toolbar
                        ? Array.prototype.slice.call(this.$refs.toolbar.querySelectorAll('[data-aui-toolbar-item]'))
                        : [];
                },

                syncTabStops: function (current) {
                    var items = this.toolbarItems();

                    if (! current || items.indexOf(current) === -1) {
                        current = items.filter(function (item) {
                            return item.getAttribute('tabindex') === '0' && ! item.disabled;
                        })[0] || items.filter(function (item) {
                            return ! item.disabled;
                        })[0] || items[0];
                    }

                    items.forEach(function (item) {
                        item.setAttribute('tabindex', item === current ? '0' : '-1');
                    });
                },

                focusToolbar: function () {
                    this.syncTabStops(null);

                    var current = this.toolbarItems().filter(function (item) {
                        return item.getAttribute('tabindex') === '0';
                    })[0];

                    if (current) {
                        current.focus();
                    }
                },

                toolbarKey: function (event) {
                    var items = this.toolbarItems().filter(function (item) {
                        return ! item.disabled;
                    });
                    var index = items.indexOf(event.target);

                    if (index === -1) {
                        return;
                    }

                    var next = {
                        ArrowRight: items[(index + 1) % items.length],
                        ArrowLeft: items[(index - 1 + items.length) % items.length],
                        Home: items[0],
                        End: items[items.length - 1],
                    }[event.key];

                    if (next) {
                        event.preventDefault();
                        this.syncTabStops(next);
                        next.focus();
                    } else if (event.key === 'Escape' && editor) {
                        editor.commands.focus();
                    }
                },

                /* Replace the whole document. Before Tiptap has loaded, the
                   value is simply what the editor will start with. */
                setContent: function (html) {
                    if (! editor) {
                        this.value = html;

                        return;
                    }

                    editor.commands.setContent(html, { emitUpdate: false });
                    this.value = this.html();
                },

                /* Insert at the cursor (the end, if the editor never had one),
                   replacing any selected text. Read-only editors are left alone. */
                insertContent: function (html) {
                    if (! editor) {
                        this.value = (this.value || '') + html;

                        return;
                    }

                    if (! editor.isEditable || html === '') {
                        return;
                    }

                    /* Never focused: after the last block, not inside it (a list, say). */
                    if (! focused) {
                        editor.chain().insertContentAt(editor.state.doc.content.size, html).focus('end').run();

                        return;
                    }

                    editor.chain().focus().insertContent(html).run();
                },

                disabled: function () {
                    return this.$el.dataset.auiDisabled === 'true';
                },

                /* What the counter shows: the characters typed so far. */
                characters: function () {
                    if (this.tick < 0 || ! editor || ! editor.storage.characterCount) {
                        return 0;
                    }

                    return editor.storage.characterCount.characters();
                },

                atLimit: function () {
                    return !! config.max && this.characters() >= config.max;
                },

                /* An empty document submits as '' rather than '<p></p>'. */
                html: function () {
                    return ! editor || editor.isEmpty ? '' : editor.getHTML();
                },

                isActive: function (item) {
                    var command = editorCommand(item);

                    if (this.tick < 0 || ! editor || ! command || ! command.active) {
                        return false;
                    }

                    return editor.isActive.apply(editor, command.active);
                },

                can: function (item) {
                    var command = editorCommand(item);

                    if (this.tick < 0 || ! editor || ! command || ! editor.isEditable) {
                        return false;
                    }

                    if (item === 'file' && ! this.canUpload()) {
                        return false;
                    }

                    /* A command whose extension is not loaded is not there. */
                    if (typeof editor.commands[command.needs] !== 'function') {
                        return false;
                    }

                    return ! command.dryRun || command.run(editor.can().chain(), editor).run();
                },

                run: function (item) {
                    if (item === 'link' || item === 'image') {
                        this.openPanel(item);

                        return;
                    }

                    if (item === 'color' || item === 'table' || item === 'emoji') {
                        this.toggleMenu(item);

                        return;
                    }

                    if (item === 'file') {
                        this.pickFile('file');

                        return;
                    }

                    var command = editorCommand(item);

                    if (command && this.can(item)) {
                        command.run(editor.chain().focus(), editor).run();
                    }
                },

                /* The color, table and emoji menus: one open at a time. */
                toggleMenu: function (item) {
                    var self = this;
                    var open = ! this[item + 'Open'] && this.can(item);

                    this.colorOpen = false;
                    this.tableOpen = false;
                    this.emojiOpen = false;
                    this[item + 'Open'] = open;

                    if (! open) {
                        return;
                    }

                    if (item === 'table') {
                        this.tableRows = 3;
                        this.tableCols = 3;
                    }

                    if (item === 'emoji') {
                        this.emojiQuery = '';
                    }

                    /* x-show's transition shows the menu a frame later. */
                    this.$nextTick(function () {
                        requestAnimationFrame(function () {
                            var target = item === 'table' ? self.$refs.tableGrid : (item === 'emoji' ? self.$refs.emojiSearch : null);

                            if (target) {
                                target.focus();
                            }
                        });
                    });
                },

                closeMenus: function (backToText) {
                    this.colorOpen = false;
                    this.tableOpen = false;
                    this.emojiOpen = false;

                    if (backToText && editor) {
                        editor.commands.focus();
                    }
                },

                /* The table picker: hover or arrow keys pick the size. */
                tableSize: function (rows, cols) {
                    this.tableRows = Math.min(TABLE_MAX, Math.max(1, rows));
                    this.tableCols = Math.min(TABLE_MAX, Math.max(1, cols));
                },

                tableKey: function (event) {
                    var moves = {
                        ArrowDown: [1, 0],
                        ArrowUp: [-1, 0],
                        ArrowRight: [0, 1],
                        ArrowLeft: [0, -1],
                    };

                    if (moves[event.key]) {
                        event.preventDefault();
                        this.tableSize(this.tableRows + moves[event.key][0], this.tableCols + moves[event.key][1]);
                    } else if (event.key === 'Enter' || event.key === ' ') {
                        event.preventDefault();
                        this.insertTable(this.tableRows, this.tableCols);
                    }
                },

                insertTable: function (rows, cols) {
                    this.tableOpen = false;

                    if (this.can('table')) {
                        editor.chain().focus().insertTable({ rows: rows, cols: cols, withHeaderRow: true }).run();
                    }
                },

                /* The emoji picker: the first matches for the search. */
                emojiResults: function () {
                    if (this.tick < 0 || ! editor || ! editor.storage.emoji) {
                        return [];
                    }

                    return findEmojis(editor.storage.emoji.emojis, this.emojiQuery.trim(), EMOJI_LIMIT).map(function (item) {
                        return { name: item.name, emoji: item.emoji, label: ':' + item.code + ':' };
                    });
                },

                pickEmoji: function (name) {
                    this.emojiOpen = false;

                    if (this.can('emoji')) {
                        editor.chain().focus().setEmoji(name).run();
                    }
                },

                /* Text colour and size live on the textStyle mark. */
                textStyle: function (attribute) {
                    if (this.tick < 0 || ! editor) {
                        return '';
                    }

                    return editor.getAttributes('textStyle')[attribute] || '';
                },

                setColor: function (color) {
                    this.colorOpen = false;

                    if (! this.can('color')) {
                        return;
                    }

                    var chain = editor.chain().focus();

                    (color ? chain.setColor(color) : chain.unsetColor()).run();
                },

                setFontSize: function (size) {
                    if (! this.can('font-size')) {
                        return;
                    }

                    var chain = editor.chain().focus();

                    (size ? chain.setFontSize(size) : chain.unsetFontSize()).run();
                },

                /* Link and image ask for their URL in a bar under the toolbar. */
                openPanel: function (kind) {
                    if (! this.can(kind)) {
                        return;
                    }

                    var self = this;

                    /* A selected image is edited in place. */
                    var current = editor.getAttributes(kind);

                    this.panelUrl = (kind === 'link' ? current.href : current.src) || '';
                    this.panelAlt = kind === 'image' ? (current.alt || '') : '';
                    this.panel = kind;
                    this.closeMenus(false);

                    this.$nextTick(function () {
                        self.$refs.panelInput.focus();
                        self.$refs.panelInput.select();
                    });
                },

                closePanel: function () {
                    this.panel = null;
                    this.panelUrl = '';
                    this.panelAlt = '';

                    if (editor) {
                        editor.commands.focus();
                    }
                },

                applyPanel: function () {
                    var url = (this.panelUrl || '').trim();

                    if (this.panel === 'link' && url === '') {
                        this.removeLink();

                        return;
                    }

                    /* "example.com" is a host, not a relative path. */
                    if (! /^([a-z][a-z0-9+.-]*:|[\/#?])/i.test(url)) {
                        url = 'https://' + url;
                    }

                    if (this.panel === 'link') {
                        editor.chain().focus().extendMarkRange('link').setLink({ href: url }).run();
                    } else if (url !== '') {
                        editor.chain().focus().setImage({ src: url, alt: this.panelAlt.trim() || null }).run();
                    }

                    this.closePanel();
                },

                removeLink: function () {
                    editor.chain().focus().extendMarkRange('link').unsetLink().run();
                    this.closePanel();
                },

                /* Uploads ----------------------------------------------------- */

                canUpload: function () {
                    if (! uploads) {
                        return false;
                    }

                    return uploads.method ? !! this.$wire : !! uploads.url;
                },

                pickFile: function (kind) {
                    if (! this.canUpload() || ! editor || ! editor.isEditable) {
                        return;
                    }

                    var input = this.$refs.fileInput;
                    var images = (uploads.accept || []).filter(function (rule) {
                        return rule.indexOf('image/') === 0;
                    });

                    input.accept = (kind === 'image' ? (images.length ? images : ['image/*']) : (uploads.accept || [])).join(',');
                    input.click();
                },

                picked: function (event) {
                    var files = Array.prototype.slice.call(event.target.files || []);

                    event.target.value = '';
                    this.panel = null;
                    this.uploadFiles(files, null);
                },

                uploadFiles: function (files, pos) {
                    var self = this;

                    if (! this.canUpload()) {
                        return;
                    }

                    var rejected = [];

                    this.uploadError = '';

                    files.forEach(function (file) {
                        if (acceptsFile(file, uploads.accept)) {
                            self.uploadFile(file, pos);
                        } else {
                            rejected.push(file.name);
                        }
                    });

                    if (rejected.length) {
                        this.uploadError = message(rejected.length > 1 ? 'rejectedMany' : 'rejected', rejected.join(', '));
                    }
                },

                uploadFile: function (file, pos) {
                    var self = this;
                    var upload = { pos: pos === null || pos === undefined ? editor.state.selection.to : pos };

                    pending.push(upload);
                    this.uploading++;

                    this.sendFile(file).then(function (result) {
                        var url = typeof result === 'string' ? result : (result && result.url);

                        if (! url) {
                            throw new Error(message('failed', file.name));
                        }

                        self.insertFile(file, url, (result && result.name) || file.name, upload.pos);
                        self.$dispatch('aui-editor-uploaded', { url: url, name: file.name });
                    }).catch(function (error) {
                        self.uploadError = (error && error.message) || message('failed', file.name);
                        self.$dispatch('aui-editor-upload-failed', { name: file.name, message: self.uploadError });
                    }).then(function () {
                        pending.splice(pending.indexOf(upload), 1);
                        self.uploading = Math.max(0, self.uploading - 1);
                    });
                },

                /* An image becomes an image when the editor has them; anything
                   else becomes a link to the file, or its name as text. */
                insertFile: function (file, url, name, pos) {
                    if (! editor || destroyed) {
                        return;
                    }

                    var schema = editor.schema;
                    var content;

                    if (/^image\//.test(file.type) && schema.nodes.image) {
                        content = { type: 'image', attrs: { src: url, alt: name } };
                    } else {
                        content = { type: 'text', text: name };

                        if (schema.marks.link) {
                            content.marks = [{ type: 'link', attrs: { href: url } }];
                        }
                    }

                    pos = Math.min(Math.max(0, pos), editor.state.doc.content.size);

                    /* Focus stays wherever the user went while the file uploaded. */
                    editor.commands.insertContentAt(pos, content);
                },

                /* Resolves with the file's URL, or { url, name }. */
                sendFile: function (file) {
                    if (uploads.method) {
                        var wire = this.$wire;

                        /* One at a time, or a second file would replace the
                           first on the property before its method has run. */
                        var turn = wireQueue.then(function () {
                            return new Promise(function (resolve, reject) {
                                wire.upload(uploads.model, file, function () {
                                    Promise.resolve(wire[uploads.method]()).then(resolve, reject);
                                }, function () {
                                    reject(new Error(message('failed', file.name)));
                                });
                            });
                        });

                        wireQueue = turn.catch(function () {});

                        return turn;
                    }

                    var body = new FormData();

                    body.append('file', file);

                    var headers = csrfHeaders(this.$el);

                    headers.Accept = 'application/json';
                    headers['X-Requested-With'] = 'XMLHttpRequest';

                    return fetch(uploads.url, {
                        method: 'POST',
                        body: body,
                        headers: headers,
                        credentials: 'same-origin',
                    }).then(function (response) {
                        return response.json().catch(function () {
                            return {};
                        }).then(function (data) {
                            if (! response.ok) {
                                throw new Error((data && data.message) || message('failed', file.name));
                            }

                            return data;
                        });
                    });
                },
            };
        },

        /**
         * Modal / dialog.
         *
         * Open it from anywhere with a browser event:
         *   window.AvianUI.openModal('edit')                      // plain JS
         *   $dispatch('aui-modal-open', { name: 'edit' })         // Alpine
         *   $this->dispatch('aui-modal-open', name: 'edit');      // Livewire
         */
        auiModal: function (config) {
            config = config || {};

            return {
                open: config.open === true,
                name: config.name || null,
                closeOnEscape: config.closeOnEscape !== false,
                closeOnOverlay: config.closeOnOverlay !== false,
                locked: false,
                overlayId: ++overlayIds,

                init: function () {
                    var self = this;

                    this.$watch('open', function (value) {
                        value ? self.lock() : self.unlock();
                    });

                    this.onOpen = function (event) {
                        if (self.name && eventName(event) === self.name) {
                            self.show();
                        }
                    };

                    this.onClose = function (event) {
                        var target = eventName(event);

                        if (! target || (self.name && target === self.name)) {
                            self.hide();
                        }
                    };

                    window.addEventListener('aui-modal-open', this.onOpen);
                    window.addEventListener('aui-modal-close', this.onClose);

                    if (this.open) {
                        this.lock();
                    }
                },

                destroy: function () {
                    window.removeEventListener('aui-modal-open', this.onOpen);
                    window.removeEventListener('aui-modal-close', this.onClose);

                    this.unlock();
                },

                lock: function () {
                    if (! this.locked) {
                        this.locked = true;
                        overlays.push(this.overlayId);
                        lockScroll();
                    }
                },

                unlock: function () {
                    if (this.locked) {
                        this.locked = false;
                        var index = overlays.indexOf(this.overlayId);

                        if (index !== -1) {
                            overlays.splice(index, 1);
                        }
                        unlockScroll();
                    }
                },

                show: function () {
                    this.open = true;
                },

                hide: function () {
                    this.open = false;
                },

                toggle: function () {
                    this.open = ! this.open;
                },

                escape: function () {
                    /* Esc answers a confirm dialog on top first. */
                    if (confirmHost && confirmHost.open) {
                        return;
                    }

                    /* A modal opened from a drawer (or another modal) closes on its own. */
                    if (overlays.length && overlays[overlays.length - 1] !== this.overlayId) {
                        return;
                    }

                    if (this.closeOnEscape) {
                        this.hide();
                    }
                },

                overlay: function (event) {
                    if (this.closeOnOverlay && event.target === event.currentTarget) {
                        this.hide();
                    }
                },
            };
        },

        /**
         * Shared confirmation dialog (<x-avian::confirm>).
         *
         *   window.AvianUI.confirm({ message: 'Delete?' }).then(ok => ...)   // plain JS
         *   $dispatch('aui-confirm', { message: 'Delete?', event: 'go' })   // Alpine
         *   $this->dispatch('aui-confirm', message: 'Delete?', event: 'go'); // Livewire
         *
         * With `event`, a yes dispatches that browser event (with `params` as
         * its detail), which a Livewire #[On] listener picks up.
         *
         * With `loading`, a yes keeps the dialog open with a spinner on the
         * confirm button until the work it started is done: the Livewire
         * requests it triggered, or a page navigation (which never returns).
         * An `action` function implies `loading` and is waited on as well:
         *
         *   AvianUI.confirm({ message: 'Delete?', action: () => $wire.delete(5) })
         */
        auiConfirm: function (config) {
            config = config || {};

            var defaults = {
                title: config.title || 'Are you sure?',
                message: '',
                confirmText: config.confirmText || 'Confirm',
                cancelText: config.cancelText || 'Cancel',
                variant: config.variant || 'danger',
            };

            return {
                open: false,
                busy: false,
                leaving: false,
                current: Object.assign({}, defaults),
                resolve: null,
                loading: config.loading === true,
                action: null,
                turn: 0,

                init: function () {
                    var self = this;

                    confirmHost = this;

                    /* A navigation the yes started keeps the dialog busy until
                       the page goes; coming back from the bfcache lets go. */
                    this.onLeave = function () {
                        if (self.busy) {
                            self.leaving = true;
                        }
                    };

                    this.onReturn = function (event) {
                        if (event.persisted && self.busy) {
                            self.finish();
                        }
                    };

                    window.addEventListener('beforeunload', this.onLeave);
                    window.addEventListener('pageshow', this.onReturn);

                    this.onAsk = function (event) {
                        var detail = event.detail;

                        /* Livewire wraps positional dispatch params in an array. */
                        if (detail && Array.isArray(detail.params) && typeof detail.params[0] === 'object' && ! detail.message) {
                            detail = detail.params[0];
                        }

                        detail = typeof detail === 'string' ? { message: detail } : (detail || {});

                        self.ask(detail).then(function (ok) {
                            if (ok && detail.event) {
                                window.dispatchEvent(new CustomEvent(detail.event, { detail: detail.params || {} }));
                            }
                        });
                    };

                    window.addEventListener('aui-confirm', this.onAsk);
                },

                destroy: function () {
                    window.removeEventListener('aui-confirm', this.onAsk);
                    window.removeEventListener('beforeunload', this.onLeave);
                    window.removeEventListener('pageshow', this.onReturn);

                    if (confirmHost === this) {
                        confirmHost = null;
                    }

                    this.answer(false);
                    this.finish();
                },

                get icon() {
                    return {
                        danger: 'fas fa-triangle-exclamation',
                        warning: 'fas fa-circle-exclamation',
                        success: 'fas fa-circle-check',
                        info: 'fas fa-circle-info',
                    }[this.current.variant] || 'fas fa-circle-question';
                },

                ask: function (options) {
                    var self = this;
                    var picked = {};

                    /* A second question replaces the first, which counts as a
                       no, and takes over from one still busy. */
                    this.answer(false);
                    this.finish();

                    Object.keys(defaults).forEach(function (key) {
                        if (options[key] !== undefined && options[key] !== null && options[key] !== '') {
                            picked[key] = String(options[key]);
                        }
                    });

                    this.current = Object.assign({}, defaults, picked);
                    this.action = typeof options.action === 'function' ? options.action : null;
                    this.loading = this.action !== null || (
                        options.loading === undefined || options.loading === null
                            ? config.loading === true
                            : options.loading !== false && options.loading !== 'false'
                    );
                    this.open = true;
                    lockScroll();

                    /* Focus the safe choice, so a stray Enter never confirms. */
                    this.$nextTick(function () {
                        if (self.$refs.cancel) {
                            self.$refs.cancel.focus();
                        }
                    });

                    return new Promise(function (resolve) {
                        self.resolve = resolve;
                    });
                },

                answer: function (ok) {
                    if (! this.resolve) {
                        return;
                    }

                    var self = this;
                    var resolve = this.resolve;

                    this.resolve = null;

                    if (ok !== true || ! this.loading) {
                        this.open = false;
                        unlockScroll();
                        resolve(ok === true);

                        return;
                    }

                    var turn = ++this.turn;
                    var work = this.action ? Promise.resolve().then(this.action) : null;

                    this.busy = true;
                    this.leaving = false;

                    /* Resolving first lets the caller start its work (replay a
                       click, dispatch the event) before we wait on it. */
                    resolve(true);

                    Promise.resolve(work)
                        .catch(function (error) {
                            console.error(error);
                        })
                        .then(livewireSettled)
                        .then(function () {
                            if (turn === self.turn && ! self.leaving) {
                                self.finish();
                            }
                        });
                },

                /* Closes a busy dialog once its work is done. */
                finish: function () {
                    if (! this.busy) {
                        return;
                    }

                    this.turn++;
                    this.busy = false;
                    this.leaving = false;
                    this.open = false;
                    unlockScroll();
                },
            };
        },

        /**
         * Toast stack (<x-avian::toasts>).
         *
         *   window.AvianUI.toast('Saved.', 'success')                        // plain JS
         *   $dispatch('aui-toast', { message: 'Saved.' })                    // Alpine
         *   $this->dispatch('aui-toast', message: 'Saved.', variant: 'info'); // Livewire
         *
         * Each toast closes itself after `duration` ms (0 keeps it open); hover
         * or focus pauses the timer so it never vanishes while being read.
         */
        auiToasts: function (config) {
            config = config || {};

            var icons = {
                success: 'fas fa-circle-check',
                warning: 'fas fa-triangle-exclamation',
                danger: 'fas fa-circle-exclamation',
                info: 'fas fa-circle-info',
                neutral: 'fas fa-bell',
            };

            var nextId = 1;

            return {
                toasts: [],
                duration: typeof config.duration === 'number' ? config.duration : 5000,
                max: typeof config.max === 'number' && config.max > 0 ? config.max : 5,

                init: function () {
                    var self = this;

                    toastHost = this;

                    this.onToast = function (event) {
                        var detail = event.detail;

                        /* Livewire wraps positional dispatch params in an array. */
                        if (detail && Array.isArray(detail.params) && ! detail.message) {
                            detail = typeof detail.params[0] === 'string'
                                ? { message: detail.params[0], variant: detail.params[1] }
                                : detail.params[0];
                        }

                        self.add(typeof detail === 'string' ? { message: detail } : (detail || {}));
                    };

                    window.addEventListener('aui-toast', this.onToast);

                    (config.toasts || []).concat(toastQueue.splice(0)).forEach(function (options) {
                        self.add(options);
                    });
                },

                destroy: function () {
                    window.removeEventListener('aui-toast', this.onToast);

                    this.toasts.forEach(function (item) {
                        clearTimeout(item.timer);
                    });

                    if (toastHost === this) {
                        toastHost = null;
                    }
                },

                add: function (options) {
                    options = options || {};

                    var variant = options.variant === 'error' ? 'danger' : (options.variant || 'success');
                    var duration = typeof options.duration === 'number' ? options.duration : this.duration;
                    var message = options.message === undefined || options.message === null ? '' : String(options.message);
                    var title = options.title ? String(options.title) : '';

                    if (message === '' && title === '') {
                        return null;
                    }

                    var item = {
                        id: nextId++,
                        variant: variant,
                        title: title,
                        message: message,
                        icon: options.icon === false ? null : (options.icon || icons[variant] || icons.info),
                        dismissible: options.dismissible !== false,
                        duration: Math.max(0, duration),
                        remaining: Math.max(0, duration),
                        visible: true,
                        paused: false,
                        timer: null,
                        startedAt: 0,
                    };

                    this.toasts.push(item);

                    /* Past the cap, the oldest still showing makes room. */
                    var showing = this.toasts.filter(function (t) { return t.visible; });

                    if (showing.length > this.max) {
                        this.dismiss(showing[0].id);
                    }

                    this.schedule(this.find(item.id));

                    return item.id;
                },

                find: function (id) {
                    for (var i = 0; i < this.toasts.length; i++) {
                        if (this.toasts[i].id === id) {
                            return this.toasts[i];
                        }
                    }

                    return null;
                },

                schedule: function (item) {
                    var self = this;

                    if (! item || item.duration === 0) {
                        return;
                    }

                    item.startedAt = Date.now();
                    item.timer = setTimeout(function () {
                        self.dismiss(item.id);
                    }, item.remaining);
                },

                pause: function (item) {
                    if (item.duration === 0 || item.paused || ! item.visible) {
                        return;
                    }

                    clearTimeout(item.timer);
                    item.remaining = Math.max(0, item.remaining - (Date.now() - item.startedAt));
                    item.paused = true;
                },

                resume: function (item) {
                    if (! item.paused || ! item.visible) {
                        return;
                    }

                    item.paused = false;
                    this.schedule(item);
                },

                dismiss: function (id) {
                    var self = this;
                    var item = this.find(id);

                    if (! item || ! item.visible) {
                        return;
                    }

                    clearTimeout(item.timer);
                    item.visible = false;

                    /* Leave time for the leave transition before dropping it. */
                    setTimeout(function () {
                        self.toasts = self.toasts.filter(function (t) { return t.id !== id; });
                    }, 250);
                },

                clear: function () {
                    var self = this;

                    this.toasts.forEach(function (item) {
                        self.dismiss(item.id);
                    });
                },
            };
        },

        /** Accordion: tracks which <x-avian::accordion.item> keys are open. */
        auiAccordion: function (config) {
            config = config || {};

            return {
                multiple: config.multiple === true,
                active: [],

                isOpen: function (key) {
                    return this.active.indexOf(key) !== -1;
                },

                expand: function (key) {
                    if (this.isOpen(key)) {
                        return;
                    }

                    this.active = this.multiple ? this.active.concat([key]) : [key];
                },

                collapse: function (key) {
                    this.active = this.active.filter(function (item) {
                        return item !== key;
                    });
                },

                toggle: function (key) {
                    this.isOpen(key) ? this.collapse(key) : this.expand(key);
                },
            };
        },

        /**
         * Expandable table row: shows and hides the detail <tr> rendered
         * straight after it by <x-avian::table.row>.
         */
        auiTableRow: function (config) {
            config = config || {};

            return {
                open: config.expanded === true,

                init: function () {
                    var self = this;

                    this.sync();

                    this.$watch('open', function () {
                        self.sync();
                    });
                },

                details: function () {
                    var next = this.$el.nextElementSibling;

                    return next && next.classList.contains('aui-table-details') ? next : null;
                },

                sync: function () {
                    var details = this.details();

                    if (details) {
                        details.hidden = ! this.open;
                    }
                },

                toggle: function () {
                    this.open = ! this.open;
                },

                /* A clickable row ignores clicks meant for its own controls,
                   and a click that ends a text selection. */
                clickRow: function (event) {
                    if (event.target.closest('a, button, input, select, textarea, label, [data-no-toggle]')) {
                        return;
                    }

                    if (window.getSelection && String(window.getSelection()) !== '') {
                        return;
                    }

                    this.toggle();
                },
            };
        },

        /** Collapsible card: folds <x-avian::card collapsible> into its header. */
        auiCard: function (config) {
            config = config || {};

            return {
                collapsed: config.collapsed === true,
                persist: config.persist || null,

                init: function () {
                    if (! this.persist) {
                        return;
                    }

                    try {
                        var stored = window.localStorage.getItem('aui-card:' + this.persist);

                        if (stored === 'collapsed' || stored === 'expanded') {
                            this.collapsed = stored === 'collapsed';
                        }
                    } catch (error) {
                        /* Storage unavailable: keep the server-rendered state. */
                    }
                },

                toggle: function () {
                    this.collapsed ? this.expand() : this.collapse();
                },

                expand: function () {
                    this.set(false);
                },

                collapse: function () {
                    this.set(true);
                },

                set: function (collapsed) {
                    if (this.collapsed === collapsed) {
                        return;
                    }

                    this.collapsed = collapsed;

                    if (this.persist) {
                        try {
                            window.localStorage.setItem('aui-card:' + this.persist, collapsed ? 'collapsed' : 'expanded');
                        } catch (error) {
                            /* Storage unavailable: the choice lasts for this page only. */
                        }
                    }

                    this.$dispatch('aui-card-toggled', { collapsed: collapsed });
                },

                /** Header clicks toggle too, unless they land on a control inside it. */
                headerClick: function (event) {
                    if (event.target.closest('a, button, input, select, textarea, label, [role="button"], .aui-card-actions')) {
                        return;
                    }

                    this.toggle();
                },
            };
        },

        /** Dropdown menu. */
        auiDropdown: function (config) {
            config = config || {};

            return {
                open: config.open === true,
                closeOnSelect: config.closeOnSelect !== false,
                align: config.align || 'left',
                top: 0,
                left: 0,

                /* The menu is position: fixed so a scrolling ancestor (a table
                   wrapper, a card) cannot clip it. Scroll events don't bubble,
                   so listen in the capture phase to follow any scroller. */
                init: function () {
                    var self = this;

                    this.follow = function () {
                        if (self.open) {
                            self.reposition();
                        }
                    };

                    window.addEventListener('scroll', this.follow, true);
                    window.addEventListener('resize', this.follow);

                    if (this.open) {
                        this.$nextTick(function () {
                            self.reposition();
                        });
                    }
                },

                destroy: function () {
                    window.removeEventListener('scroll', this.follow, true);
                    window.removeEventListener('resize', this.follow);
                },

                reposition: function () {
                    var trigger = this.$refs.trigger;
                    var menu = this.$refs.menu;

                    if (! trigger || ! menu) {
                        return;
                    }

                    var rect = trigger.getBoundingClientRect();
                    var width = menu.offsetWidth;
                    var height = menu.offsetHeight;
                    var top = rect.bottom + 6;
                    var left = this.align === 'right' ? rect.right - width : rect.left;

                    /* Open upwards when there is no room below but there is above. */
                    if (top + height > window.innerHeight && rect.top - 6 - height >= 0) {
                        top = rect.top - 6 - height;
                    }

                    this.top = top;
                    this.left = Math.max(4, Math.min(left, window.innerWidth - width - 4));
                },

                toggle: function () {
                    if (this.open) {
                        this.hide();
                    } else {
                        this.show();
                    }
                },

                show: function () {
                    var self = this;

                    this.open = true;

                    /* Measure once the menu is displayed. */
                    this.$nextTick(function () {
                        self.reposition();
                    });
                },

                hide: function () {
                    this.open = false;
                },

                /* Esc from inside an open menu closes only the menu, not a
                   modal or drawer around it, and hands focus back. */
                escape: function (event) {
                    if (! this.open) {
                        return;
                    }

                    event.stopPropagation();
                    this.hide();

                    var trigger = this.$refs.trigger;
                    var target = trigger && (trigger.matches('button, a') ? trigger : trigger.querySelector('button, a, [tabindex]'));

                    if (target) {
                        target.focus();
                    }
                },

                select: function () {
                    if (this.closeOnSelect) {
                        this.open = false;
                    }
                },
            };
        },

        /** Tab set. */
        auiTabs: function (config) {
            config = config || {};

            return {
                active: config.active || null,

                select: function (tab) {
                    this.active = tab;

                    this.$dispatch('aui-tab-changed', { tab: tab });
                },

                isActive: function (tab) {
                    return this.active === tab;
                },
            };
        },

        /**
         * Data list: switches its items between a list and a grid layout.
         *
         * `persist` names a localStorage key that remembers the viewer's
         * choice across page loads. Storage can be unavailable (private mode,
         * blocked site data), so every access is guarded and the server-side
         * `view` simply stays in place when it fails.
         */
        auiDatalist: function (config) {
            config = config || {};

            return {
                view: config.view === 'grid' ? 'grid' : 'list',
                persist: config.persist || null,

                init: function () {
                    if (! this.persist) {
                        return;
                    }

                    try {
                        var stored = window.localStorage.getItem('aui-datalist:' + this.persist);

                        if (stored === 'list' || stored === 'grid') {
                            this.view = stored;
                        }
                    } catch (error) {
                        /* Storage unavailable: keep the server-rendered view. */
                    }
                },

                set: function (view) {
                    if (view !== 'list' && view !== 'grid') {
                        return;
                    }

                    this.view = view;

                    if (this.persist) {
                        try {
                            window.localStorage.setItem('aui-datalist:' + this.persist, view);
                        } catch (error) {
                            /* Storage unavailable: the choice lasts for this page only. */
                        }
                    }

                    this.$dispatch('aui-view-changed', { view: view });
                },
            };
        },

        /** Dismissible element (alerts, banners). */
        auiDismiss: function (config) {
            config = config || {};

            return {
                visible: config.visible !== false,

                dismiss: function () {
                    this.visible = false;

                    this.$dispatch('aui-dismissed');
                },
            };
        },

        /**
         * Searchable select (combobox).
         *
         * A `<select>` replacement with a search box. By default it filters
         * client-side, against the rendered option labels. Pass a
         * `searchProperty` (Livewire only) to hand filtering to the server
         * instead — the parent returns an already-filtered option list, so
         * `filter()` becomes a no-op and the search input binds with
         * `wire:model` directly.
         *
         * With `taggable`, a search term that matches no option label can be
         * picked as a value of its own, and reopening prefills the search box
         * with the current label so it can be edited.
         *
         * The dropdown is teleported to <body> and positioned from the
         * trigger's bounding rect, so it never gets clipped by an
         * `overflow: hidden` ancestor (a card, for instance).
         */
        auiSearchableSelect: function (config) {
            config = config || {};

            return {
                open: false,
                search: '',
                property: config.property || null,
                searchProperty: config.searchProperty || null,
                labels: config.labels || {},
                localValue: config.value || null,
                taggable: config.taggable === true,
                createText: config.createText || 'Add ":term"',

                /* True while the search box still holds the prefilled label
                   of a taggable select, i.e. the user has not typed yet. */
                pristine: false,

                top: 0,
                left: 0,
                width: 0,
                maxHeight: 280,
                sizeObserver: null,

                /* Seeds come from data-* attributes rather than the x-data
                   expression, so it stays constant across Livewire morphs
                   and Alpine never re-initialises (and forgets) the cache. */
                init: function () {
                    var dataset = this.$el.dataset;

                    if (dataset.auiValue) {
                        this.localValue = dataset.auiValue;
                    }

                    if (dataset.auiLabels) {
                        try {
                            Object.assign(this.labels, JSON.parse(dataset.auiLabels));
                        } catch (e) {}
                    }

                    /* Capture phase, so scrolling any ancestor (a modal body,
                       a table wrapper) keeps the fixed dropdown anchored. */
                    var self = this;

                    this.follow = function () {
                        if (self.open) {
                            self.reposition();
                        }
                    };

                    window.addEventListener('scroll', this.follow, true);
                    window.addEventListener('resize', this.follow);
                },

                destroy: function () {
                    window.removeEventListener('scroll', this.follow, true);
                    window.removeEventListener('resize', this.follow);

                    if (this.sizeObserver) {
                        this.sizeObserver.disconnect();
                    }
                },

                /* Read through $wire so a server-side change reaches the
                   label too; falls back to local state when there is no
                   wire:model. */
                get selectedValue() {
                    var raw = this.property ? this.$wire.$get(this.property) : this.localValue;

                    return raw === null || raw === undefined || raw === '' ? null : String(raw);
                },

                get selectedLabel() {
                    var value = this.selectedValue;

                    if (value === null) {
                        return null;
                    }

                    return this.labels[value] ?? (this.taggable ? value : null);
                },

                get term() {
                    return (this.search || '').trim();
                },

                /* Offer the typed text as a new value unless it already names
                   an option (or the current selection). */
                get canCreate() {
                    var term = this.term.toLowerCase();

                    if (! this.taggable || this.pristine || term === '') {
                        return false;
                    }

                    if (this.selectedLabel !== null && this.selectedLabel.toLowerCase() === term) {
                        return false;
                    }

                    return ! this.options().some(function (item) {
                        return (item.dataset.label || item.textContent || '').trim().toLowerCase() === term;
                    });
                },

                get createLabel() {
                    return this.createText.replace(':term', this.term);
                },

                remember: function (value, label) {
                    if (value === null || value === '') {
                        return;
                    }

                    this.labels[String(value)] = label;
                },

                isSelected: function (value) {
                    return this.selectedValue !== null && this.selectedValue === String(value);
                },

                items: function () {
                    return this.$refs.list ? Array.from(this.$refs.list.querySelectorAll('.aui-combobox-item')) : [];
                },

                /* Every row but the taggable "Add …" one, whose visibility
                   is bound to `canCreate` rather than set by `filter()`. */
                options: function () {
                    return this.items().filter(function (item) {
                        return ! item.classList.contains('aui-combobox-create');
                    });
                },

                visibleItems: function () {
                    return this.items().filter(function (item) {
                        return ! item.hidden;
                    });
                },

                typed: function (value) {
                    this.search = value;
                    this.pristine = false;
                    this.filter();
                },

                filter: function () {
                    if (this.searchProperty) {
                        return;
                    }

                    var term = this.search.trim().toLowerCase();
                    var visible = 0;

                    this.options().forEach(function (item) {
                        var text = (item.dataset.label || item.textContent || '').toLowerCase();
                        item.hidden = term !== '' && text.indexOf(term) === -1;
                        item.classList.remove('is-highlighted');

                        if (! item.hidden) {
                            visible++;
                        }
                    });

                    if (this.$refs.empty) {
                        this.$refs.empty.hidden = visible > 0 || this.canCreate;
                    }
                },

                /* Observe the list lazily: the teleported dropdown does not
                   exist yet when init() runs. */
                watchSize: function () {
                    if (! this.sizeObserver) {
                        this.sizeObserver = observeDropdown(this);
                    }
                },

                reposition: function () {
                    var place = placeDropdown(this.$refs.trigger, this.$refs.dropdown, this.$refs.list);

                    this.top = place.top;
                    this.left = place.left;
                    this.width = place.width;
                    this.maxHeight = place.maxHeight;
                },

                toggle: function () {
                    if (this.open) {
                        this.close();

                        return;
                    }

                    var self = this;

                    this.reposition();
                    this.open = true;

                    if (! this.searchProperty) {
                        this.search = '';
                        this.filter();
                    }

                    /* Prefill after filtering so the full list still shows;
                       the text is selected, so typing replaces it. */
                    var prefill = this.taggable && this.selectedLabel !== null
                        && ! (this.$refs.search && this.$refs.search.value);

                    if (prefill) {
                        this.search = this.selectedLabel;
                        this.pristine = true;
                    }

                    /* Measure again once the dropdown is displayed. */
                    this.$nextTick(function () {
                        self.watchSize();
                        self.reposition();

                        if (self.$refs.search) {
                            if (prefill) {
                                self.$refs.search.value = self.selectedLabel;
                            }

                            self.$refs.search.focus();

                            if (prefill) {
                                self.$refs.search.select();
                            }
                        }
                    });
                },

                close: function () {
                    this.open = false;

                    /* An untouched prefill is not a search the next open
                       should inherit. */
                    if (this.pristine) {
                        this.pristine = false;
                        this.search = '';

                        if (this.$refs.search) {
                            this.$refs.search.value = '';
                        }
                    }
                },

                create: function () {
                    var term = this.term;

                    if (term !== '') {
                        this.choose(term, term);
                    }
                },

                clear: function () {
                    if (this.selectedValue === null) {
                        return;
                    }

                    this.localValue = null;
                    this.write('');
                    this.$dispatch('aui-cleared');
                    this.$refs.trigger.focus();
                },

                /* Written through the hidden input so every wire:model
                   modifier keeps behaving as usual. */
                write: function (value) {
                    this.$refs.input.value = value;
                    this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
                    this.$refs.input.dispatchEvent(new Event('change', { bubbles: true }));
                },

                choose: function (value, label) {
                    this.remember(value, label);
                    this.localValue = String(value);
                    this.pristine = false;

                    /* Queued deferred so it rides along with the request the
                       value change fires — the next open starts from the
                       full list again. */
                    if (this.searchProperty) {
                        this.$wire.$set(this.searchProperty, '', false);

                        if (this.$refs.search) {
                            this.$refs.search.value = '';
                        }
                    }

                    this.search = '';
                    this.write(value);

                    this.close();
                    this.$refs.trigger.focus();
                },

                move: function (step) {
                    var items = this.visibleItems();

                    if (! items.length) {
                        return;
                    }

                    var current = items.findIndex(function (item) {
                        return item.classList.contains('is-highlighted');
                    });
                    var next = current === -1
                        ? (step > 0 ? 0 : items.length - 1)
                        : (current + step + items.length) % items.length;

                    items.forEach(function (item) {
                        item.classList.remove('is-highlighted');
                    });
                    items[next].classList.add('is-highlighted');
                    items[next].scrollIntoView({ block: 'nearest' });
                },

                chooseHighlighted: function () {
                    var items = this.visibleItems();
                    var highlighted = this.$refs.list && this.$refs.list.querySelector('.aui-combobox-item.is-highlighted');

                    /* Enter on an untouched prefill keeps the current value. */
                    if (! highlighted && this.pristine) {
                        this.close();
                        this.$refs.trigger.focus();

                        return;
                    }

                    var item = highlighted || items[0];

                    if (item) {
                        item.click();
                    }
                },
            };
        },

        /**
         * Searchable select that picks several values. With `taggable`, a
         * search term that matches no option label can be added as a value
         * of its own.
         */
        auiMultiSelect: function (config) {
            config = config || {};

            return {
                open: false,
                search: '',
                values: [],
                labels: {},
                /* Values added by typing (taggable), so Backspace can tell
                   them apart from picked options a server search has
                   filtered out of the list. */
                tags: {},
                max: config.max || null,
                searchProperty: config.searchProperty || null,
                taggable: config.taggable === true,
                createText: config.createText || 'Add ":term"',

                top: 0,
                left: 0,
                width: 0,
                maxHeight: 280,
                sizeObserver: null,

                /* Seeds come from data-* attributes so the x-data expression
                   stays constant across Livewire morphs. Scroll listening
                   runs in the capture phase, so the dropdown also follows a
                   scrolling ancestor such as a table wrapper. */
                init: function () {
                    var self = this;
                    var dataset = this.$el.dataset;

                    try {
                        Object.assign(this.labels, JSON.parse(dataset.auiLabels || '{}'));
                    } catch (e) {}

                    try {
                        this.values = this.normalize(JSON.parse(dataset.auiValues || '[]'));
                    } catch (e) {}

                    this.follow = function () {
                        if (self.open) {
                            self.reposition();
                        }
                    };

                    window.addEventListener('scroll', this.follow, true);
                    window.addEventListener('resize', this.follow);

                    /* x-modelable hands over whatever the server holds. An
                       array is kept as is, even one of integers: rewriting it
                       as strings would sync straight back to a `.live` model.
                       So every comparison below goes through String(). */
                    this.$watch('values', function (value) {
                        if (! Array.isArray(value)) {
                            self.values = self.normalize(value);
                        }
                    });
                },

                destroy: function () {
                    window.removeEventListener('scroll', this.follow, true);
                    window.removeEventListener('resize', this.follow);

                    if (this.sizeObserver) {
                        this.sizeObserver.disconnect();
                    }
                },

                normalize: function (value) {
                    if (value === null || value === undefined || value === '') {
                        return [];
                    }

                    var list = Array.isArray(value) ? value : (typeof value === 'object' ? Object.values(value) : [value]);

                    return list.map(String).filter(function (item, index, all) {
                        return item !== '' && all.indexOf(item) === index;
                    });
                },

                remember: function (value, label) {
                    if (value === null || value === '' || label === null || label === undefined) {
                        return;
                    }

                    this.labels[String(value)] = label;
                },

                /* A row marked `selected` joins the picks once, when it first
                   registers; never beyond `max`, never again after the user
                   removed it. */
                preselect: function (value) {
                    value = String(value);

                    if (! this.isSelected(value) && (! this.max || this.values.length < this.max)) {
                        this.values = this.values.concat([value]);
                    }
                },

                labelFor: function (value) {
                    return this.labels[String(value)] ?? String(value);
                },

                isSelected: function (value) {
                    value = String(value);

                    return Array.isArray(this.values) && this.values.some(function (item) {
                        return String(item) === value;
                    });
                },

                get term() {
                    return (this.search || '').trim();
                },

                /* Offer the typed text as a new value unless it already names
                   an option or a pick, or the `max` is reached. */
                get canCreate() {
                    var self = this;
                    var term = this.term.toLowerCase();

                    if (! this.taggable || term === '' || (this.max && this.values.length >= this.max)) {
                        return false;
                    }

                    var taken = this.values.some(function (item) {
                        return String(item).toLowerCase() === term || String(self.labelFor(item)).toLowerCase() === term;
                    });

                    return ! taken && ! this.options().some(function (item) {
                        return (item.dataset.label || item.textContent || '').trim().toLowerCase() === term;
                    });
                },

                get createLabel() {
                    return this.createText.replace(':term', this.term);
                },

                create: function () {
                    var term = this.term;

                    if (! this.canCreate) {
                        return;
                    }

                    this.tags[term] = true;
                    this.setSearch('');
                    this.toggleValue(term, term);
                },

                /* A typed tag goes back into the search box so it can be
                   edited and re-added. Client-side, that is any pick no
                   option row carries; with a server search the list only
                   holds the current matches, so a pick counts as a tag when
                   it was typed here or no option ever named it. */
                removeLast: function () {
                    var value = String(this.values[this.values.length - 1]);
                    var isTag = this.taggable && (this.searchProperty
                        ? this.tags[value] === true || ! (value in this.labels)
                        : ! this.options().some(function (item) {
                            return item.dataset.value === value;
                        }));

                    this.remove(value);

                    if (isTag) {
                        this.setSearch(this.labelFor(value));
                    }
                },

                /* Client-side this re-filters the rendered rows; with a
                   server search the term is sent to the search property so
                   the parent re-renders `options`. */
                setSearch: function (term) {
                    this.search = term;

                    if (! this.searchProperty) {
                        this.filter();

                        return;
                    }

                    if (this.$refs.search) {
                        this.$refs.search.value = term;
                    }

                    this.$wire.$set(this.searchProperty, term);
                },

                toggleValue: function (value, label) {
                    value = String(value);
                    this.remember(value, label);

                    if (this.isSelected(value)) {
                        this.remove(value);
                    } else if (! this.max || this.values.length < this.max) {
                        this.values = this.values.concat([value]);
                    }

                    this.changed();
                },

                remove: function (value) {
                    value = String(value);

                    this.values = this.values.filter(function (item) {
                        return String(item) !== value;
                    });

                    this.changed();
                },

                clear: function () {
                    this.values = [];
                    this.changed();
                },

                /* Chips can wrap onto a new line and move the trigger's
                   bottom edge, so re-anchor the dropdown after each change. */
                changed: function () {
                    var self = this;

                    this.$dispatch('aui-multiselect-changed', { values: this.values.slice() });

                    this.$nextTick(function () {
                        if (self.open) {
                            self.reposition();

                            if (self.$refs.search) {
                                self.$refs.search.focus();
                            }
                        }
                    });
                },

                items: function () {
                    return this.$refs.list ? Array.from(this.$refs.list.querySelectorAll('.aui-combobox-item')) : [];
                },

                /* Every row but the taggable "Add …" one, whose visibility
                   is bound to `canCreate` rather than set by `filter()`. */
                options: function () {
                    return this.items().filter(function (item) {
                        return ! item.classList.contains('aui-combobox-create');
                    });
                },

                visibleItems: function () {
                    return this.items().filter(function (item) {
                        return ! item.hidden;
                    });
                },

                filter: function () {
                    if (this.searchProperty) {
                        return;
                    }

                    var term = this.search.trim().toLowerCase();
                    var visible = 0;

                    this.options().forEach(function (item) {
                        var text = (item.dataset.label || item.textContent || '').toLowerCase();
                        item.hidden = term !== '' && text.indexOf(term) === -1;
                        item.classList.remove('is-highlighted');

                        if (! item.hidden) {
                            visible++;
                        }
                    });

                    if (this.$refs.empty) {
                        this.$refs.empty.hidden = visible > 0 || this.canCreate;
                    }
                },

                /* Observe the list lazily: the teleported dropdown does not
                   exist yet when init() runs. */
                watchSize: function () {
                    if (! this.sizeObserver) {
                        this.sizeObserver = observeDropdown(this);
                    }
                },

                reposition: function () {
                    var place = placeDropdown(this.$refs.trigger, this.$refs.dropdown, this.$refs.list);

                    this.top = place.top;
                    this.left = place.left;
                    this.width = place.width;
                    this.maxHeight = place.maxHeight;
                },

                toggle: function () {
                    if (this.open) {
                        this.close();

                        return;
                    }

                    var self = this;

                    this.reposition();
                    this.open = true;

                    /* A server search keeps its term (and its results)
                       between opens, like searchable-select. */
                    if (! this.searchProperty) {
                        this.search = '';
                        this.filter();
                    }

                    this.$nextTick(function () {
                        self.watchSize();
                        self.reposition();

                        if (self.$refs.search) {
                            self.$refs.search.focus();
                        }
                    });
                },

                close: function () {
                    this.open = false;
                },

                move: function (step) {
                    var items = this.visibleItems();

                    if (! items.length) {
                        return;
                    }

                    var current = items.findIndex(function (item) {
                        return item.classList.contains('is-highlighted');
                    });
                    var next = current === -1
                        ? (step > 0 ? 0 : items.length - 1)
                        : (current + step + items.length) % items.length;

                    items.forEach(function (item) {
                        item.classList.remove('is-highlighted');
                    });
                    items[next].classList.add('is-highlighted');
                    items[next].scrollIntoView({ block: 'nearest' });
                },

                /* With nothing highlighted, Enter adds the typed tag. */
                chooseHighlighted: function () {
                    var item = this.$refs.list && this.$refs.list.querySelector('.aui-combobox-item.is-highlighted');

                    if (item) {
                        item.click();
                    } else if (this.canCreate) {
                        this.create();
                    }
                },
            };
        },

        /**
         * Range slider, single or two-thumbed. `value` is what x-modelable
         * hands to Livewire: a number, or { min, max } in range mode.
         */
        auiSlider: function (config) {
            config = config || {};

            var min = Number(config.min) || 0;
            var max = Number(config.max) > min ? Number(config.max) : min + 100;
            var step = Number(config.step) > 0 ? Number(config.step) : 1;
            var decimals = (String(step).split('.')[1] || '').length;

            function snap(number, fallback) {
                number = parseFloat(number);

                if (isNaN(number)) {
                    return fallback;
                }

                number = Math.min(max, Math.max(min, number));

                return Number((Math.round((number - min) / step) * step + min).toFixed(decimals));
            }

            return {
                range: config.range === true,
                value: null,
                low: min,
                high: max,

                init: function () {
                    var self = this;

                    try {
                        this.value = JSON.parse(this.$el.dataset.auiValue || 'null');
                    } catch (e) {}

                    this.sync(this.value);

                    /* A server-side change arrives here through x-modelable. */
                    this.$watch('value', function (value) {
                        self.sync(value);
                    });
                },

                sync: function (value) {
                    if (! this.range) {
                        this.high = snap(value, min);

                        return;
                    }

                    value = value || {};

                    var low = snap(Array.isArray(value) ? value[0] : value.min, min);
                    var high = snap(Array.isArray(value) ? value[1] : value.max, max);

                    this.low = Math.min(low, high);
                    this.high = Math.max(low, high);
                },

                /* The thumbs never cross: a dragged thumb stops at the other. */
                setLow: function (number) {
                    this.low = Math.min(snap(number, min), this.high);
                    this.$el.querySelector('.aui-slider-input-low').value = this.low;
                    this.value = { min: this.low, max: this.high };
                },

                setHigh: function (number) {
                    if (this.range) {
                        this.high = Math.max(snap(number, max), this.low);
                        this.$el.querySelector('.aui-slider-input-high').value = this.high;
                        this.value = { min: this.low, max: this.high };

                        return;
                    }

                    this.high = snap(number, min);
                    this.value = this.high;
                },

                /* Stacked thumbs at the top end could only move left, so the
                   low thumb goes on top there or it could never be grabbed. */
                get lowOnTop() {
                    return this.low === this.high && this.high > (min + max) / 2;
                },

                get start() {
                    return this.range ? (this.low - min) / (max - min) * 100 : 0;
                },

                get end() {
                    return (this.high - min) / (max - min) * 100;
                },

                display: function (prefix, suffix) {
                    prefix = prefix || '';
                    suffix = suffix || '';

                    if (this.range) {
                        return prefix + this.low + suffix + ' – ' + prefix + this.high + suffix;
                    }

                    return prefix + this.high + suffix;
                },
            };
        },

        /** File input with a filename readout. */
        auiFile: function (config) {
            config = config || {};

            return {
                placeholder: config.placeholder || 'No file chosen',
                names: [],

                get label() {
                    if (this.names.length === 0) {
                        return this.placeholder;
                    }

                    if (this.names.length === 1) {
                        return this.names[0];
                    }

                    return this.names.length + ' files selected';
                },

                browse: function () {
                    this.$refs.input.click();
                },

                update: function (event) {
                    var files = event.target.files;

                    this.names = files ? Array.prototype.map.call(files, function (file) {
                        return file.name;
                    }) : [];
                },

                reset: function () {
                    this.$refs.input.value = '';
                    this.names = [];
                },
            };
        },

        /**
         * Tooltip: a short label shown on hover and on keyboard focus.
         *
         * The bubble is position: fixed so a scrolling table or card cannot
         * clip it, and it describes the first focusable element inside the
         * trigger (aria-describedby), so screen readers announce it too.
         */
        auiTooltip: function (config) {
            config = config || {};

            return {
                open: false,
                placement: config.placement || 'top',
                resolved: config.placement || 'top',
                delay: typeof config.delay === 'number' ? config.delay : 150,
                top: 0,
                left: 0,
                arrow: 0,
                timer: null,

                init: function () {
                    followAnchor(this);

                    var panel = this.$refs.panel;
                    var target = this.$el.querySelector('a[href], button, input, select, textarea, [tabindex]');

                    /* Plain text or an icon: make the wrapper itself reachable. */
                    if (! target) {
                        target = this.$el;
                        target.setAttribute('tabindex', '0');
                    }

                    if (panel && panel.id) {
                        var described = target.getAttribute('aria-describedby');

                        if (! described || described.split(' ').indexOf(panel.id) === -1) {
                            target.setAttribute('aria-describedby', described ? described + ' ' + panel.id : panel.id);
                        }
                    }
                },

                destroy: function () {
                    clearTimeout(this.timer);
                    unfollowAnchor(this);
                },

                show: function () {
                    var self = this;

                    clearTimeout(this.timer);

                    this.timer = setTimeout(function () {
                        self.open = true;
                        self.$nextTick(function () {
                            self.reposition();
                        });
                    }, this.delay);
                },

                hide: function () {
                    clearTimeout(this.timer);
                    this.open = false;
                },

                reposition: function () {
                    if (! this.$refs.panel) {
                        return;
                    }

                    var place = placeFloating(this.$el, this.$refs.panel, this.placement, 8);

                    this.top = place.top;
                    this.left = place.left;
                    this.arrow = place.arrow;
                    this.resolved = place.placement;
                },
            };
        },

        /**
         * Popover: a small panel of rich content opened by clicking a trigger.
         *
         * Closes on a click outside and on Esc (which hands focus back to the
         * trigger). Opened from the keyboard, focus moves into the panel.
         */
        auiPopover: function (config) {
            config = config || {};

            return {
                open: config.open === true,
                placement: config.placement || 'bottom',
                resolved: config.placement || 'bottom',
                top: 0,
                left: 0,
                arrow: 0,

                init: function () {
                    var self = this;

                    followAnchor(this);

                    /* The control in the trigger slot announces the panel. */
                    var trigger = this.$refs.trigger;
                    var control = trigger && (trigger.querySelector('button, a[href], [tabindex]') || trigger);

                    if (control) {
                        control.setAttribute('aria-haspopup', 'dialog');
                        control.setAttribute('aria-expanded', this.open ? 'true' : 'false');

                        if (trigger.dataset.auiControls) {
                            control.setAttribute('aria-controls', trigger.dataset.auiControls);
                        }
                    }

                    this.$watch('open', function (value) {
                        if (control) {
                            control.setAttribute('aria-expanded', value ? 'true' : 'false');
                        }

                        if (value) {
                            self.$nextTick(function () {
                                self.reposition();
                            });
                        }

                        self.$dispatch(value ? 'aui-popover-open' : 'aui-popover-close');
                    });

                    if (this.open) {
                        this.$nextTick(function () {
                            self.reposition();
                        });
                    }
                },

                destroy: function () {
                    unfollowAnchor(this);
                },

                /* A click with detail 0 came from Enter or Space on the trigger. */
                toggle: function (event) {
                    if (this.open) {
                        this.hide();

                        return;
                    }

                    this.show(event && event.detail === 0);
                },

                show: function (focusPanel) {
                    var self = this;

                    this.open = true;

                    if (! focusPanel) {
                        return;
                    }

                    /* x-show reveals the panel after the next tick. */
                    this.$nextTick(function () {
                        setTimeout(function () {
                            var panel = self.$refs.panel;
                            var first = panel && panel.querySelector('a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])');

                            if (panel) {
                                (first || panel).focus();
                            }
                        });
                    });
                },

                hide: function (restoreFocus) {
                    if (! this.open) {
                        return;
                    }

                    this.open = false;

                    if (restoreFocus) {
                        var trigger = this.$refs.trigger;
                        var target = trigger && (trigger.querySelector('a[href], button, input, [tabindex]') || trigger);

                        if (target && typeof target.focus === 'function') {
                            target.focus();
                        }
                    }
                },

                reposition: function () {
                    if (! this.$refs.trigger || ! this.$refs.panel) {
                        return;
                    }

                    var place = placeFloating(this.$refs.trigger, this.$refs.panel, this.placement, 10);

                    this.top = place.top;
                    this.left = place.left;
                    this.arrow = place.arrow;
                    this.resolved = place.placement;
                },
            };
        },

        /**
         * Wizard: a form split into steps, with a stepper header and
         * Back / Next / Finish buttons.
         *
         * Each <x-avian::wizard.step> is a panel; the header is built from
         * their titles. Next only moves on once the current panel's fields
         * pass the browser's own validation (required, min, pattern...).
         * `step` is x-modelable, so wire:model can follow it.
         */
        auiWizard: function (config) {
            config = config || {};

            return {
                step: 1,
                steps: [],
                linear: config.linear !== false,

                init: function () {
                    var self = this;

                    this.steps = this.panels().map(function (panel) {
                        return {
                            title: panel.dataset.title || '',
                            description: panel.dataset.description || '',
                        };
                    });

                    this.step = this.clamp(parseInt(this.$el.dataset.auiStep, 10) || 1);

                    this.$watch('step', function (value) {
                        var step = self.clamp(parseInt(value, 10) || 1);

                        if (step !== value) {
                            self.step = step;

                            return;
                        }

                        self.$dispatch('aui-wizard-change', { step: step, total: self.steps.length });
                    });
                },

                /* This wizard's own panels, in document order — not a nested wizard's. */
                panels: function () {
                    var root = this.$root;

                    return Array.prototype.filter.call(root.querySelectorAll('[data-aui-wizard-step]'), function (panel) {
                        return panel.closest('[data-aui-wizard]') === root;
                    });
                },

                clamp: function (step) {
                    return Math.max(1, Math.min(step, Math.max(1, this.steps.length)));
                },

                isActive: function (panel) {
                    return this.panels().indexOf(panel) + 1 === this.step;
                },

                status: function (index) {
                    var number = index + 1;

                    if (number < this.step) {
                        return 'complete';
                    }

                    return number === this.step ? 'current' : 'upcoming';
                },

                get isFirst() {
                    return this.step <= 1;
                },

                get isLast() {
                    return this.step >= this.steps.length;
                },

                /* Reports the first invalid field of the current panel, if any. */
                validate: function () {
                    var panel = this.panels()[this.step - 1];

                    if (! panel) {
                        return true;
                    }

                    var fields = panel.querySelectorAll('input, select, textarea');

                    for (var i = 0; i < fields.length; i++) {
                        if (typeof fields[i].checkValidity === 'function' && ! fields[i].checkValidity()) {
                            fields[i].reportValidity();

                            return false;
                        }
                    }

                    return true;
                },

                next: function () {
                    if (this.isLast || ! this.validate()) {
                        return;
                    }

                    this.step += 1;
                    this.focusPanel();
                },

                back: function () {
                    if (this.isFirst) {
                        return;
                    }

                    this.step -= 1;
                    this.focusPanel();
                },

                /* A linear wizard only jumps back to steps already done. */
                goTo: function (number) {
                    if (number === this.step) {
                        return;
                    }

                    if (this.linear && number > this.step) {
                        return;
                    }

                    this.step = this.clamp(number);
                    this.focusPanel();
                },

                canGoTo: function (index) {
                    return ! this.linear || index + 1 < this.step;
                },

                /* The Finish button is a submit button: block it while the
                   last panel is invalid, otherwise let the form submit. */
                finish: function (event) {
                    if (! this.validate()) {
                        event.preventDefault();

                        return;
                    }

                    this.$dispatch('aui-wizard-finish', { step: this.step });
                },

                /* Moves focus to the new panel so screen readers start there.
                   x-show reveals it after the next tick, so wait one more. */
                focusPanel: function () {
                    var self = this;

                    this.$nextTick(function () {
                        setTimeout(function () {
                            var panel = self.panels()[self.step - 1];

                            if (panel) {
                                panel.focus({ preventScroll: true });
                            }
                        });
                    });
                },
            };
        },

        /**
         * Date range: a flatpickr range picker that submits two fields,
         * name[from] and name[to], in a fixed value format (Y-m-d by
         * default) whatever the display format is. Presets fill common
         * ranges. `value` is x-modelable: { from: '...', to: '...' }.
         */
        auiDateRange: function (config) {
            config = config || {};

            return {
                value: { from: '', to: '' },
                format: config.format || 'Y-m-d',

                init: function () {
                    var self = this;

                    try {
                        this.value = this.normalize(JSON.parse(this.$el.dataset.auiValue || 'null'));
                    } catch (e) {}

                    /* A server-side change arrives here through x-modelable. */
                    this.$watch('value', function (value) {
                        self.syncPicker(self.normalize(value));
                    });
                },

                normalize: function (value) {
                    value = value || {};

                    return {
                        from: typeof value.from === 'string' ? value.from : '',
                        to: typeof value.to === 'string' ? value.to : '',
                    };
                },

                picker: function () {
                    return (this.$refs.input && this.$refs.input._flatpickr) || null;
                },

                /* flatpickr fires change after each click: the first click of a
                   range only picks the start, so wait for the second. */
                changed: function () {
                    var picker = this.picker();

                    if (! picker) {
                        return;
                    }

                    var dates = picker.selectedDates;

                    if (dates.length === 1) {
                        return;
                    }

                    this.value = {
                        from: dates[0] ? picker.formatDate(dates[0], this.format) : '',
                        to: dates[1] ? picker.formatDate(dates[1], this.format) : '',
                    };
                },

                syncPicker: function (value) {
                    var picker = this.picker();

                    if (! picker) {
                        return;
                    }

                    var current = picker.selectedDates.map(function (date) {
                        return picker.formatDate(date, this.format);
                    }, this);

                    if (current[0] === (value.from || undefined) && current[1] === (value.to || undefined)) {
                        return;
                    }

                    if (! value.from) {
                        picker.clear(false);

                        return;
                    }

                    picker.setDate([value.from, value.to || value.from], false, this.format);
                },

                preset: function (key) {
                    var range = DATE_PRESETS[key];

                    if (! range) {
                        return;
                    }

                    var dates = range(startOfDay(new Date()));
                    var picker = this.picker();

                    if (picker) {
                        picker.setDate(dates, true);

                        return;
                    }

                    this.value = {
                        from: formatDateValue(dates[0], this.format),
                        to: formatDateValue(dates[1], this.format),
                    };
                },

                isPreset: function (key) {
                    var range = DATE_PRESETS[key];

                    if (! range || ! this.value.from) {
                        return false;
                    }

                    var dates = range(startOfDay(new Date()));

                    return formatDateValue(dates[0], this.format) === this.value.from
                        && formatDateValue(dates[1], this.format) === this.value.to;
                },

                clear: function () {
                    var picker = this.picker();

                    if (picker) {
                        picker.clear(false);
                    } else if (this.$refs.input) {
                        this.$refs.input.value = '';
                    }

                    this.value = { from: '', to: '' };
                },
            };
        },

        /** Copy button: copies a string and shows "copied" for a moment. */
        auiCopy: function () {
            return {
                copied: false,
                timer: null,

                copy: function (text) {
                    var self = this;

                    copyText(String(text || '')).then(function () {
                        self.copied = true;
                        clearTimeout(self.timer);
                        self.timer = setTimeout(function () {
                            self.copied = false;
                        }, 2000);
                        self.$dispatch('aui-copied', { text: text });
                    }, function () {});
                },

                destroy: function () {
                    clearTimeout(this.timer);
                },
            };
        },

        /**
         * Tree view, following the WAI-ARIA tree pattern.
         *
         * Open branches, checked nodes and the focused node are kept here as
         * ids, and every node binds to them, so a Livewire morph does not
         * collapse the tree. `value` (x-modelable) is the list of checked ids.
         */
        auiTree: function (config) {
            config = config || {};

            return {
                selectable: config.selectable === true,
                value: [],
                opened: [],
                allOpen: false,
                focused: null,
                typed: '',
                typedTimer: null,

                init: function () {
                    var self = this;
                    var state = {};

                    try {
                        state = JSON.parse(this.$el.dataset.auiTree || '{}') || {};
                    } catch (e) {}

                    this.allOpen = state.open === true;
                    this.opened = Array.isArray(state.open) ? state.open.map(String) : [];
                    this.value = Array.isArray(state.checked) ? state.checked.map(String) : [];

                    var first = this.items()[0];
                    this.focused = state.active && this.node(state.active) ? String(state.active) : (first ? first.dataset.auiNode : null);

                    /* A server-side change arrives here through x-modelable:
                       normalise it, so a checked branch checks all of it. */
                    this.$watch('value', function (value) {
                        var normal = self.normalize(Array.isArray(value) ? value.map(String) : []);

                        if (normal.join('|') !== (value || []).map(String).join('|')) {
                            self.value = normal;
                        }
                    });
                },

                /* Every node, in document order. */
                items: function () {
                    return Array.prototype.slice.call(this.$root.querySelectorAll('[role="treeitem"]'));
                },

                node: function (id) {
                    return this.items().filter(function (item) {
                        return item.dataset.auiNode === String(id);
                    })[0] || null;
                },

                childNodes: function (item) {
                    var group = item.querySelector(':scope > [role="group"]');

                    return group ? Array.prototype.slice.call(group.querySelectorAll(':scope > [role="treeitem"]')) : [];
                },

                parentNode: function (item) {
                    var parent = item.parentElement && item.parentElement.closest('[role="treeitem"]');

                    return parent && this.$root.contains(parent) ? parent : null;
                },

                /* The nodes not hidden inside a closed branch. */
                visibleItems: function () {
                    var self = this;

                    return this.items().filter(function (item) {
                        var parent = self.parentNode(item);

                        while (parent) {
                            if (! self.isOpen(parent.dataset.auiNode)) {
                                return false;
                            }

                            parent = self.parentNode(parent);
                        }

                        return true;
                    });
                },

                isOpen: function (id) {
                    return this.allOpen || this.opened.indexOf(String(id)) !== -1;
                },

                setOpen: function (id, open) {
                    id = String(id);

                    if (this.allOpen && ! open) {
                        /* Turn "all open" into an explicit list before closing one. */
                        this.allOpen = false;
                        this.opened = this.items().filter(function (item) {
                            return item.hasAttribute('aria-expanded');
                        }).map(function (item) {
                            return item.dataset.auiNode;
                        });
                    }

                    var index = this.opened.indexOf(id);

                    if (open && index === -1) {
                        this.opened.push(id);
                    } else if (! open && index !== -1) {
                        this.opened.splice(index, 1);
                    }
                },

                toggle: function (id) {
                    this.setOpen(id, ! this.isOpen(id));
                },

                /* 'true', 'false' or 'mixed', derived from the leaves under a branch. */
                checkState: function (id) {
                    var item = this.node(id);

                    if (! item) {
                        return 'false';
                    }

                    var children = this.childNodes(item);

                    if (children.length === 0) {
                        return this.value.indexOf(String(id)) !== -1 ? 'true' : 'false';
                    }

                    var states = children.map(function (child) {
                        return this.checkState(child.dataset.auiNode);
                    }, this);

                    if (states.every(function (state) { return state === 'true'; })) {
                        return 'true';
                    }

                    return states.some(function (state) { return state !== 'false'; }) ? 'mixed' : 'false';
                },

                /* Checking a node checks (or clears) everything under it; then
                   each branch above is checked exactly when all of it is. */
                check: function (id) {
                    if (! this.selectable) {
                        return;
                    }

                    var item = this.node(id);

                    if (! item) {
                        return;
                    }

                    var on = this.checkState(id) !== 'true';
                    var ids = this.value.slice();
                    var branch = [item].concat(Array.prototype.slice.call(item.querySelectorAll('[role="treeitem"]')));

                    branch.forEach(function (node) {
                        var nodeId = node.dataset.auiNode;
                        var index = ids.indexOf(nodeId);

                        if (on && index === -1) {
                            ids.push(nodeId);
                        } else if (! on && index !== -1) {
                            ids.splice(index, 1);
                        }
                    });

                    this.value = this.withBranches(ids);
                },

                /* Adds every fully checked branch, removes partly checked ones. */
                withBranches: function (ids) {
                    var self = this;
                    var leaves = this.items().filter(function (item) {
                        return self.childNodes(item).length === 0;
                    });
                    var checkedLeaves = leaves.filter(function (leaf) {
                        return ids.indexOf(leaf.dataset.auiNode) !== -1;
                    }).map(function (leaf) {
                        return leaf.dataset.auiNode;
                    });

                    var result = checkedLeaves.slice();

                    this.items().forEach(function (item) {
                        if (self.childNodes(item).length === 0) {
                            return;
                        }

                        var under = Array.prototype.filter.call(item.querySelectorAll('[role="treeitem"]'), function (node) {
                            return self.childNodes(node).length === 0;
                        });

                        if (under.length && under.every(function (leaf) { return checkedLeaves.indexOf(leaf.dataset.auiNode) !== -1; })) {
                            result.push(item.dataset.auiNode);
                        }
                    });

                    /* Document order, so the submitted list is stable. */
                    var order = this.items().map(function (item) { return item.dataset.auiNode; });

                    return result.sort(function (a, b) {
                        return order.indexOf(a) - order.indexOf(b);
                    });
                },

                /* A checked branch in an incoming value checks all of it. */
                normalize: function (ids) {
                    var expanded = ids.slice();

                    ids.forEach(function (id) {
                        var item = this.node(id);

                        if (item) {
                            Array.prototype.forEach.call(item.querySelectorAll('[role="treeitem"]'), function (node) {
                                if (expanded.indexOf(node.dataset.auiNode) === -1) {
                                    expanded.push(node.dataset.auiNode);
                                }
                            });
                        }
                    }, this);

                    return this.withBranches(expanded);
                },

                /* A row click: a link follows itself, a checkbox tree toggles
                   the check, anything else opens or closes the branch. */
                rowClick: function (id, event) {
                    var item = this.node(id);

                    this.focus(item);

                    if (event.target.closest('a[href]')) {
                        return;
                    }

                    if (this.selectable) {
                        this.check(id);

                        return;
                    }

                    this.activate(item);
                },

                activate: function (item) {
                    var id = item.dataset.auiNode;
                    var link = item.querySelector(':scope > .aui-tree-row a[href]');

                    if (link) {
                        link.click();

                        return;
                    }

                    if (this.selectable) {
                        this.check(id);

                        return;
                    }

                    if (this.childNodes(item).length) {
                        this.toggle(id);
                    }

                    var label = item.querySelector(':scope > .aui-tree-row .aui-tree-label');

                    this.$dispatch('aui-tree-select', { id: id, label: label ? label.textContent.trim() : '' });
                },

                focus: function (item) {
                    if (! item) {
                        return;
                    }

                    this.focused = item.dataset.auiNode;
                    item.focus({ preventScroll: false });
                },

                keydown: function (event) {
                    var item = event.target.closest('[role="treeitem"]');

                    if (! item || event.altKey || event.ctrlKey || event.metaKey) {
                        return;
                    }

                    var id = item.dataset.auiNode;
                    var visible = this.visibleItems();
                    var index = visible.indexOf(item);
                    var hasChildren = this.childNodes(item).length > 0;
                    var handled = true;

                    switch (event.key) {
                        case 'ArrowDown':
                            this.focus(visible[index + 1]);
                            break;
                        case 'ArrowUp':
                            this.focus(visible[index - 1]);
                            break;
                        case 'Home':
                            this.focus(visible[0]);
                            break;
                        case 'End':
                            this.focus(visible[visible.length - 1]);
                            break;
                        case 'ArrowRight':
                            if (hasChildren && ! this.isOpen(id)) {
                                this.setOpen(id, true);
                            } else if (hasChildren) {
                                this.focus(this.childNodes(item)[0]);
                            }
                            break;
                        case 'ArrowLeft':
                            if (hasChildren && this.isOpen(id)) {
                                this.setOpen(id, false);
                            } else {
                                this.focus(this.parentNode(item));
                            }
                            break;
                        case 'Enter':
                            this.activate(item);
                            break;
                        case ' ':
                            if (this.selectable) {
                                this.check(id);
                            } else {
                                this.activate(item);
                            }
                            break;
                        case '*':
                            /* Opens every sibling branch at this level. */
                            Array.prototype.forEach.call(item.parentElement.children, function (sibling) {
                                if (sibling.hasAttribute('aria-expanded')) {
                                    this.setOpen(sibling.dataset.auiNode, true);
                                }
                            }, this);
                            break;
                        default:
                            handled = this.typeahead(event.key, visible, index);
                    }

                    if (handled) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                },

                /* Typing jumps to the next visible node starting with the typed text. */
                typeahead: function (key, visible, index) {
                    if (key.length !== 1 || ! /\S/.test(key)) {
                        return false;
                    }

                    var self = this;

                    clearTimeout(this.typedTimer);
                    this.typed += key.toLowerCase();
                    this.typedTimer = setTimeout(function () {
                        self.typed = '';
                    }, 500);

                    var ordered = visible.slice(index + 1).concat(visible.slice(0, index + 1));
                    var match = ordered.filter(function (node) {
                        var label = node.querySelector(':scope > .aui-tree-row .aui-tree-label');

                        return label && label.textContent.trim().toLowerCase().indexOf(self.typed) === 0;
                    })[0];

                    this.focus(match);

                    return true;
                },
            };
        },

        /**
         * Command palette: a shortcut opens a search box over the page.
         *
         * The input keeps focus the whole time (the list is a listbox driven
         * by aria-activedescendant), so focus never leaves the dialog. It goes
         * back to where it was on close.
         */
        auiCommand: function (config) {
            config = config || {};

            var shortcut = parseShortcut(config.shortcut);

            return {
                open: false,
                name: config.name || null,
                server: config.server === true,
                query: '',
                empty: false,
                active: null,
                returnFocus: null,
                observer: null,

                init: function () {
                    var self = this;

                    this.$watch('open', function (value) {
                        value ? lockScroll() : unlockScroll();
                    });

                    /* Items morphed in by Livewire (server search) or re-rendered:
                       refilter and keep a valid highlight. Attribute changes are
                       ignored, so hiding items here cannot loop. */
                    if (typeof MutationObserver !== 'undefined' && this.$refs.list) {
                        this.observer = new MutationObserver(function () {
                            self.refresh();
                        });

                        this.observer.observe(this.$refs.list, { childList: true, subtree: true });
                    }

                    this.refresh();
                },

                destroy: function () {
                    if (this.observer) {
                        this.observer.disconnect();
                    }

                    if (this.open) {
                        unlockScroll();
                    }
                },

                shortcutPressed: function (event) {
                    if (shortcut && matchesShortcut(event, shortcut)) {
                        event.preventDefault();
                        this.open ? this.hide() : this.show();
                    }
                },

                openFrom: function (event) {
                    var target = eventName(event);

                    if (! target || target === this.name) {
                        this.show();
                    }
                },

                show: function () {
                    var self = this;

                    if (this.open) {
                        return;
                    }

                    this.returnFocus = document.activeElement;
                    this.open = true;

                    /* x-show reveals the dialog after the next tick. */
                    this.$nextTick(function () {
                        setTimeout(function () {
                            self.$refs.input.focus();
                            self.$refs.input.select();
                            self.refresh();
                        });
                    });
                },

                hide: function () {
                    if (! this.open) {
                        return;
                    }

                    this.open = false;

                    var target = this.returnFocus;
                    this.returnFocus = null;

                    if (target && typeof target.focus === 'function' && document.contains(target)) {
                        target.focus();
                    }
                },

                allItems: function () {
                    return Array.prototype.slice.call(this.$refs.list.querySelectorAll('[data-aui-command-item]'));
                },

                visible: function () {
                    return this.allItems().filter(function (item) {
                        return ! item.hidden;
                    });
                },

                search: function (text) {
                    this.query = String(text || '');

                    if (! this.server) {
                        this.refresh();
                    }
                },

                /* Client-side filter: every word of the query must appear in
                   the item's text or keywords. Server mode leaves it alone. */
                refresh: function () {
                    var words = this.server ? [] : this.query.toLowerCase().split(/\s+/).filter(Boolean);
                    var counter = 0;

                    this.allItems().forEach(function (item) {
                        if (! item.id) {
                            item.id = 'aui-command-item-' + (++commandIds);
                        }

                        var haystack = (item.textContent + ' ' + (item.dataset.keywords || '')).toLowerCase();
                        var match = words.every(function (word) {
                            return haystack.indexOf(word) !== -1;
                        });

                        item.hidden = ! match;
                        counter += match ? 1 : 0;
                    });

                    Array.prototype.forEach.call(this.$refs.list.querySelectorAll('[data-aui-command-group]'), function (group) {
                        group.hidden = ! group.querySelector('[data-aui-command-item]:not([hidden])');
                    });

                    this.empty = counter === 0;

                    var visible = this.visible();

                    this.highlight(visible.indexOf(this.active) !== -1 ? this.active : visible[0] || null);
                },

                highlight: function (item) {
                    this.allItems().forEach(function (other) {
                        other.classList.toggle('is-active', other === item);
                        other.setAttribute('aria-selected', other === item ? 'true' : 'false');
                    });

                    this.active = item || null;

                    if (this.$refs.input) {
                        if (item) {
                            this.$refs.input.setAttribute('aria-activedescendant', item.id);
                        } else {
                            this.$refs.input.removeAttribute('aria-activedescendant');
                        }
                    }
                },

                move: function (step) {
                    var visible = this.visible();

                    if (! visible.length) {
                        return;
                    }

                    var index = visible.indexOf(this.active);
                    var next = visible[(index + step + visible.length) % visible.length];

                    this.highlight(next);
                    next.scrollIntoView({ block: 'nearest' });
                },

                moveTo: function (end) {
                    var visible = this.visible();
                    var item = end === 'first' ? visible[0] : visible[visible.length - 1];

                    if (item) {
                        this.highlight(item);
                        item.scrollIntoView({ block: 'nearest' });
                    }
                },

                /* Enter clicks the highlighted item, so links navigate and
                   wire:click / x-on:click handlers run as for a mouse click. */
                chooseActive: function () {
                    if (this.active) {
                        this.active.click();
                    }
                },

                chosen: function (item) {
                    /* Open the modal once this click is over, or the modal reads
                       the very same click as a click outside and closes. */
                    if (item.dataset.modal) {
                        var modal = item.dataset.modal;

                        setTimeout(function () {
                            window.dispatchEvent(new CustomEvent('aui-modal-open', { detail: { name: modal } }));
                        });
                    }

                    this.$dispatch('aui-command-select', {
                        value: item.dataset.value || null,
                        label: (item.querySelector('.aui-command-text') || item).textContent.trim(),
                    });

                    /* A modal takes focus itself: don't send it back behind it. */
                    if (item.dataset.modal) {
                        this.returnFocus = null;
                    }

                    this.hide();
                },
            };
        },
    };

    /*
     * x-data resolves an unknown name against the global scope, so exposing the
     * factories here keeps the components working even when this file is loaded
     * after Alpine has already started.
     */
    Object.keys(components).forEach(function (name) {
        if (! window[name]) {
            window[name] = components[name];
        }
    });

    var registered = false;

    function register(Alpine) {
        if (registered || ! Alpine) {
            return;
        }

        registered = true;

        Object.keys(components).forEach(function (name) {
            Alpine.data(name, components[name]);
        });
    }

    /* Imperative API for code that is not running inside Alpine. */
    window.AvianUI = window.AvianUI || {
        openModal: function (name) {
            window.dispatchEvent(new CustomEvent('aui-modal-open', { detail: { name: name } }));
        },
        closeModal: function (name) {
            window.dispatchEvent(new CustomEvent('aui-modal-close', { detail: { name: name || null } }));
        },
        /* Drawers share the modal's events. */
        openDrawer: function (name) {
            window.AvianUI.openModal(name);
        },
        closeDrawer: function (name) {
            window.AvianUI.closeModal(name);
        },
        confirm: confirmDialog,
        toast: toast,
        openCommand: function (name) {
            window.dispatchEvent(new CustomEvent('aui-command-open', { detail: { name: name || null } }));
        },
        closeCommand: function () {
            window.dispatchEvent(new CustomEvent('aui-command-close'));
        },
        copy: copyText,
        /* Replace an editor's content, or insert HTML at its cursor. */
        setEditor: function (name, html) {
            window.dispatchEvent(new CustomEvent('aui-editor-set', { detail: { name: name, html: html } }));
        },
        insertIntoEditor: function (name, html) {
            window.dispatchEvent(new CustomEvent('aui-editor-insert', { detail: { name: name, html: html } }));
        },
    };

    document.addEventListener('alpine:init', function () {
        register(window.Alpine);
    });

    /* wire:navigate replaces the body: never leave it locked behind. */
    document.addEventListener('livewire:navigating', releaseScroll);
})();
