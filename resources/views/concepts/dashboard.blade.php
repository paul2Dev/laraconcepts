<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Concepts — {{ config('app.name', 'Laravel') }}</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css'])
        @endif
    </head>
    <body class="bg-neutral-50 text-neutral-900 antialiased">
        <div class="mx-auto max-w-3xl px-6 py-12">
            <h1 class="text-2xl font-semibold">Concept Registry</h1>

            @forelse ($groups as $category => $entries)
                <section class="mt-10">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-neutral-500">{{ $category }}</h2>

                    <ul class="mt-4 divide-y divide-neutral-200 rounded-lg border border-neutral-200 bg-white">
                        @foreach ($entries as $entry)
                            @php $concept = $entry['concept']; @endphp
                            <li class="flex items-center justify-between gap-4 px-4 py-3">
                                <div>
                                    <p class="font-medium">
                                        @if (Route::has($concept->demoRoute))
                                            <a href="{{ route($concept->demoRoute) }}" class="hover:underline">{{ $concept->name }}</a>
                                        @else
                                            {{ $concept->name }}
                                        @endif
                                    </p>
                                    <p class="text-sm text-neutral-500">{{ $concept->description }}</p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <span class="text-sm font-medium {{ $entry['active'] ? 'text-green-600' : 'text-neutral-400' }}">
                                        {{ $entry['active'] ? 'On' : 'Off' }}
                                    </span>

                                    <form method="POST" action="{{ route('concepts.toggle', $concept->slug) }}">
                                        @csrf
                                        <button type="submit" class="rounded border border-neutral-300 px-3 py-1 text-sm hover:bg-neutral-100">
                                            {{ $entry['active'] ? 'Deactivate' : 'Activate' }}
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @empty
                <p class="mt-10 text-neutral-500">No concepts registered yet.</p>
            @endforelse
        </div>
    </body>
</html>
