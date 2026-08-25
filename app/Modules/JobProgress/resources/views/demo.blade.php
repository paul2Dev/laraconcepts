<x-layout title="Job Progress">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Job Progress</h1>
            <p class="mt-2 text-sm text-fg-subtle">Upload a file to watch a queued job count it in chunks, live, over a Reverb channel scoped to this upload.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Job Progress" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    status: 'idle',
                    percentage: 0,
                    linesProcessed: 0,
                    error: null,
                    channel: null,

                    async submit(event) {
                        if (this.channel) {
                            window.Echo.leave(this.channel);
                        }

                        this.error = null;
                        this.status = 'uploading';
                        this.percentage = 0;
                        this.linesProcessed = 0;

                        const response = await fetch('{{ route('job-progress.upload') }}', {
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

                        this.status = 'processing';
                        this.listen(body.channel);
                    },

                    listen(channel) {
                        this.channel = channel;

                        window.Echo.channel(channel).listen('.progress.updated', (event) => {
                            this.percentage = event.percentage;
                            this.linesProcessed = event.lines_processed;

                            if (event.percentage >= 100) {
                                this.status = 'done';
                                window.Echo.leave(channel);
                            }
                        });
                    },
                }"
            >
                <form @submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">
                    <input
                        type="file"
                        name="file"
                        required
                        class="block w-full text-sm text-fg-muted file:mr-4 file:rounded-lg file:border-0 file:bg-surface-hover file:px-4 file:py-2 file:text-sm file:font-medium"
                    >

                    <button
                        type="submit"
                        :disabled="status === 'uploading' || status === 'processing'"
                        class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
                    >
                        Upload
                    </button>
                </form>

                <p x-show="error" x-text="error" class="mt-4 text-sm text-red-500"></p>

                <div x-show="status === 'processing' || status === 'done'" class="mt-8">
                    <div class="h-3 w-full overflow-hidden rounded-full bg-surface-hover">
                        <div class="h-full bg-accent transition-all" :style="`width: ${percentage}%`"></div>
                    </div>
                    <p class="mt-2 text-sm text-fg-subtle">
                        <span x-text="percentage"></span>% — <span x-text="linesProcessed"></span> lines processed
                    </p>
                </div>
            </div>
        @endif
    </div>
</x-layout>
