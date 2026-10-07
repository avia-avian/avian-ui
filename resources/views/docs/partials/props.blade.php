{{-- Props reference table. Expects `$props`: a list of [name, type, default, description]. --}}
<div class="aui-showcase-block">
    <h2 class="aui-showcase-heading">Props</h2>

    <div class="aui-showcase-props">
        <x-avian::table :headers="['Prop', 'Type', 'Default', 'Description']" :hover="false">
            @foreach ($props as [$prop, $type, $default, $description])
                <tr data-search-prop="{{ $prop }}">
                    <td><code>{{ $prop }}</code></td>
                    <td><code class="aui-showcase-type">{{ $type }}</code></td>
                    <td><code class="aui-showcase-type">{{ $default }}</code></td>
                    <td>{{ $description }}</td>
                </tr>
            @endforeach
        </x-avian::table>
    </div>

    <p class="aui-showcase-note">
        Any other attribute (<code>class</code>, <code>id</code>, <code>disabled</code>, <code>x-*</code>,
        <code>wire:*</code>…) is forwarded to the rendered element.
    </p>
</div>
