@php
    // ================= TOTAL LUNAS =================
    $totalLunas = $group->events
        ->flatMap(fn($e) => $e->participants)
        ->where('pivot.is_paid', true)
        ->sum(fn($p) => $p->pivot->harga_peserta);

    // ================= TOTAL TAGIHAN =================
    if ($group->training_type === 'inhouse') {
        // INHOUSE → pakai harga paket (1x)
        $totalTagihan = $group->harga_paket ?? 0;
    } else {
        // REGULER → akumulasi semua peserta semua event
        $totalTagihan = $group->events->flatMap(fn($e) => $e->participants)->sum(fn($p) => $p->pivot->harga_peserta);
    }

@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800">
            Detail Event Group
        </h2>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8">

        {{-- ================= HEADER ================= --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">
                {{ $group->masterTraining->nama_training }}
            </h1>

            <div class="mt-2 text-gray-600 space-y-1">
                <p>Job Number: {{ $group->job_number ?? '-' }}</p>
                <p>Tempat: {{ $group->tempat ?? '-' }}</p>
                <p>
                    Tipe Training:
                    <span class="font-semibold uppercase">
                        {{ $group->training_type }}
                    </span>
                </p>
            </div>
        </div>

        {{-- ================= SUMMARY ================= --}}
        <div class="grid grid-cols-3 gap-6 mb-10">

            {{-- TOTAL EVENT --}}
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Total Event</p>
                <p class="text-3xl font-bold text-gray-800">
                    {{ $group->events->count() }}
                </p>
            </div>

            {{-- TOTAL TAGIHAN --}}
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Total Biaya</p>
                <p class="text-3xl font-bold text-indigo-600">
                    Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                </p>

                @if ($group->training_type === 'inhouse')
                    <p class="text-xs text-gray-500 mt-1">
                        Harga Paket Inhouse
                    </p>
                @else
                    <p class="text-xs text-gray-500 mt-1">
                        Akumulasi seluruh peserta
                    </p>
                @endif
            </div>

            {{-- TOTAL LUNAS --}}
            <div class="bg-white border rounded-lg p-6 shadow-sm">
                <p class="text-sm text-gray-500 mb-1">Total Lunas</p>
                <p class="text-3xl font-bold text-green-600">
                    Rp {{ number_format($totalLunas, 0, ',', '.') }}
                </p>
            </div>

        </div>

        {{-- ================= TABLE EVENT ================= --}}
        <div class="bg-white border rounded-lg overflow-hidden shadow-sm">
            <table class="w-full border-collapse">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-3 text-left">#</th>
                        <th class="px-4 py-3 text-left">Training</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-center">Status Training</th>
                        <th class="px-4 py-3 text-center">Status Pembayaran</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($group->events as $i => $event)
                        @php
                            $finance = $event->financeBadge();
                        @endphp

                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-3">
                                {{ $i + 1 }}
                            </td>

                            <td class="px-4 py-3 font-semibold">
                                {{ $event->training->name ?? strtoupper($event->non_training_type) }}
                            </td>

                            <td class="px-4 py-3">
                                {{ optional($event->tanggal_start)->format('d M Y') }}
                                @if ($event->tanggal_end)
                                    - {{ optional($event->tanggal_end)->format('d M Y') }}
                                @endif
                            </td>

                            {{-- STATUS TRAINING --}}
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-1 text-xs rounded font-semibold
                {{ $event->status === 'done'
                    ? 'bg-green-200 text-green-800'
                    : ($event->status === 'on_progress'
                        ? 'bg-yellow-200 text-yellow-800'
                        : ($event->status === 'active'
                            ? 'bg-blue-200 text-blue-800'
                            : 'bg-gray-200 text-gray-800')) }}">
                                    {{ strtoupper($event->status) }}
                                </span>
                            </td>

                            {{-- STATUS PEMBAYARAN --}}
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-1 text-xs rounded font-semibold
                {{ $finance['color'] === 'green'
                    ? 'bg-green-200 text-green-800'
                    : ($finance['color'] === 'yellow'
                        ? 'bg-yellow-200 text-yellow-800'
                        : 'bg-red-200 text-red-800') }}">
                                    {{ $finance['label'] }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('event-training.show', $event->id) }}"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-semibold
                      bg-blue-600 text-white rounded
                      hover:bg-blue-700 transition">
                                    Detail Event
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                                Belum ada event dalam group ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        {{-- ================= BACK ================= --}}
        <div class="mt-8">
            <a href="{{ route('event-training.index') }}"
                class="inline-block px-4 py-2 bg-gray-500 text-white rounded
                      hover:bg-gray-600 transition">
                ← Kembali ke daftar event
            </a>
        </div>

    </div>
</x-app-layout>
