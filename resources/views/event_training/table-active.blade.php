<table class="w-full border-collapse bg-white">
    <thead>
        <tr class="border-b">
            <th class="p-2">No</th>
            <th class="p-2">Training</th>
            <th class="p-2">Tanggal</th>
            <th class="p-2">Tempat</th>
            <th class="p-2 text-center">Peserta</th>
            <th class="p-2 text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($events as $i => $event)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 text-center">
                    {{ ($events->currentPage() - 1) * $events->perPage() + $i + 1 }}
                </td>

                <td class="p-2 font-semibold">
                    {{ $event->training->name ?? '-' }}
                </td>

                <td class="py-2 px-3">
                    {{ $event->tanggal_start->format('d M Y') }}
                    –
                    {{ $event->tanggal_end->format('d M Y') }}
                </td>

                <td class="p-2">{{ $event->tempat }}</td>

                <td class="p-2 text-center">
                    {{ $event->participants_count }}
                </td>

                {{-- STATUS --}}
                <td class="py-2 px-3 text-center">
                    @php
                        $statusClass = match($event->status) {
                            'pending' => 'bg-gray-200 text-gray-800',
                            'active' => 'bg-blue-200 text-blue-800',
                            'on_progress' => 'bg-yellow-200 text-yellow-800',
                            'done' => 'bg-green-200 text-green-800',
                        };
                    @endphp

                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">
                        {{ strtoupper(str_replace('_', ' ', $event->status)) }}
                    </span>
                </td>

                <td class="p-2 text-center">
                    <a href="{{ route('event-training.show', $event->id) }}"
                       class="px-3 py-1 bg-blue-500 text-white rounded">
                        Detail
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="p-4 text-center text-gray-500">
                    Tidak ada event aktif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $events->links() }}
</div>
