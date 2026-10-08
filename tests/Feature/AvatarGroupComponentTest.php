<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Blade;

function avatarGroupUsers(int $count): array
{
    return array_map(fn (int $i): array => ['name' => "Person {$i}", 'avatar_url' => $i === 1 ? '/p1.jpg' : null], range(1, $count));
}

it('draws an avatar with a tooltip and an accessible label per user', function () {
    $html = Blade::render('<x-avian::avatar-group :users="$users" label="Team" />', ['users' => avatarGroupUsers(2)]);

    expect($html)->toContain('class="aui-avatar-group"')
        ->toContain('role="group"')
        ->toContain('aria-label="Team"')
        ->toContain('<img src="/p1.jpg" alt="Person 1">')
        ->toContain('aria-label="Person 2"')
        ->toContain('role="img"')
        ->and(substr_count($html, 'aui-tooltip-trigger'))->toBe(2)
        ->and(substr_count($html, 'class="aui-avatar"'))->toBe(2);
});

it('collapses the users past max into a +N chip naming them', function () {
    $html = Blade::render('<x-avian::avatar-group :users="$users" :max="3" />', ['users' => avatarGroupUsers(5)]);

    expect($html)->toContain('aui-avatar-more')
        ->toContain('aria-label="2 more"')
        ->toContain('>+2</span>')
        ->toContain('>Person 4, Person 5</span>')
        ->not->toContain('aria-label="Person 4"');
});

it('counts the hidden names past ten in the chip tooltip', function () {
    $html = Blade::render('<x-avian::avatar-group :users="$users" :max="1" />', ['users' => avatarGroupUsers(13)]);

    expect($html)->toContain('>+12</span>')
        ->toContain('Person 11 and 2 more')
        ->not->toContain('Person 12,');
});

it('renders no chip when everyone fits', function () {
    expect(Blade::render('<x-avian::avatar-group :users="$users" :max="5" />', ['users' => avatarGroupUsers(3)]))
        ->not->toContain('aui-avatar-more');
});

it('reads custom name and image keys from models', function () {
    $users = [
        (object) ['full_name' => 'Rina Wijaya', 'photo' => '/rina.jpg'],
        (object) ['full_name' => 'Budi Santoso', 'photo' => null],
    ];

    $html = Blade::render('<x-avian::avatar-group :users="$users" name-key="full_name" src-key="photo" size="sm" />', ['users' => $users]);

    expect($html)->toContain('class="aui-avatar-group aui-avatar-group-sm"')
        ->toContain('<img src="/rina.jpg" alt="Rina Wijaya">')
        ->toContain('aui-avatar-sm')
        ->toContain('BS');
});

it('leaves out the tooltips when asked', function () {
    $html = Blade::render('<x-avian::avatar-group :users="$users" :max="1" :tooltips="false" />', ['users' => avatarGroupUsers(3)]);

    expect($html)->not->toContain('aui-tooltip')
        ->toContain('aria-label="Person 1"')
        ->toContain('aria-label="2 more"');
});

it('renders avatars passed in the slot', function () {
    $html = Blade::render(<<<'BLADE'
        <x-avian::avatar-group>
            <x-avian::avatar name="Rina Wijaya" />
        </x-avian::avatar-group>
        BLADE);

    expect($html)->toContain('class="aui-avatar-group"')
        ->toContain('RW');
});

it('escapes names', function () {
    expect(Blade::render('<x-avian::avatar-group :users="[[\'name\' => \'<b>X</b>\']]" />'))
        ->toContain('&lt;b&gt;X&lt;/b&gt;')
        ->not->toContain('<b>X</b>');
});
