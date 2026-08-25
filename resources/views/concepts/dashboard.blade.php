<x-layout title="Concepts">
    <div class="mx-auto max-w-3xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Concept Registry</h1>
            <p class="mt-2 text-sm text-fg-subtle">Every proof-of-concept module on this platform, gated live behind its own feature flag.</p>
        </header>

        @forelse ($groups as $category => $entries)
            <section class="mt-12">
                <h2 class="text-xs font-medium tracking-widest text-fg-subtle uppercase">{{ $category }}</h2>

                <ul class="mt-4 divide-y divide-border rounded-xl border border-border bg-surface">
                    @foreach ($entries as $entry)
                        @php $concept = $entry['concept']; @endphp
                        <li
                            x-data="conceptToggle('{{ $concept->slug }}', @js($entry['active']))"
                            class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-surface-hover"
                        >
                            <div class="min-w-0">
                                <p class="font-medium">
                                    @if (Route::has($concept->demoRoute))
                                        <a href="{{ route($concept->demoRoute) }}" class="transition-colors hover:text-accent">{{ $concept->name }}</a>
                                    @else
                                        {{ $concept->name }}
                                    @endif
                                    <span class="ml-2 font-mono text-xs text-fg-subtle">{{ $concept->slug }}</span>
                                </p>
                                <p class="mt-1 truncate text-sm text-fg-muted">{{ $concept->description }}</p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <span
                                    class="text-xs font-medium"
                                    :class="active ? 'text-accent' : 'text-fg-subtle'"
                                    x-text="active ? 'On' : 'Off'"
                                >{{ $entry['active'] ? 'On' : 'Off' }}</span>

                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="active.toString()"
                                    @click="toggle()"
                                    :disabled="pending"
                                    class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-accent focus-visible:ring-offset-2 focus-visible:ring-offset-bg disabled:opacity-50"
                                    :class="active ? 'bg-accent' : 'bg-surface-hover border border-border-strong'"
                                >
                                    <span
                                        class="inline-block h-4 w-4 transform rounded-full bg-fg shadow transition-transform"
                                        :class="active ? 'translate-x-6' : 'translate-x-1'"
                                    ></span>
                                </button>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="mt-12 text-sm text-fg-subtle">No concepts registered yet.</p>
        @endforelse
    </div>
</x-layout>
