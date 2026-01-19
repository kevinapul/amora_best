<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Detail Event
        </h2>
    </x-slot>

    @php
        try {
            $start = $event->tanggal_start
                ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
                : null;

            $end = $event->tanggal_end
                ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y')
                : null;

            $tanggal = $start && $end
                ? ($start === $end ? $start : "$start - $end")
                : '-';
        } catch (\Exception $ex) {
            $tanggal = '-';
        }

        $group = $event->eventTrainingGroup;
    @endphp

    <div class="max-w-6xl mx-auto px-6 py-8">

        {{-- ================= INFO EVENT ================= --}}
        <div class="bg-white shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-xl font-bold mb-2">
                {{ $event->training->name }}
                <span class="text-sm text-gray-500">
                    ({{ $event->training->code }})
                </span>
            </h3>

            <div class="grid grid-cols-2 gap-4 text-sm mt-4">
                <p><strong>Job Number:</strong> {{ $group->job_number ?? '-' }}</p>
                <p><strong>Tanggal:</strong> {{ $tanggal }}</p>
                <p><strong>Tempat:</strong> {{ $group->tempat ?? '-' }}</p>

                <p>
                    <strong>Jenis Event:</strong>
                    {{ ucfirst($event->jenis_event) }}
                    @if ($event->jenis_event === 'training')
                        ({{ ucfirst($group->training_type) }})
                    @elseif ($event->jenis_event === 'non_training')
                        ({{ ucfirst($event->non_training_type) }})
                    @endif
                </p>

                <p><strong>Sertifikasi:</strong> {{ $group->sertifikasi ?? '-' }}</p>
            </div>

            <div class="mt-4">
                <span class="px-3 py-1 rounded text-white text-xs font-semibold
                    {{ $event->status === 'done'
                        ? 'bg-green-600'
                        : ($event->status === 'active'
                            ? 'bg-blue-600'
                            : 'bg-gray-500') }}">
                    {{ strtoupper($event->status) }}
                </span>
            </div>
        </div>

        {{-- ================= ACTION ================= --}}
        @can('addParticipant', $event)
            <div class="mb-4">
                <a href="{{ route('event-participant.create', $event->id) }}"
                   class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                    + Tambah Peserta
                </a>
            </div>
        @endcan

        {{-- ================= DAFTAR PESERTA ================= --}}
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Daftar Peserta</h3>

            @if ($event->participants->count())
                <table class="w-full border border-gray-300 text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border px-3 py-2 text-center">No</th>
                            <th class="border px-3 py-2">Nama</th>
                            <th class="border px-3 py-2">Perusahaan</th>
                            <th class="border px-3 py-2">No HP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($event->participants as $i => $participant)
                            <tr>
                                <td class="border px-3 py-2 text-center">
                                    {{ $i + 1 }}
                                </td>
                                <td class="border px-3 py-2">
                                    {{ $participant->nama }}
                                </td>
                                <td class="border px-3 py-2">
                                    {{ $participant->perusahaan ?? '-' }}
                                </td>
                                <td class="border px-3 py-2">
                                    {{ $participant->no_hp ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-gray-600">Belum ada peserta.</p>
            @endif
        </div>

        {{-- ================= BACK ================= --}}
        <div class="mt-6">
            <a href="{{ route('event-training.group.show', $group->id) }}"
               class="inline-block px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                ← Kembali ke Group
            </a>
        </div>

    </div>
</x-app-layout>
