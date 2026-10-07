{{-- One usage example. Expects `$example` with a `title`, a `code` and an optional `text` explanation. --}}
<div class="aui-showcase-example" data-search-example="{{ $example['title'] }}">
    <h3 class="aui-showcase-example-title">{{ $example['title'] }}</h3>

    @if (filled($example['text'] ?? null))
        <p class="aui-showcase-text">{{ $example['text'] }}</p>
    @endif

    <div class="aui-showcase-code-wrap" x-data="showcaseCopy">
        <div class="aui-showcase-code-bar">
            <span>Code</span>

            <button
                type="button"
                class="aui-showcase-copy"
                x-on:click="copy()"
                x-bind:class="{ 'is-copied': copied }"
                x-bind:aria-label="copied ? 'Copied' : 'Copy code'"
                aria-label="Copy code"
            >
                <i class="far fa-copy" x-show="! copied" aria-hidden="true"></i>
                <i class="fas fa-check" x-show="copied" x-cloak aria-hidden="true"></i>
                <span x-text="copied ? 'Copied' : 'Copy'">Copy</span>
            </button>
        </div>

        <pre class="aui-showcase-code"><code x-ref="code">{{ $example['code'] }}</code></pre>
    </div>
</div>
