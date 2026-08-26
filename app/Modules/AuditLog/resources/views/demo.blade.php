<x-layout title="Audit Log">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Audit Log</h1>
            <p class="mt-2 text-sm text-fg-subtle">Create, rename, and delete demo notes — each action lands in the activity feed below.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Audit Log" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    title: '',
                    notes: @js($notes),
                    entries: @js($entries),
                    busy: false,

                    async create() {
                        if (! this.title) return;
                        await this.send('{{ route('audit-log.notes.store') }}', 'POST', { title: this.title });
                        this.title = '';
                    },

                    async rename(note) {
                        const title = prompt('New title', note.title);
                        if (! title || title === note.title) return;
                        await this.send(`/concepts/audit-log/notes/${note.id}`, 'PUT', { title });
                    },

                    async remove(note) {
                        await this.send(`/concepts/audit-log/notes/${note.id}`, 'DELETE');
                    },

                    async send(url, method, body = null) {
                        this.busy = true;

                        const response = await fetch(url, {
                            method,
                            headers: {
                                Accept: 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            },
                            body: body ? JSON.stringify(body) : null,
                        });

                        const data = await response.json();
                        this.notes = data.notes;
                        this.entries = data.entries;
                        this.busy = false;
                    },
                }"
            >
                <form @submit.prevent="create" class="flex gap-3">
                    <input
                        type="text"
                        x-model="title"
                        placeholder="New note title"
                        class="block w-full rounded-lg border border-border bg-surface px-3 py-2.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                    >
                    <button
                        type="submit"
                        :disabled="busy"
                        class="rounded-lg bg-accent px-5 py-2.5 text-sm font-medium text-white disabled:opacity-50"
                    >
                        Add
                    </button>
                </form>

                <ul class="mt-6 divide-y divide-border rounded-xl border border-border bg-surface">
                    <template x-for="note in notes" :key="note.id">
                        <li class="flex items-center justify-between gap-4 px-4 py-3.5 text-sm">
                            <span x-text="note.title"></span>
                            <span class="flex gap-3 text-xs">
                                <button @click="rename(note)" class="underline hover:text-accent">Rename</button>
                                <button @click="remove(note)" class="underline hover:text-accent">Delete</button>
                            </span>
                        </li>
                    </template>

                    <li x-show="notes.length === 0" class="px-4 py-3.5 text-sm text-fg-subtle">No notes yet.</li>
                </ul>

                <h2 class="mt-12 text-lg font-semibold tracking-tight">Activity feed</h2>

                <table class="mt-4 w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-border text-xs uppercase tracking-wide text-fg-subtle">
                            <th class="py-2 pr-4">Actor</th>
                            <th class="py-2 pr-4">Action</th>
                            <th class="py-2 pr-4">Subject</th>
                            <th class="py-2">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <template x-for="entry in entries" :key="entry.id">
                            <tr>
                                <td class="py-2 pr-4 font-mono text-xs" x-text="entry.actor"></td>
                                <td class="py-2 pr-4" x-text="entry.action"></td>
                                <td class="py-2 pr-4" x-text="entry.subject"></td>
                                <td class="py-2 text-fg-subtle" x-text="entry.created_at"></td>
                            </tr>
                        </template>

                        <tr x-show="entries.length === 0">
                            <td colspan="4" class="py-3 text-fg-subtle">No activity yet.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-layout>
