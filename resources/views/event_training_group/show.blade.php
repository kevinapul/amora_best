@php
    // ================= TOTAL LUNAS =================
    $totalLunas = $group->events
        ->flatMap(fn($e) => $e->participants)
        ->where('pivot.is_paid', true)
        ->sum(fn($p) => $p->pivot->harga_peserta);

    // ================= TOTAL TAGIHAN =================
    if ($group->training_type === 'inhouse') {
        $totalTagihan = $group->harga_paket ?? 0;
    } else {
        $totalTagihan = $group->events
            ->flatMap(fn($e) => $e->participants)
            ->sum(fn($p) => $p->pivot->harga_peserta);
    }
@endphp

<x-app-layout>

<div class="alkon-root">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

        <!-- ================= HEADER ================= -->
        <div class="alkon-panel mb-8">
            <div class="alkon-panel-body flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

                <div>
                    <h1 class="text-2xl font-semibold text-[var(--alkon-text)]">
                        📋 {{ $group->masterTraining->nama_training }}
                    </h1>

                    <div class="mt-2 text-sm text-[var(--alkon-muted)] space-y-1">
                        <p>Job Number: <span class="font-medium">{{ $group->job_number ?? '-' }}</span></p>
                        <p>Tempat: <span class="font-medium">{{ $group->tempat ?? '-' }}</span></p>
                        <p>
                            Tipe Training:
                            <span class="uppercase font-semibold text-[var(--alkon-green)]">
                                {{ $group->training_type }}
                            </span>
                        </p>
                    </div>
                </div>

                <a href="{{ route('event-training.index') }}"
                   class="alkon-btn-secondary">
                    ← Kembali
                </a>

            </div>
        </div>

        <!-- ================= SUMMARY ================= -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10">

            <!-- TOTAL EVENT -->
            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-sm text-[var(--alkon-muted)]">Total Event</p>
                    <p class="text-3xl font-bold text-[var(--alkon-text)] mt-1">
                        {{ $group->events->count() }}
                    </p>
                </div>
            </div>

            <!-- TOTAL TAGIHAN -->
            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-sm text-[var(--alkon-muted)]">Total Biaya</p>
                    <p class="text-3xl font-bold text-indigo-600 mt-1">
                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-[var(--alkon-muted)] mt-1">
                        {{ $group->training_type === 'inhouse'
                            ? 'Harga Paket Inhouse'
                            : 'Akumulasi seluruh peserta' }}
                    </p>
                </div>
            </div>

            <!-- TOTAL LUNAS -->
            <div class="alkon-panel">
                <div class="alkon-panel-body">
                    <p class="text-sm text-[var(--alkon-muted)]">Total Lunas</p>
                    <p class="text-3xl font-bold text-green-600 mt-1">
                        Rp {{ number_format($totalLunas, 0, ',', '.') }}
                    </p>
                </div>
            </div>

        </div>

        <!-- ================= TABLE EVENT ================= -->
        <div class="alkon-panel">
            <div class="alkon-panel-body p-0 overflow-x-auto">

                <table class="w-full border-collapse text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr class="text-left text-gray-700">
                            <th class="px-5 py-3 w-12">#</th>
                            <th class="px-5 py-3">Training</th>
                            <th class="px-5 py-3">Tanggal</th>
                            <th class="px-5 py-3 text-center">Status Training</th>
                            <th class="px-5 py-3 text-center">Pembayaran</th>
                            <th class="px-5 py-3 text-center w-40">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($group->events as $i => $event)
                            @php $finance = $event->financeBadge(); @endphp

                            <tr class="border-b hover:bg-gray-50 transition">

                                <td class="px-5 py-3 text-gray-600">
                                    {{ $i + 1 }}
                                </td>

                                <td class="px-5 py-3 font-semibold text-gray-800">
                                    {{ $event->training->name ?? strtoupper($event->non_training_type) }}
                                </td>

                                <td class="px-5 py-3 text-gray-600">
                                    {{ optional($event->tanggal_start)->format('d M Y') }}
                                    @if ($event->tanggal_end)
                                        – {{ optional($event->tanggal_end)->format('d M Y') }}
                                    @endif
                                </td>

                                <!-- STATUS TRAINING -->
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $event->status === 'done'
                                            ? 'bg-green-100 text-green-700'
                                            : ($event->status === 'on_progress'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : ($event->status === 'active'
                                                    ? 'bg-blue-100 text-blue-700'
                                                    : 'bg-gray-100 text-gray-700')) }}">
                                        {{ strtoupper($event->status) }}
                                    </span>
                                </td>

                                <!-- STATUS PEMBAYARAN -->
                                <td class="px-5 py-3 text-center">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold
                                        {{ $finance['color'] === 'green'
                                            ? 'bg-green-100 text-green-700'
                                            : ($finance['color'] === 'yellow'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-red-100 text-red-700') }}">
                                        {{ $finance['label'] }}
                                    </span>
                                </td>

                                <td class="px-5 py-3 text-center">
                                    <a href="{{ route('event-training.show', $event->id) }}"
                                       class="alkon-btn-primary text-sm">
                                        Detail
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada event dalam group ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>

    </div>
</div>

</x-app-layout>
