<table class="w-full text-left text-sm">
    <thead>
        <tr class="border-b border-border text-xs uppercase tracking-wide text-fg-subtle">
            <th class="py-2 pr-4">Actor</th>
            <th class="py-2 pr-4">Action</th>
            <th class="py-2 pr-4">Subject</th>
            <th class="py-2">When</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-border">
        @forelse ($entries as $entry)
            <tr>
                <td class="py-2 pr-4 font-mono text-xs">{{ $entry->actor }}</td>
                <td class="py-2 pr-4">{{ $entry->action }}</td>
                <td class="py-2 pr-4">{{ $entry->subject }}</td>
                <td class="py-2 text-fg-subtle">{{ $entry->created_at->diffForHumans() }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="py-3 text-fg-subtle">No activity yet.</td>
            </tr>
        @endforelse
    </tbody>
</table>
