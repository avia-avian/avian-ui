@php
    $props = [
        ['name', 'string|null', 'null', 'Input name; also the key for validation errors and old input.'],
        ['value', 'string|null', 'null', 'Initial value, written in `date-format`. Falls back to old input.'],
        ['mode', "'single'|'multiple'|'range'|'time'", "'single'", 'One date, several dates, a from–to range, or a time only (no calendar).'],
        ['date-format', 'string', "'d/m/Y' ('H:i' for time)", 'Flatpickr format of the value shown and submitted (Y-m-d, d/m/Y H:i, …).'],
        ['enable-time', 'bool', 'false', 'Adds a time picker under the calendar. Include H:i in date-format.'],
        ['time-24hr', 'bool', 'true', 'Use a 24-hour clock whenever a time is picked; set :time24hr="false" for AM/PM (and use h:i K).'],
        ['min-time', 'string|null', 'null', 'Earliest selectable time, e.g. "08:00". Only with a clock.'],
        ['max-time', 'string|null', 'null', 'Latest selectable time.'],
        ['min-date', 'string|null', 'null', 'Earliest selectable date ("today" or a date in date-format).'],
        ['max-date', 'string|null', 'null', 'Latest selectable date.'],
        ['placeholder', 'string|null', 'null', 'Placeholder text.'],
        ['label', 'string|null', 'null', 'Label shown above the input.'],
        ['hint', 'string|null', 'null', 'Helper text under the input.'],
        ['error', 'string|null', 'null', 'Force an error message; otherwise read from $errors.'],
        ['error-bag', 'string|null', 'null', 'Named error bag to read from.'],
        ['required', 'bool', 'false', 'Asterisk on the label + native required attribute.'],
        ['size', "'sm'|'lg'|null", 'null', 'Control height.'],
        ['disabled', 'bool', 'false', 'Disables the input.'],
        ['field', 'bool', 'true', 'Set :field="false" to render just the input.'],
    ];

    $examples = [
        [
            'title' => '1. Load flatpickr in your layout (once)',
            'text' => 'The package does not bundle flatpickr. Load it in the host app and upgrade every .flatpickr-input using the data-fp-* attributes the component renders.',
            'code' => <<<'BLADE'
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
                <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                <script>
                    function initDatepickers() {
                        document.querySelectorAll('.flatpickr-input:not(.flatpickr-ready)').forEach((input) => {
                            input.classList.add('flatpickr-ready');
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
                    }

                    document.addEventListener('DOMContentLoaded', initDatepickers);
                    document.addEventListener('livewire:navigated', initDatepickers);
                </script>
                BLADE,
        ],
        [
            'title' => '2. Use the component',
            'code' => <<<'BLADE'
                <x-avian::datepicker name="start_date" label="Start date" placeholder="dd/mm/yyyy" />
                BLADE,
        ],
        [
            'title' => 'Match the format with your validation',
            'text' => 'The value is submitted exactly as displayed. Validate with the same format, then parse it — or use date-format="Y-m-d" to store it directly.',
            'code' => <<<'BLADE'
                <x-avian::datepicker name="start_date" label="Start date" date-format="d/m/Y" />

                $data = $request->validate(['start_date' => ['required', 'date_format:d/m/Y']]);
                $start = Carbon::createFromFormat('d/m/Y', $data['start_date']);
                BLADE,
        ],
        [
            'title' => 'Range, time, limits',
            'text' => 'A range submits one string such as "01/10/2026 to 07/10/2026" — split it on " to " on the server.',
            'code' => <<<'BLADE'
                <x-avian::datepicker name="period" label="Period" mode="range" />
                <x-avian::datepicker name="appointment" label="Appointment" enable-time date-format="d/m/Y H:i" />
                <x-avian::datepicker name="due_date" label="Due date" min-date="today" />
                BLADE,
        ],
        [
            'title' => 'Time only',
            'text' => 'mode="time" hides the calendar and submits just the time ("08:30"). Bound it with min-time / max-time.',
            'code' => <<<'BLADE'
                <x-avian::datepicker name="opens_at" label="Opens at" mode="time" min-time="08:00" max-time="17:00" />
                <x-avian::datepicker name="reminder" label="Reminder" mode="time" :time24hr="false" date-format="h:i K" />
                BLADE,
        ],
        [
            'title' => 'Editing an existing date',
            'text' => 'Format the stored date into date-format before passing it.',
            'code' => <<<'BLADE'
                <x-avian::datepicker name="due_date" label="Due date" :value="old('due_date', $task->due_date?->format('d/m/Y'))" />
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Datepicker', 'subtitle' => 'Calendar popup powered by flatpickr'])
<p class="aui-showcase-lead">
    A text input that opens a calendar. The component renders the input and its settings as
    <code>data-fp-*</code> attributes; <a href="https://flatpickr.js.org" target="_blank" rel="noopener">flatpickr</a>,
    loaded by your app, turns it into the picker. Supports single dates, multiple dates, ranges, date + time and time only.
</p>

<div class="aui-showcase-demo">
    <div class="aui-form-grid">
        <x-avian::datepicker name="datepicker_start" label="Start date" placeholder="dd/mm/yyyy" />
        <x-avian::datepicker name="datepicker_period" label="Period" mode="range" placeholder="Pick a range" />
        <x-avian::datepicker name="datepicker_appointment" label="Appointment" enable-time date-format="d/m/Y H:i" placeholder="dd/mm/yyyy hh:mm" />
        <x-avian::datepicker name="datepicker_due" label="Due date" min-date="today" hint="Past dates are disabled (min-date=today)." placeholder="dd/mm/yyyy" />
        <x-avian::datepicker name="datepicker_opens" label="Opens at (time only)" mode="time" min-time="08:00" max-time="17:00" hint="Between 08:00 and 17:00." placeholder="hh:mm" />
        <x-avian::datepicker name="datepicker_reminder" label="Reminder (12-hour)" mode="time" :time24hr="false" date-format="h:i K" placeholder="hh:mm AM" />
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Without flatpickr loaded the component is just a plain text input — nothing breaks, you just don't get the calendar.</li>
        <li>flatpickr makes the input <code>readonly</code> so users pick from the calendar. Pass <code>allowInput: true</code> in your flatpickr config if typing should be allowed.</li>
        <li>The default <code>date-format</code> is <code>d/m/Y</code>. Whatever you choose is what reaches the server, so validate with the same format.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Setup &amp; examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
