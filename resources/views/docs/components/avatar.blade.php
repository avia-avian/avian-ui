@php
    $props = [
        ['name', 'string|null', 'null', 'Person\'s name. Used for the initials (first letter of the first two words) and as the image alt text.'],
        ['src', 'string|null', 'null', 'Image URL. When set, the photo is shown instead of initials.'],
        ['initials', 'string|null', 'null', 'Override the generated initials.'],
        ['size', "'sm'|'lg'|null", 'null', 'Avatar size. Omit for the default size.'],
    ];

    $examples = [
        [
            'title' => 'Initials or photo',
            'text' => 'Pass the name always, and the photo when there is one — the name becomes the alt text.',
            'code' => <<<'BLADE'
                <x-avian::avatar name="Ada Lovelace" />
                <x-avian::avatar :name="$user->name" :src="$user->avatar_url" />
                BLADE,
        ],
        [
            'title' => 'Fallback when the photo is optional',
            'text' => 'A null src falls back to initials, so no @if is needed.',
            'code' => <<<'BLADE'
                <x-avian::avatar
                    :name="$user->name"
                    :src="$user->avatar_path ? Storage::url($user->avatar_path) : null"
                />
                BLADE,
        ],
        [
            'title' => 'Sizes and custom initials',
            'code' => <<<'BLADE'
                <x-avian::avatar name="Grace Hopper" size="sm" />
                <x-avian::avatar name="Grace Hopper" size="lg" />
                <x-avian::avatar name="Avian Brands" initials="AB" />
                BLADE,
        ],
        [
            'title' => 'User cell in a table',
            'code' => <<<'BLADE'
                <td>
                    <div class="aui-row">
                        <x-avian::avatar :name="$user->name" size="sm" />
                        <span>{{ $user->name }}</span>
                    </div>
                </td>
                BLADE,
        ],
    ];
@endphp

@include('avian-ui::docs.partials.header', ['title' => 'Avatar', 'subtitle' => 'User photo or initials'])
<p class="aui-showcase-lead">
    A round avatar that shows a person's photo, or their initials when there is no photo. Use it in
    headers, comment lists and user columns.
</p>

<div class="aui-showcase-demo">
    <div class="aui-row" style="flex-wrap: wrap">
        <x-avian::avatar name="Ada Lovelace" size="sm" />
        <x-avian::avatar name="Ada Lovelace" />
        <x-avian::avatar name="Ada Lovelace" size="lg" />
        <x-avian::avatar name="Grace Brewster Hopper" />
        <x-avian::avatar name="Avian Brands" initials="AV" />
        <div class="aui-row" style="margin-left: 16px">
            <x-avian::avatar name="Alan Turing" size="sm" />
            <span>Alan Turing</span>
        </div>
    </div>
</div>

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">How it works</h2>
    <ul class="aui-showcase-list">
        <li>Initials are the first letter of the first two words of <code>name</code>, uppercased — "Grace Brewster Hopper" becomes <code>GB</code>. Multibyte names work.</li>
        <li>With <code>src</code> the image fills the circle; with neither <code>src</code> nor <code>name</code> the avatar is empty.</li>
    </ul>
</div>

@include('avian-ui::docs.partials.props')

<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Examples</h2>
    @foreach ($examples as $example)
        @include('avian-ui::docs.partials.example', ['example' => $example])
    @endforeach
</div>
