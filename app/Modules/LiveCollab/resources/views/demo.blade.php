<x-layout title="Live Collab">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Live Collab</h1>
            <p class="mt-2 text-sm text-fg-subtle">Edit the shared document below — open this page in a second session to watch your edits appear there live, over a Reverb channel scoped to this document.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Live Collab" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    content: @js($document->content),
                    clientId: crypto.randomUUID(),
                    error: null,
                    debounceTimer: null,

                    init() {
                        window.Echo.channel('{{ $channel }}').listen('.document.edited', (event) => {
                            if (event.client_id === this.clientId) {
                                return;
                            }

                            this.content = event.content;
                        });
                    },

                    onInput() {
                        clearTimeout(this.debounceTimer);
                        this.debounceTimer = setTimeout(() => this.submit(), 300);
                    },

                    async submit() {
                        const response = await fetch('{{ route('live-collab.edit') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            },
                            body: JSON.stringify({ content: this.content, client_id: this.clientId }),
                        });

                        if (! response.ok) {
                            const body = await response.json();
                            this.error = body.message ?? 'unavailable';
                        } else {
                            this.error = null;
                        }
                    },
                }"
            >
                <textarea
                    x-model="content"
                    @input="onInput"
                    rows="12"
                    placeholder="Start typing…"
                    class="block w-full rounded-lg border border-border bg-surface px-3 py-2.5 text-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-accent"
                ></textarea>

                <p x-show="error" x-text="error" class="mt-2 text-sm text-red-500"></p>
            </div>
        @endif
    </div>
</x-layout>
