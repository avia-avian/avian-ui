{{--
    A vertical list of events in order: an order's history, an audit log,
    a project's milestones.

        <x-avian::timeline>
            @foreach ($order->events as $event)
                <x-avian::timeline.item :title="$event->title" :time="$event->created_at" relative>
                    {{ $event->note }}
                </x-avian::timeline.item>
            @endforeach
        </x-avian::timeline>
--}}
@props([
    'size' => null,
])

<ol {{ $attributes->class(['aui-timeline', 'aui-timeline-'.$size => filled($size)]) }}>
    {{ $slot }}
</ol>
