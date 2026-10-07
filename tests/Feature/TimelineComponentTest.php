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
        ->toContain('class="aui-timeline-item aui-timeline-item-success"')
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
