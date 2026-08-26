<x-layout title="Welcome">
    <div class="flex min-h-screen items-center justify-center px-6 py-16">
        <div class="w-full max-w-lg rounded-xl border border-border bg-surface p-8">
            <h1 class="text-2xl font-semibold tracking-tight">Laravel Concepts Platform</h1>
            <p class="mt-2 text-sm text-fg-subtle">A sandbox Laravel app for building one proof-of-concept feature per module, each toggleable live via a feature flag.</p>

            <div class="mt-6 space-y-4 text-sm text-fg-muted">
                <p>
                    Every module registers itself in <code class="rounded bg-surface-hover px-1.5 py-0.5 font-mono text-xs text-fg">ConceptRegistry</code>
                    and shows up on the concept dashboard grouped by category, with a live on/off toggle backed by
                    <a href="https://laravel.com/docs/pennant" target="_blank" class="underline underline-offset-4 hover:text-accent">Laravel Pennant</a>.
                    Flip a module on, follow its demo link, and see the pattern actually run.
                </p>
                <p>
                    Built on Sail + MySQL + Pest, with Reverb wired up for real-time modules to build on.
                </p>
            </div>

            <a
                href="{{ route('concepts.dashboard') }}"
                class="mt-8 flex items-center justify-center gap-2 rounded-lg bg-accent px-5 py-2.5 text-sm font-medium text-accent-fg transition-colors hover:bg-accent-hover"
            >
                Browse concepts
                <svg width="10" height="11" viewBox="0 0 10 11" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-2.5 w-2.5">
                    <path d="M2.5 8L7.5 3.00001M7.70833 6.95834V2.79167H3.54167" stroke="currentColor" stroke-linecap="square" />
                </svg>
            </a>
        </div>
    </div>
</x-layout>
