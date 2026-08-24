<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Concepts — {{ config('app.name', 'Laravel') }}</title>

        @fonts

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="min-h-screen bg-bg font-sans text-fg antialiased">
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
                            <li class="flex items-center justify-between gap-4 px-5 py-4 transition-colors hover:bg-surface-hover">
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

                                <div class="flex shrink-0 items-center gap-4">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-medium {{ $entry['active'] ? 'text-accent' : 'text-fg-subtle' }}">
                                        <span class="h-1.5 w-1.5 rounded-full {{ $entry['active'] ? 'bg-accent' : 'bg-fg-subtle' }}"></span>
                                        {{ $entry['active'] ? 'On' : 'Off' }}
                                    </span>

                                    <form method="POST" action="{{ route('concepts.toggle', $concept->slug) }}">
                                        @csrf
                                        @if ($entry['active'])
                                            <button type="submit" class="rounded-md border border-border px-3 py-1.5 text-xs font-medium text-fg-muted transition-colors hover:bg-surface-hover hover:text-fg">
                                                Deactivate
                                            </button>
                                        @else
                                            <button type="submit" class="rounded-md bg-accent px-3 py-1.5 text-xs font-medium text-accent-fg transition-colors hover:bg-accent-hover">
                                                Activate
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <p class="mt-12 text-sm text-fg-subtle">No concepts registered yet.</p>
            @endforelse
        </div>
    </body>
</html>
