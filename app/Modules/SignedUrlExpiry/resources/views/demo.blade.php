<x-layout title="Signed URL Expiry">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Signed URL Expiry</h1>
            <p class="mt-2 text-sm text-fg-subtle">A signed, time-limited download link. Watch the countdown — once it hits zero the link is rejected instead of silently serving the file.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Signed URL Expiry" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    expiresAtMs: {{ $expiresAt->timestamp }} * 1000,
                    remaining: 0,
                    status: 'idle',
                    result: null,

                    init() {
                        this.tick();
                        this.intervalId = setInterval(() => this.tick(), 1000);
                    },

                    tick() {
                        this.remaining = Math.max(0, Math.round((this.expiresAtMs - Date.now()) / 1000));

                        if (this.remaining === 0) {
                            clearInterval(this.intervalId);
                        }
                    },

                    async download() {
                        this.status = 'loading';

                        const response = await fetch('{{ $signedUrl }}');

                        if (response.ok) {
                            const text = await response.text();
                            this.status = 'success';
                            this.result = text;
                        } else {
                            const body = await response.json().catch(() => null);
                            this.status = 'rejected';
                            this.result = body?.message ?? `Rejected (${response.status})`;
                        }
                    },
                }"
            >
                <p class="text-sm text-fg-subtle">Signed link:</p>
                <code class="mt-1 block break-all rounded-lg bg-surface-hover px-3 py-2 text-xs">{{ $signedUrl }}</code>

                <p class="mt-4 text-sm">
                    <span x-show="remaining > 0">Expires in <span class="font-semibold" x-text="remaining"></span>s</span>
                    <span x-show="remaining === 0" class="font-semibold text-red-500">Expired</span>
                </p>

                <button
                    @click="download"
                    :disabled="status === 'loading'"
                    class="mt-4 rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                >
                    Download
                </button>

                <p x-show="status === 'success'" x-text="result" class="mt-4 text-sm text-green-600"></p>
                <p x-show="status === 'rejected'" x-text="result" class="mt-4 text-sm text-red-500"></p>
            </div>
        @endif
    </div>
</x-layout>
