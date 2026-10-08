@php
    $props = [
        ['step', 'int', '1', 'The step it opens on. With wire:model, the step follows the property.'],
        ['linear', 'bool', 'true', 'Clicking the header only goes back to steps already done. Set :linear="false" to jump freely.'],
        ['next-label', 'string|null', "'Next'", 'Label of the Next button.'],
        ['back-label', 'string|null', "'Back'", 'Label of the Back button.'],
        ['finish-label', 'string|null', "'Finish'", 'Label of the last step\'s submit button.'],
        ['finish-icon', 'string|null', "'fas fa-check'", 'Icon of the Finish button.'],
    ];

    $examples = [
        [
            'title' => 'A plain form',
            'text' => 'Every step stays inside the form, so one submit sends all the fields, and validation errors come back the usual way.',
            'code' => <<<'BLADE'
                <form method="POST" action="{{ route('accounts.store') }}">
                    @csrf

                    <x-avian::wizard finish-label="Create account">
                        <x-avian::wizard.step title="Account" description="Sign-in details">
                            <x-avian::input name="email" type="email" label="Email" required />
                            <x-avian::input name="password" type="password" label="Password" required minlength="8" />
                        </x-avian::wizard.step>

                        <x-avian::wizard.step title="Profile">
                            <x-avian::input name="name" label="Full name" required />
                        </x-avian::wizard.step>

                        <x-avian::wizard.step title="Confirm">
                            <x-avian::checkbox name="terms" label="I accept the terms" required />
                        </x-avian::wizard.step>
                    </x-avian::wizard>
                </form>
                BLADE,
        ],
        [
            'title' => 'With Livewire',
            'text' => 'wire:model keeps the step in a property, so the server can move the user to the step that failed validation.',
            'code' => <<<'BLADE'
                <form wire:submit="save">
                    <x-avian::wizard wire:model="step">
                        ...
                    </x-avian::wizard>
                </form>

                // In the component
                public int $step = 1;

                public function save()
                {
                    try {
                        $this->validate();
                    } catch (ValidationException $e) {
                        $this->step = isset($e->errors()['email']) ? 1 : 2;

                        throw $e;
                    }
                }
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Wizard', 'subtitle' => 'A form split into steps'])
<p class="aui-showcase-lead">
    Breaks a long form into steps with a progress header and Back / Next / Finish buttons. Next only
    moves on when the current step's fields are valid.
</p>

<div class="aui-showcase-demo">
    <form x-data x-on:submit.prevent="window.AvianUI.toast({ variant: 'success', message: 'Account created' })">
        <x-avian::wizard finish-label="Create account">
            <x-avian::wizard.step title="Account" description="Sign-in details">
                <div class="aui-form-grid">
                    <x-avian::input name="wizard_email" type="email" label="Email" required />
                    <x-avian::input name="wizard_password" type="password" label="Password" required minlength="8" />
                </div>
            </x-avian::wizard.step>

            <x-avian::wizard.step title="Profile" description="About you">
                <x-avian::input name="wizard_name" label="Full name" required />
            </x-avian::wizard.step>

            <x-avian::wizard.step title="Confirm">
                <x-avian::checkbox name="wizard_terms" label="I accept the terms" required />
            </x-avian::wizard.step>
        </x-avian::wizard>
    </form>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Each <code>&lt;x-avian::wizard.step&gt;</code> is a panel. Its <code>title</code> and <code>description</code> label it in the header.</li>
        <li>Next checks the current step with the browser's own validation (<code>required</code>, <code>type</code>, <code>min</code>, <code>pattern</code>…) and points at the first invalid field.</li>
        <li>Finish is a submit button: it submits the surrounding form, or triggers <code>wire:submit</code>.</li>
        <li>Focus moves to each new step, and the step change is announced to screen readers.</li>
        <li>It dispatches <code>aui-wizard-change</code> (with <code>step</code> and <code>total</code>) and <code>aui-wizard-finish</code>.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
