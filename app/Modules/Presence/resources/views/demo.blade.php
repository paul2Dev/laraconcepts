<x-layout title="Presence">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Presence</h1>
            <p class="mt-2 text-sm text-fg-subtle">Join a Reverb presence channel and see who else is here, live — plus a typing indicator whispered directly between browsers, no server round trip.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Presence" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    guestName: @js($guestName),
                    members: [],
                    typingFrom: null,
                    typingTimeout: null,
                    channel: null,
                    error: false,

                    init() {
                        this.channel = window.Echo.join('{{ $channel }}')
                            .here((users) => { this.members = users; })
                            .joining((user) => { this.members.push(user); })
                            .leaving((user) => { this.members = this.members.filter((member) => member.id !== user.id); })
                            .listenForWhisper('typing', (event) => {
                                if (event.name === this.guestName) return;

                                this.typingFrom = event.name;
                                clearTimeout(this.typingTimeout);
                                this.typingTimeout = setTimeout(() => { this.typingFrom = null; }, 2000);
                            })
                            .error(() => { this.error = true; });
                    },

                    notifyTyping() {
                        this.channel.whisper('typing', { name: this.guestName });
                    },
                }"
            >
                <p class="text-sm text-fg-subtle">You are <span class="font-medium text-fg" x-text="guestName"></span>.</p>
                <p class="mt-1 text-xs text-fg-subtle">To test this: open this page in a <strong>different browser</strong> (or a private/incognito window) — two tabs in the same browser share one session, so they show up as the same person here.</p>

                <p x-show="error" class="mt-4 text-sm text-red-500">Connection unavailable.</p>

                <ul class="mt-4 divide-y divide-border rounded-xl border border-border bg-surface">
                    <template x-if="members.length === 0">
                        <li class="px-4 py-3 text-sm text-fg-subtle">No one else is here yet.</li>
                    </template>
                    <template x-for="member in members" :key="member.id">
                        <li class="flex items-center gap-2 px-4 py-3">
                            <span class="h-2 w-2 rounded-full bg-green-500"></span>
                            <span x-text="member.name"></span>
                        </li>
                    </template>
                </ul>

                <div class="mt-6">
                    <textarea
                        @input="notifyTyping"
                        rows="3"
                        placeholder="Type something — the other session sees a typing indicator instantly."
                        class="w-full rounded-lg border border-border bg-surface p-3 text-sm"
                    ></textarea>
                    <p class="mt-2 h-5 text-sm text-fg-subtle">
                        <span x-show="typingFrom" x-text="`${typingFrom} is typing…`"></span>
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
