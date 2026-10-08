{{--
    The toast stack. Place it once in the layout, next to `<x-avian::scripts />`:

        <x-avian::toasts />

    From a redirect — `success`, `error`, `warning` and `info` flashes are
    picked up automatically, and `toast` takes the full options:

        return back()->with('success', 'Order saved.');
        return back()->with('toast', ['variant' => 'warning', 'title' => 'Heads up', 'message' => 'Stock is low.']);

    From JavaScript, Alpine or Livewire:

        AvianUI.toast('Order saved.', 'success')
        AvianUI.toast({ title: 'Export ready', message: 'Check your inbox.', variant: 'info', duration: 0 })
        $dispatch('aui-toast', { message: 'Copied!' })
        $this->dispatch('aui-toast', message: 'Order saved.', variant: 'success');

    Options: `message`, `title`, `variant` (any shared color — success,
    danger / error, warning, info, neutral get a matching icon; `success` by
    default), `duration` in ms (`0` keeps it until
    dismissed), `icon` (a class, or `false` for none) and `dismissible`.
    Hovering or focusing a toast pauses its timer.
--}}
@props([
    'position' => 'top-right',
    'duration' => 5000,
    'max' => 5,
    'flash' => true,
])

@php
    $config = [
        'duration' => (int) $duration,
        'max' => (int) $max,
        'toasts' => $flash ? app(\AvianUi\AvianUi\AvianUi::class)->flashedToasts() : [],
    ];
@endphp

<div
    {{ $attributes->class(['aui-toasts', 'aui-toasts-'.$position]) }}
    x-data="auiToasts({{ Illuminate\Support\Js::from($config) }})"
    role="region"
    aria-label="{{ __('avian-ui::messages.notifications') }}"
>
    <template x-for="toast in toasts" x-bind:key="toast.id">
        <div
            class="aui-toast"
            x-bind:class="'aui-tone-' + toast.variant"
            x-bind:role="toast.variant === 'danger' ? 'alert' : 'status'"
            x-show="toast.visible"
            x-transition:enter="aui-toast-transition"
            x-transition:enter-start="aui-toast-hidden"
            x-transition:leave="aui-toast-transition"
            x-transition:leave-end="aui-toast-hidden"
            x-on:mouseenter="pause(toast)"
            x-on:mouseleave="resume(toast)"
            x-on:focusin="pause(toast)"
            x-on:focusout="resume(toast)"
        >
            <template x-if="toast.icon">
                <i class="aui-toast-icon" x-bind:class="toast.icon" aria-hidden="true"></i>
            </template>

            <div class="aui-toast-content">
                <p class="aui-toast-title" x-show="toast.title" x-text="toast.title"></p>
                <p class="aui-toast-message" x-show="toast.message" x-text="toast.message"></p>
            </div>

            <button
                type="button"
                class="aui-toast-close"
                x-show="toast.dismissible"
                x-on:click="dismiss(toast.id)"
                aria-label="{{ __('avian-ui::messages.dismiss') }}"
            >
                <i class="fas fa-xmark" aria-hidden="true"></i>
            </button>

            <span
                class="aui-toast-progress"
                x-show="toast.duration > 0"
                x-bind:class="{ 'is-paused': toast.paused }"
                x-bind:style="'animation-duration: ' + toast.duration + 'ms'"
                aria-hidden="true"
            ></span>
        </div>
    </template>
</div>
