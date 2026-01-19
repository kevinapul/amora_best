
@php
    try {
        $start = $event->tanggal_start
            ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
            : null;

        $end = $event->tanggal_end
            ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y')
            : null;

        $tanggal = $start && $end ? ($start === $end ? $start : "$start - $end") : '-';
    } catch (\Exception $ex) {
        $tanggal = '-';
    }

    $group = $event->eventTrainingGroup;
@endphp

{{-- ================= INFO EVENT ================= --}}
<div class="bg-white shadow-md rounded-lg p-6 mb-6">
    <h3 class="text-lg font-semibold mb-2">
        {{ $event->training->name }} ({{ $event->training->code }})
    </h3>

    <p><strong>Job Number:</strong> {{ $group->job_number ?? '-' }}</p>
    <p><strong>Tanggal:</strong> {{ $tanggal }}</p>
    <p><strong>Tempat:</strong> {{ $group->tempat ?? '-' }}</p>

    <p>
        <strong>Jenis Event:</strong>
        {{ ucfirst($event->jenis_event) }}
        @if ($event->jenis_event === 'training')
            {{ ucfirst($group->training_type) }}
        @elseif ($event->jenis_event === 'non_training')
            {{ ucfirst($event->non_training_type) }}
        @endif
    </p>

    <p><strong>Sertifikasi:</strong> {{ $group->sertifikasi ?? '-' }}</p>

    <p class="mt-2">
        <span class="px-2 py-1 rounded text-white font-semibold
            {{ $event->status === 'done'
                ? 'bg-green-600'
                : ($event->status === 'active'
                    ? 'bg-blue-600'
                    : 'bg-gray-500') }}">
            {{ strtoupper($event->status) }}
        </span>
    </p>
</div>

{{-- ================= BUTTON TAMBAH PESERTA ================= --}}
@can('addParticipant', $event)
    <div class="mb-4">
        <a href="{{ route('event-participant.create', $event->id) }}"
           class="px-4 py-2 bg-indigo-600 text-white rounded">
            Tambah Peserta
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
                    <th class="border px-3 py-2">No</th>
                    <th class="border px-3 py-2">Nama</th>
                    <th class="border px-3 py-2">Perusahaan</th>
                    <th class="border px-3 py-2">No HP</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($event->participants as $i => $participant)
                    <tr>
                        <td class="border px-3 py-2 text-center">{{ $i + 1 }}</td>
                        <td class="border px-3 py-2">{{ $participant->nama }}</td>
                        <td class="border px-3 py-2">{{ $participant->perusahaan ?? '-' }}</td>
                        <td class="border px-3 py-2">{{ $participant->no_hp ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-gray-600">Belum ada peserta.</p>
    @endif
</div>
