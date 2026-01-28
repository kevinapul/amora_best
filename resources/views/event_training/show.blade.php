<x-app-layout>

@php
    try {
        $start = $event->tanggal_start
            ? \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d F Y')
            : null;

        $end = $event->tanggal_end
            ? \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d F Y')
            : null;

        $tanggal = $start && $end
            ? ($start === $end ? $start : "$start – $end")
            : '-';
    } catch (\Exception $ex) {
        $tanggal = '-';
    }

    $group = $event->eventTrainingGroup;
@endphp

<div class="alkon-root">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- ================= HEADER ================= -->
        <div class="alkon-panel mb-8">
            <div class="alkon-panel-body flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4">

                <div>
                    <h1 class="text-2xl font-semibold text-[var(--alkon-text)]">
                        📘 {{ $event->training->name }}
                        <span class="text-sm text-[var(--alkon-muted)] font-normal">
                            ({{ $event->training->code }})
                        </span>
                    </h1>

                    <p class="mt-2 text-sm text-[var(--alkon-muted)]">
                        Job Number: <span class="font-medium">{{ $group->job_number ?? '-' }}</span>
                    </p>
                </div>

                <span
                    class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-semibold
                    {{ $event->status === 'done'
                        ? 'bg-green-100 text-green-700'
                        : ($event->status === 'active'
                            ? 'bg-blue-100 text-blue-700'
                            : 'bg-gray-200 text-gray-700') }}">
                    {{ strtoupper($event->status) }}
                </span>

            </div>
        </div>

        <!-- ================= INFO GRID ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Tanggal</p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $tanggal }}</p>
                </div>
            </div>

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Tempat</p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $group->tempat ?? '-' }}</p>
                </div>
            </div>

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Sertifikasi</p>
                    <p class="font-semibold text-gray-800 mt-1">{{ $group->sertifikasi ?? '-' }}</p>
                </div>
            </div>

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Instruktur</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $staffs['Instruktur'] ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Training Officer</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ $staffs['Training Officer'] ?? '-' }}
                    </p>
                </div>
            </div>

            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-xs text-[var(--alkon-muted)]">Jenis Event</p>
                    <p class="font-semibold text-gray-800 mt-1">
                        {{ ucfirst($event->jenis_event) }}
                        @if ($event->jenis_event === 'training')
                            ({{ ucfirst($group->training_type) }})
                        @elseif ($event->jenis_event === 'non_training')
                            ({{ ucfirst($event->non_training_type) }})
                        @endif
                    </p>
                </div>
            </div>

        </div>

        <!-- ================= ACTION ================= -->
        <div class="flex flex-wrap gap-3 mb-8">

            @can('addParticipant', $event)
                <a href="{{ route('event-participant.create', $event->id) }}"
                   class="alkon-btn-primary">
                    + Tambah Peserta
                </a>
            @endcan

            @can('addInstructor', $event)
                <a href="{{ route('event-staff.create', $event->id) }}"
                   class="alkon-btn-primary bg-green-600 hover:bg-green-700">
                    + Tambah Instruktur
                </a>
            @endcan

            @can('approveFinance', $event)
                <a href="{{ route('event-training.finance', $event->id) }}"
                   class="alkon-btn-primary bg-yellow-600 hover:bg-yellow-700">
                    Cek & Approve Pembayaran
                </a>
            @endcan

        </div>

        <!-- ================= PESERTA ================= -->
        <div class="alkon-panel">
            <div class="alkon-panel-body p-0">

                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">
                        👥 Daftar Peserta
                    </h3>
                </div>

                @if ($event->participants->count())
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-center w-12">No</th>
                                    <th class="px-4 py-3 text-left">Nama</th>
                                    <th class="px-4 py-3 text-left">Perusahaan</th>
                                    <th class="px-4 py-3 text-center">No HP</th>
                                    <th class="px-4 py-3 text-center">Biaya</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($event->participants as $i => $participant)
                                    <tr class="border-b hover:bg-gray-50">
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ $i + 1 }}
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            {{ $participant->nama }}
                                        </td>
                                        <td class="px-4 py-3 text-gray-700">
                                            {{ $participant->perusahaan ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            {{ $participant->no_hp ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-center font-semibold">
                                            Rp {{ number_format($participant->pivot->harga_peserta ?? 0, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="px-6 py-6 text-gray-500">
                        Belum ada peserta.
                    </p>
                @endif

            </div>
        </div>

        <!-- ================= BACK ================= -->
        <div class="mt-8">
            <a href="{{ route('event-training.group.show', $group->id) }}"
               class="alkon-btn-secondary">
                ← Kembali ke Group
            </a>
        </div>

    </div>
</div>

</x-app-layout>
