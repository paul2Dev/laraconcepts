<x-layout title="Semantic Search">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Semantic Search</h1>
            <p class="mt-2 text-sm text-fg-subtle">Search a seeded product catalog, toggling between plain keyword matching and embedding-based semantic ranking.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Semantic Search" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    query: @js($query),
                    mode: @js($mode),
                    results: @js($results),
                    status: 'idle',
                    initialQuery: @js($query),
                    initialMode: @js($mode),
                    initialResults: @js($results),

                    async search() {
                        this.status = 'loading';

                        const params = new URLSearchParams({ query: this.query, mode: this.mode });
                        const response = await fetch(`{{ route('semantic-search.demo') }}?${params}`, {
                            headers: { Accept: 'application/json' },
                        });

                        const body = await response.json();
                        this.results = body.results;
                        this.status = 'idle';
                    },

                    reset() {
                        this.query = this.initialQuery;
                        this.mode = this.initialMode;
                        this.results = this.initialResults;
                    },
                }"
            >
                <form @submit.prevent="search" class="space-y-5">
                    <input
                        type="text"
                        name="query"
                        x-model="query"
                        placeholder="e.g. notebook"
                        class="block w-full rounded-lg border border-border bg-surface px-3 py-2.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >

                    <div class="flex items-center gap-6 text-sm text-fg-muted">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="mode" value="semantic" x-model="mode">
                            Semantic
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="mode" value="keyword" x-model="mode">
                            Keyword
                        </label>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="submit"
                            :disabled="status === 'loading'"
                            class="rounded-lg bg-accent px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                        >
                            Search
                        </button>

                        <button
                            type="button"
                            @click="reset"
                            :disabled="status === 'loading'"
                            class="rounded-lg border border-border px-5 py-2.5 text-sm font-medium text-fg-muted hover:bg-surface-hover disabled:opacity-50"
                        >
                            Reset
                        </button>
                    </div>
                </form>

                <ul class="mt-10 divide-y divide-border rounded-xl border border-border bg-surface">
                    <template x-for="result in results" :key="result.id">
                        <li class="flex items-center justify-between gap-4 px-4 py-3.5 text-sm">
                            <span x-text="result.title"></span>
                            <span x-show="result.score !== undefined" class="font-mono text-xs text-fg-subtle" x-text="result.score?.toFixed(3)"></span>
                        </li>
                    </template>

                    <li x-show="results.length === 0" class="px-4 py-3.5 text-sm text-fg-subtle">No results.</li>
                </ul>
            </div>
        @endif
    </div>
</x-layout>
