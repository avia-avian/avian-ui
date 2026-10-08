{{--
    Overlapping avatars, for assignees, team members or viewers:

        <x-avian::avatar-group :users="$task->assignees" :max="4" size="sm" />

        <x-avian::avatar-group>
            <x-avian::avatar :src="$owner->avatar_url" :name="$owner->name" />
            <x-avian::avatar name="Rina Wijaya" />
        </x-avian::avatar-group>

    `users` is any list of models or arrays; `name-key` and `src-key` pick the
    attributes (name and avatar_url by default). Past `max`, the rest become
    one "+N" chip whose tooltip names them. Each avatar gets its name as a
    tooltip and an accessible label. `max` only applies to `users`: Blade
    cannot count the avatars in a slot.
--}}
@props([
    'users' => null,
    'max' => null,
    'size' => null,
    'nameKey' => 'name',
    'srcKey' => 'avatar_url',
    'tooltips' => true,
    'label' => null,
])

@php
    $people = collect(is_iterable($users) ? $users : [])
        ->map(fn (mixed $user): array => [
            'name' => (string) data_get($user, $nameKey, ''),
            'src' => data_get($user, $srcKey),
        ])
        ->values();

    $limit = is_numeric($max) && (int) $max > 0 ? (int) $max : null;
    $shown = $limit !== null ? $people->take($limit) : $people;
    $hidden = $limit !== null ? $people->slice($limit) : collect();

    // The chip's tooltip names the first ten hidden people, then counts the rest.
    $hiddenNames = $hidden->take(10)->pluck('name')->filter()->implode(', ');

    if ($hidden->count() > 10) {
        $hiddenNames .= ' '.__('avian-ui::messages.and_more', ['count' => $hidden->count() - 10]);
    }

    $more = __('avian-ui::messages.more_people', ['count' => $hidden->count()]);
@endphp

<div {{ $attributes->class(['aui-avatar-group', 'aui-avatar-group-'.$size => filled($size)]) }} role="group" @if (filled($label)) aria-label="{{ $label }}" @endif>
    @foreach ($shown as $person)
        @if ($tooltips && filled($person['name']))
            <x-avian-ui::tooltip :text="$person['name']">
                <x-avian-ui::avatar :src="$person['src']" :name="$person['name']" :size="$size" role="img" :aria-label="$person['name']" />
            </x-avian-ui::tooltip>
        @else
            <x-avian-ui::avatar :src="$person['src']" :name="$person['name']" :size="$size" role="img" :aria-label="$person['name'] ?: null" />
        @endif
    @endforeach

    {{ $slot }}

    @if ($hidden->isNotEmpty())
        @if ($tooltips && $hiddenNames !== '')
            <x-avian-ui::tooltip :text="$hiddenNames">
                <span @class(['aui-avatar', 'aui-avatar-more', 'aui-avatar-'.$size => filled($size)]) role="img" aria-label="{{ $more }}">+{{ $hidden->count() }}</span>
            </x-avian-ui::tooltip>
        @else
            <span @class(['aui-avatar', 'aui-avatar-more', 'aui-avatar-'.$size => filled($size)]) role="img" aria-label="{{ $more }}">+{{ $hidden->count() }}</span>
        @endif
    @endif
</div>
