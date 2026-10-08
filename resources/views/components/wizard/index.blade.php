{{--
    A form split into steps, with a stepper header and Back / Next / Finish:

        <form method="POST" action="{{ route('accounts.store') }}">
            @csrf
            <x-avian::wizard>
                <x-avian::wizard.step title="Account">
                    <x-avian::input name="email" type="email" label="Email" required />
                </x-avian::wizard.step>
                <x-avian::wizard.step title="Profile" description="Optional">
                    <x-avian::input name="name" label="Name" />
                </x-avian::wizard.step>
                <x-avian::wizard.step title="Confirm">...</x-avian::wizard.step>
            </x-avian::wizard>
        </form>

    Every step stays in the form, so one submit sends all of them. Next only
    moves on when the current step's fields pass the browser's validation
    (required, type, min, pattern...). Finish is a submit button, so it also
    works with `wire:submit` on a Livewire form. `wire:model` on the wizard
    follows the step number. A `linear` wizard (the default) only lets the
    header jump back to steps already done. Events: `aui-wizard-change`
    and `aui-wizard-finish`.
--}}
@props([
    'step' => 1,
    'linear' => true,
    'nextLabel' => null,
    'backLabel' => null,
    'finishLabel' => null,
    'finishIcon' => 'fas fa-check',
])

@php
    $modelAttributes = $attributes->whereStartsWith('wire:model');
    $rootAttributes = $attributes->except(array_keys($modelAttributes->getAttributes()));

    // Placeholders, so the translated sentence is filled in on the client.
    $stepOf = __('avian-ui::messages.step_of', ['current' => '__current__', 'total' => '__total__']);
@endphp

<div
    x-data="auiWizard({ linear: @js((bool) $linear) })"
    x-modelable="step"
    {{ $modelAttributes }}
    data-aui-wizard
    data-aui-step="{{ (int) $step }}"
    {{ $rootAttributes->class(['aui-wizard']) }}
>
    {{-- Built from the steps' titles once Alpine has read them. --}}
    <ol class="aui-stepper aui-wizard-header" aria-label="{{ __('avian-ui::messages.progress') }}">
        <template x-for="(item, index) in steps" :key="index">
            <li class="aui-step" x-bind:class="'is-' + status(index)" x-bind:aria-current="status(index) === 'current' ? 'step' : null">
                <button
                    type="button"
                    class="aui-step-inner"
                    x-on:click="goTo(index + 1)"
                    x-bind:disabled="! canGoTo(index) && status(index) !== 'current'"
                >
                    <span class="aui-step-marker" aria-hidden="true">
                        <i class="fas fa-check" x-show="status(index) === 'complete'"></i>
                        <span x-show="status(index) !== 'complete'" x-text="index + 1"></span>
                    </span>
                    <span class="aui-step-text">
                        <span class="aui-step-title" x-text="item.title"></span>
                        <span class="aui-step-description" x-show="item.description" x-text="item.description"></span>
                    </span>
                </button>
            </li>
        </template>
    </ol>

    <p
        class="aui-sr-only"
        aria-live="polite"
        data-template="{{ $stepOf }}"
        x-text="$el.dataset.template.replace('__current__', step).replace('__total__', steps.length)"
    ></p>

    <div class="aui-wizard-body">
        {{ $slot }}
    </div>

    <div class="aui-wizard-footer">
        <x-avian-ui::button variant="light" icon="fas fa-arrow-left" x-on:click="back()" x-show="! isFirst" x-cloak>
            {{ $backLabel ?? __('avian-ui::messages.back') }}
        </x-avian-ui::button>

        <span class="aui-wizard-spacer"></span>

        <x-avian-ui::button icon-right="fas fa-arrow-right" x-on:click="next()" x-show="! isLast">
            {{ $nextLabel ?? __('avian-ui::messages.next') }}
        </x-avian-ui::button>

        <x-avian-ui::button type="submit" :icon="$finishIcon" x-on:click="finish($event)" x-show="isLast" x-cloak>
            {{ $finishLabel ?? __('avian-ui::messages.finish') }}
        </x-avian-ui::button>
    </div>
</div>
