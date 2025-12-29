<table class="w-full border-collapse mt-4 bg-white">
    <thead>
        <tr class="bg-white border-b">
            <th class="py-2 px-3 border">Training</th>
            <th class="py-2 px-3 border">Tanggal</th>
            <th class="py-2 px-3 border">Tempat</th>
            <th class="py-2 px-3 border text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($events as $event)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3">
                    {{ $event->training->name ?? '-' }}
                </td>

                <td class="py-2 px-3">
                    {{ $event->tanggal }}
                </td>

                <td class="py-2 px-3">
                    {{ $event->tempat }}
                </td>

                <td class="py-2 px-3 text-center">
                    <div class="flex justify-center gap-2">

                        <a href="{{ route('event-staff.show', $event->id) }}"
                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Detail
                        </a> 
                        <a href="{{ route('event-staff.create', $event->id) }}"
                            class="px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                            Tambah Staf
                        </a>

                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="py-3 text-center text-gray-500">
                    Tidak ada event ditemukan
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination --}}
<div class="mt-4">
    {{ $events->appends(['search' => $search])->links() }}
</div>
