<x-layout title="Live Notifications">
    <div class="mx-auto max-w-xl px-6 py-16">
        <header>
            <h1 class="text-3xl font-semibold tracking-tight">Live Notifications</h1>
            <p class="mt-2 text-sm text-fg-subtle">Fire a notification and watch it land instantly in the bell feed below, over its own Reverb channel — no refresh, no polling.</p>
        </header>

        @if (! $active)
            <p class="mt-8 text-sm text-fg-subtle">
                This concept is currently switched off. Toggle "Live Notifications" on from the
                <a href="{{ route('concepts.dashboard') }}" class="underline hover:text-accent">dashboard</a> to try it.
            </p>
        @else
            <div
                class="mt-8"
                x-data="{
                    open: false,
                    unread: 0,
                    notifications: [],
                    error: null,

                    init() {
                        window.Echo.channel('live-notifications').listen('.notification.posted', (event) => {
                            this.notifications = [event, ...this.notifications].slice(0, 50);
                            this.unread++;
                        });
                    },

                    toggle() {
                        this.open = !this.open;
                        if (this.open) {
                            this.unread = 0;
                        }
                    },

                    async send() {
                        this.error = null;

                        const response = await fetch('{{ route('live-notifications.notify') }}', {
                            method: 'POST',
                            headers: {
                                Accept: 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\'csrf-token\']').content,
                            },
                        });

                        if (! response.ok) {
                            const body = await response.json();
                            this.error = body.message ?? 'unavailable';
                        }
                    },
                }"
            >
                <div class="flex items-center gap-4">
                    <button
                        type="button"
                        @click="send"
                        class="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white"
                    >
                        Send test notification
                    </button>

                    <button
                        type="button"
                        @click="toggle"
                        class="relative rounded-full p-2 hover:bg-surface-hover"
                        aria-label="Notifications"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-6 w-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                        <span
                            x-show="unread > 0"
                            x-text="unread"
                            class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-medium text-white"
                        ></span>
                    </button>
                </div>

                <p x-show="error" x-text="error" class="mt-4 text-sm text-red-500"></p>

                <ul x-show="open" class="mt-4 divide-y divide-border rounded-xl border border-border bg-surface">
                    <template x-if="notifications.length === 0">
                        <li class="px-4 py-3 text-sm text-fg-subtle">No notifications yet.</li>
                    </template>
                    <template x-for="notification in notifications" :key="notification.id">
                        <li class="px-4 py-3">
                            <p class="font-medium" x-text="notification.title"></p>
                            <p class="text-sm text-fg-subtle" x-text="notification.body"></p>
                        </li>
                    </template>
                </ul>
            </div>
        @endif
    </div>
</x-layout>
