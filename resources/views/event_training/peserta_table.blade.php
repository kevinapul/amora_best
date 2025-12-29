<table class="w-full border-collapse mt-4 bg-white">
    <thead>
        <tr class="bg-white border-b">
            <th class="py-2 px-3 border text-center">No</th>
            <th class="py-2 px-3 border">Nama Training</th>
            <th class="py-2 px-3 border">Tanggal</th>
            <th class="py-2 px-3 border">Tempat</th>
            <th class="py-2 px-3 border text-center">Jumlah Peserta</th>
            <th class="py-2 px-3 border text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($events as $index => $event)

            {{-- HANDLE TANGGAL SECARA AMAN --}}
            @php
                try {
                    $start = $event->tanggal_start
                        ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
                        : null;

                    $end = $event->tanggal_end
                        ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y')
                        : null;
                } catch (\Exception $e) {
                    $start = $event->tanggal_start;
                    $end = $event->tanggal_end;
                }
            @endphp

            <tr class="border-b hover:bg-gray-50">
                <td class="py-2 px-3 text-center">
                    {{ ($events->currentPage() - 1) * $events->perPage() + $index + 1 }}
                </td>

                <td class="py-2 px-3 font-semibold">
                    {{ $event->training->name ?? '-' }}
                </td>

                <td class="py-2 px-3">
                    @if($start && $end)
                        {{ $start === $end ? $start : "$start - $end" }}
                    @else
                        -
                    @endif
                </td>

                <td class="py-2 px-3">
                    {{ $event->tempat }}
                </td>

                <td class="py-2 px-3 text-center">
                    {{ $event->participants_count }}
                </td>

                <td class="py-2 px-3 text-center">
                    <div class="flex justify-center gap-2">
                        <a href="{{ route('event-training.show', $event->id) }}"
                           class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Detail
                        </a>

                        @can('addParticipant', $event)
                            <a href="{{ route('event-participant.create', $event->id) }}"
                               class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                Tambah Peserta
                            </a>
                        @else
                            <button type="button"
                                    onclick="showAddParticipantWarning('{{ $event->status }}')"
                                    class="px-3 py-1 bg-gray-400 text-white rounded cursor-not-allowed">
                                Tambah Peserta
                            </button>
                        @endcan
                    </div>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="6" class="py-3 text-center text-gray-500">
                    Tidak ada data
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="mt-4">
    {{ $events->links() }}
</div>

{{-- ================= MODAL WARNING ================= --}}
<div id="addParticipantModal"
     class="fixed inset-0 hidden bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-96 text-center">
        <h2 class="text-lg font-bold text-red-600 mb-2">
            Tidak Bisa Menambah Peserta
        </h2>

        <p id="addParticipantText" class="text-gray-700 mb-4"></p>

        <button onclick="closeAddParticipantModal()"
                class="px-4 py-2 bg-indigo-600 text-white rounded">
            Mengerti
        </button>
    </div>
</div>

<script>
function showAddParticipantWarning(status) {
    let text = 'Anda tidak memiliki izin untuk menambahkan peserta.';

    if (status === 'pending') {
        text = 'Event masih PENDING dan belum di-ACC.';
    } else if (status === 'done') {
        text = 'Event sudah SELESAI. Peserta tidak dapat ditambahkan.';
    }

    document.getElementById('addParticipantText').innerText = text;
    document.getElementById('addParticipantModal').classList.remove('hidden');
}

function closeAddParticipantModal() {
    document.getElementById('addParticipantModal').classList.add('hidden');
}
</script>
