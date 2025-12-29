<table class="w-full border-collapse mt-4 bg-white">
    <thead>
        <tr class="bg-white border-b">
            <th class="py-2 px-3 border">Training</th>
            <th class="py-2 px-3 border">Job Number</th>
            <th class="py-2 px-3 border">Tanggal</th>
            <th class="py-2 px-3 border">Tempat</th>
            <th class="py-2 px-3 border">Sertifikasi</th>
            <th class="py-2 px-3 border text-center">Status</th>
            <th class="py-2 px-3 border text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($events as $e)
            <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3">{{ $e->training->name ?? '-' }}</td>
                <td class="py-2 px-3">{{ $e->job_number }}</td>

                <td class="py-2 px-3">
                    {{ $e->tanggal_start->format('d M Y') }}
                    –
                    {{ $e->tanggal_end->format('d M Y') }}
                </td>

                <td class="py-2 px-3">{{ $e->tempat }}</td>
                <td class="py-2 px-3">{{ $e->sertifikasi ?? '-' }}</td>

                {{-- STATUS --}}
                <td class="py-2 px-3 text-center">
                    @php
                        $statusClass = match($e->status) {
                            'pending' => 'bg-gray-200 text-gray-800',
                            'active' => 'bg-blue-200 text-blue-800',
                            'on_progress' => 'bg-yellow-200 text-yellow-800',
                            'done' => 'bg-green-200 text-green-800',
                        };
                    @endphp

                    <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">
                        {{ strtoupper(str_replace('_', ' ', $e->status)) }}
                    </span>
                </td>

                {{-- AKSI --}}
                <td class="py-2 px-3 text-center">
                    <div class="flex justify-center gap-2">

                        {{-- EDIT --}}
                        @if($e->canEdit())
                            <a href="{{ route('event-training.edit', $e->id) }}"
                               class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                Edit
                            </a>
                        @else
                            <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded cursor-not-allowed"
                                  title="Event tidak bisa diedit">
                                Edit
                            </span>
                        @endif

                        {{-- DELETE --}}
                        @if($e->canDelete())
                            <form action="{{ route('event-training.destroy', $e->id) }}" method="POST"
                                  onsubmit="return confirm('Yakin ingin hapus event ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                                    Hapus
                                </button>
                            </form>
                        @else
                            <span class="px-3 py-1 bg-gray-300 text-gray-600 rounded cursor-not-allowed"
                                  title="Event tidak bisa dihapus">
                                Hapus
                            </span>
                        @endif

                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="py-3 text-center text-gray-500">
                    Tidak ada data
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- Pagination --}}
<div class="mt-4">
    {{ $events->links() }}
</div>
