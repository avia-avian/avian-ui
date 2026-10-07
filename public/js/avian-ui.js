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

    var components = {
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
                max: config.max || null,
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

                    this.search = '';
                    this.toggleValue(term, term);
                    this.filter();
                },

                /* A typed tag (no option row carries its value) goes back
                   into the search box so it can be edited and re-added. */
                removeLast: function () {
                    var value = this.values[this.values.length - 1];
                    var isTag = this.taggable && ! this.options().some(function (item) {
                        return item.dataset.value === value;
                    });

                    this.remove(value);

                    if (isTag) {
                        this.search = this.labelFor(value);
                        this.filter();
                    }
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
                    this.search = '';
                    this.filter();

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
    };

    document.addEventListener('alpine:init', function () {
        register(window.Alpine);
    });

    /* wire:navigate replaces the body: never leave it locked behind. */
    document.addEventListener('livewire:navigating', releaseScroll);
})();
