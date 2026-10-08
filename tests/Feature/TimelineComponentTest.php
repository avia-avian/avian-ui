<?php

declare(strict_types=1);

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

afterEach(function () {
    Carbon::setTestNow();
});

it('renders a timeline as an ordered list of events', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::timeline size="sm">
            <x-avian::timeline.item title="Shipped" time="Today" icon="fas fa-truck" variant="success">On its way.</x-avian::timeline.item>
            <x-avian::timeline.item title="Ordered" />
        </x-avian::timeline>
        BLADE);

    expect($html)->toContain('<ol class="aui-timeline aui-timeline-sm">')
        ->toContain('class="aui-timeline-item aui-tone-success"')
        ->toContain('aui-timeline-marker aui-timeline-marker-icon')
        ->toContain('<i class="fas fa-truck"></i>')
        ->toContain('<span class="aui-timeline-title">Shipped</span>')
        ->toContain('>Today</time>')
        ->toContain('<div class="aui-timeline-body">On its way.</div>')
        ->toContain('<span class="aui-timeline-title">Ordered</span>');
});

it('leaves out the time and body of an item that has none', function () {
    expect(Blade::render('<x-avian::timeline.item title="Ordered" />'))
        ->not->toContain('<time')
        ->not->toContain('aui-timeline-body')
        ->not->toContain('aui-timeline-marker-icon');
});

it('formats a timeline date and keeps it machine readable', function () {
    $html = Blade::render('<x-avian::timeline.item title="Paid" :time="$time" time-format="Y-m-d" />', [
        'time' => Carbon::parse('2026-03-04 10:30:00', 'UTC'),
    ]);

    expect($html)->toContain('datetime="2026-03-04T10:30:00+00:00"')
        ->toContain('>2026-03-04</time>')
        ->not->toContain('title=');
});

it('shows a relative timeline date with the exact one as its title', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-04 12:00:00'));

    $html = Blade::render('<x-avian::timeline.item title="Paid" :time="now()->subHours(3)" time-format="Y-m-d H:i" relative />');

    expect($html)->toContain('>3 hours ago</time>')
        ->toContain('title="2026-03-04 09:00"');
});

it('renders a plain timeline without a size modifier and merges extra attributes', function () {
    expect(Blade::render('<x-avian::timeline class="mt-4" id="history"><x-avian::timeline.item title="Created" /></x-avian::timeline>'))
        ->toContain('<ol class="aui-timeline mt-4" id="history">')
        ->not->toContain('aui-timeline-sm');
});

it('colours a timeline item with the primary tone by default', function () {
    expect(Blade::render('<x-avian::timeline.item title="Created" />'))
        ->toContain('class="aui-timeline-item aui-tone-primary"')
        ->toContain('<span class="aui-timeline-marker" aria-hidden="true">');
});

it('colours a timeline item with each documented variant', function (string $variant) {
    expect(Blade::render('<x-avian::timeline.item title="Created" :variant="$variant" />', ['variant' => $variant]))
        ->toContain('class="aui-timeline-item aui-tone-'.$variant.'"');
})->with(['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'neutral', 'dark', 'purple', 'indigo', 'teal', 'orange', 'pink']);

it('merges classes and attributes onto a timeline item', function () {
    expect(Blade::render('<x-avian::timeline.item title="Created" class="is-current" id="event-1" data-id="7" />'))
        ->toContain('class="aui-timeline-item aui-tone-primary is-current"')
        ->toContain('id="event-1"')
        ->toContain('data-id="7"');
});

it('prints a string time as is without a machine readable date', function () {
    expect(Blade::render('<x-avian::timeline.item title="Shipped" time="Yesterday" relative time-format="Y" />'))
        ->toMatch('/<time class="aui-timeline-time"\s*>Yesterday<\/time>/')
        ->not->toContain('datetime=')
        ->not->toContain('title=');
});

it('formats a timeline date with the default format', function () {
    expect(Blade::render('<x-avian::timeline.item title="Paid" :time="$time" />', [
        'time' => Carbon::parse('2026-03-04 15:05:00', 'UTC'),
    ]))->toContain('>Mar 4, 2026 3:05 PM</time>');
});

it('accepts any date time implementation as a timeline time', function () {
    $html = Blade::render('<x-avian::timeline.item title="Paid" :time="$time" time-format="d/m/Y" />', [
        'time' => new DateTimeImmutable('2026-03-04 10:30:00', new DateTimeZone('UTC')),
    ]);

    expect($html)->toContain('datetime="2026-03-04T10:30:00+00:00"')
        ->toContain('>04/03/2026</time>');
});

it('shows a relative timeline date with the default format as its title', function () {
    Carbon::setTestNow(Carbon::parse('2026-03-04 12:00:00', 'UTC'));

    expect(Blade::render('<x-avian::timeline.item title="Paid" :time="now()->subDays(2)" relative />'))
        ->toContain('>2 days ago</time>')
        ->toContain('title="Mar 2, 2026 12:00 PM"');
});

it('renders a timeline item with only a time and a body', function () {
    $html = Blade::render('<x-avian::timeline.item time="09:00">Standup</x-avian::timeline.item>');

    expect($html)->toContain('<div class="aui-timeline-header">')
        ->toContain('>09:00</time>')
        ->toContain('<div class="aui-timeline-body">Standup</div>')
        ->not->toContain('aui-timeline-title');
});

it('leaves out the header of a timeline item without a title or time', function () {
    expect(Blade::render('<x-avian::timeline.item>Just a note</x-avian::timeline.item>'))
        ->not->toContain('aui-timeline-header')
        ->toContain('<div class="aui-timeline-body">Just a note</div>');
});

it('escapes the title, time and icon of a timeline item', function () {
    $html = Blade::render('<x-avian::timeline.item :title="$title" :time="$time" :icon="$icon" />', [
        'title' => '<script>alert(1)</script>',
        'time' => '<b>now</b>',
        'icon' => 'fas" onclick="x',
    ]);

    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')
        ->toContain('&lt;b&gt;now&lt;/b&gt;')
        ->toContain('<i class="fas&quot; onclick=&quot;x"></i>')
        ->not->toContain('<script>')
        ->not->toContain('<b>now</b>');
});
