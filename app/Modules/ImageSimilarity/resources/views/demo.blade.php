<x-layout title="Image Similarity">
    <div class="mx-auto max-w-2xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Image Similarity</h1>
            <p class="mt-2 text-sm text-fg-subtle">Upload an image to rank a seeded set of real photos by visual similarity.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Image Similarity" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    status: 'idle',
                    error: null,
                    results: [],

                    async submit(event) {
                        this.error = null;
                        this.status = 'loading';

                        const response = await fetch('{{ route('image-similarity.upload') }}', {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            },
                            body: new FormData(event.target),
                        });

                        const body = await response.json();

                        if (! response.ok) {
                            this.error = body.message ?? 'unavailable';
                            this.status = 'idle';
                            return;
                        }

                        this.results = body.results;
                        this.status = 'idle';
                    },
                }"
            >
                <form @submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">
                    <input
                        type="file"
                        name="image"
                        accept="image/*"
                        required
                        class="block w-full text-sm text-fg-muted file:mr-4 file:rounded-lg file:border-0 file:bg-surface-hover file:px-4 file:py-2 file:text-sm file:font-medium"
                    >

                    <button
                        type="submit"
                        :disabled="status === 'loading'"
                        class="rounded-lg bg-accent px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        Find similar images
                    </button>
                </form>

                <p x-show="error" x-text="error" class="mt-4 text-sm text-red-500"></p>

                <div class="mt-10" x-show="results.length">
                    <p class="text-sm font-medium text-fg-muted">Nearest matches, closest first:</p>
                    <ul class="mt-3 grid grid-cols-3 gap-4 sm:grid-cols-5">
                        <template x-for="result in results" :key="result.id">
                            <li class="text-center">
                                <img :src="result.image" :alt="result.label" class="aspect-square w-full rounded-lg border border-border object-cover">
                                <p class="mt-1.5 truncate text-xs text-fg-subtle" x-text="result.label"></p>
                                <p class="font-mono text-[11px] text-fg-subtle" x-text="result.distance.toFixed(3)"></p>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="mt-12 border-t border-border pt-8">
                    <p class="text-sm font-medium text-fg-muted">Seeded demo set ({{ $seeded->count() }} images)</p>
                    <ul class="mt-3 grid grid-cols-4 gap-3 sm:grid-cols-8">
                        @foreach ($seeded as $image)
                            <li class="text-center">
                                <img src="{{ $image->image }}" alt="{{ $image->label }}" class="aspect-square w-full rounded-lg border border-border object-cover">
                                <p class="mt-1 truncate text-[11px] text-fg-subtle">{{ $image->label }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    </div>
</x-layout>
