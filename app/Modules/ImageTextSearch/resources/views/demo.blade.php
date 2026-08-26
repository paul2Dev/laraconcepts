<x-layout title="Image Text Search">
    <div class="mx-auto max-w-2xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Image Text Search</h1>
            <p class="mt-2 text-sm text-fg-subtle">Type a text query to rank a seeded set of real photos by how well they match it — no keyword overlap with a filename or label required.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Image Text Search" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    query: @js($query),
                    results: @js($results),
                    status: 'idle',
                    initialResults: @js($results),

                    async search() {
                        if (! this.query.trim()) return;

                        this.status = 'loading';

                        const params = new URLSearchParams({ query: this.query });
                        const response = await fetch(`{{ route('image-text-search.demo') }}?${params}`, {
                            headers: { Accept: 'application/json' },
                        });

                        const body = await response.json();
                        this.results = body.results;
                        this.status = 'idle';
                    },

                    reset() {
                        this.query = '';
                        this.results = this.initialResults;
                    },
                }"
            >
                <form @submit.prevent="search" class="flex items-center gap-3">
                    <input
                        type="text"
                        name="query"
                        x-model="query"
                        placeholder="e.g. golden hour over a field"
                        class="block w-full rounded-lg border border-border bg-surface px-3 py-2.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >

                    <button
                        type="submit"
                        :disabled="status === 'loading' || ! query.trim()"
                        class="shrink-0 rounded-lg bg-accent px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        Search
                    </button>

                    <button
                        type="button"
                        @click="reset"
                        :disabled="status === 'loading'"
                        class="shrink-0 rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-fg-muted hover:bg-surface-hover disabled:opacity-50"
                    >
                        Reset
                    </button>
                </form>

                <div class="mt-10">
                    <p
                        class="text-sm font-medium text-fg-muted"
                        x-text="results.length && results[0].distance !== undefined ? 'Nearest matches, closest first:' : `All seeded photos (${results.length}):`"
                    ></p>
                    <ul class="mt-3 grid grid-cols-3 gap-4 sm:grid-cols-5">
                        <template x-for="result in results" :key="result.id">
                            <li class="text-center">
                                <img :src="result.image" :alt="result.label" class="aspect-square w-full rounded-lg border border-border object-cover">
                                <p class="mt-1.5 truncate text-xs text-fg-subtle" x-text="result.label"></p>
                                <p x-show="result.distance !== undefined" class="font-mono text-[11px] text-fg-subtle" x-text="result.distance?.toFixed(3)"></p>
                            </li>
                        </template>
                    </ul>

                    <p x-show="results.length === 0" class="mt-3 text-sm text-fg-subtle">No results.</p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
