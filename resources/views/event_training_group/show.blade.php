@php
    $totalLunas = $group->events
        ->flatMap(fn($e) => $e->participants)
        ->where('pivot.is_paid', true)
        ->sum(fn($p) => $p->pivot->harga_peserta);

    if ($group->training_type === 'inhouse') {
        $totalTagihan = $group->harga_paket ?? 0;
    } else {
        $totalTagihan = $group->events->flatMap(fn($e) => $e->participants)->sum(fn($p) => $p->pivot->harga_peserta);
    }
@endphp

<x-app-layout>
    <div class="max-w-7xl mx-auto px-6 py-8">

        <!-- ================= HEADER ================= -->
        <div class="bg-white rounded-2xl shadow-sm p-8 mb-8 border border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-6">

                <div>
                    <h1 class="text-2xl font-bold text-gray-800 tracking-wide">
                        {{ $group->masterTraining->nama_training }}
                    </h1>

                    <div class="mt-3 text-sm text-gray-500 space-y-1">
                        <p>Job Number: <span class="font-semibold text-gray-700">{{ $group->job_number ?? '-' }}</span>
                        </p>
                        <p>Tempat: <span class="font-semibold text-gray-700">{{ $group->tempat ?? '-' }}</span></p>
                        <p>
                            Tipe Training:
                            <span class="uppercase font-semibold text-green-600">
                                {{ $group->training_type }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">

                    @if (auth()->user()->hasRole(['finance', 'it']))
                        <a href="{{ route('finance.group.show', $group->id) }}"
                            class="px-5 py-2 bg-green-600 text-white rounded-lg text-sm font-semibold hover:bg-green-700 transition">
                            💰 Kelola Invoice
                        </a>
                    @endif

                    <a href="{{ route('event-training.index') }}"
                        class="px-5 py-2 bg-gray-200 text-gray-700 rounded-lg text-sm font-semibold hover:bg-gray-300 transition">
                        ← Kembali
                    </a>

                </div>
            </div>
        </div>

        <!-- ================= SUMMARY ================= -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <p class="text-sm text-gray-500">Total Event</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">
                    {{ $group->events->count() }}
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <p class="text-sm text-gray-500">Total Tagihan</p>
                <p class="text-3xl font-bold text-indigo-600 mt-1">
                    Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    {{ $group->training_type === 'inhouse' ? 'Harga Paket Inhouse' : 'Akumulasi seluruh peserta' }}
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                <p class="text-sm text-gray-500">Total Lunas</p>
                <p class="text-3xl font-bold text-green-600 mt-1">
                    Rp {{ number_format($totalLunas, 0, ',', '.') }}
                </p>
            </div>

        </div>

        <!-- ================= TABLE EVENT ================= -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">

            <div class="px-6 py-4 border-b bg-gray-50">
                <h2 class="font-semibold text-gray-700">Daftar Event</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 text-left">#</th>
                            <th class="px-6 py-3 text-left">Training</th>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-center">Status</th>
                            <th class="px-6 py-3 text-center">Pembayaran</th>
                            <th class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y">
                        @forelse($group->events as $i => $event)
                            @php $finance = $event->financeBadge(); @endphp
                            <tr class="hover:bg-gray-50 transition">

                                <td class="px-6 py-4 text-gray-500">
                                    {{ $i + 1 }}
                                </td>

                                <td class="px-6 py-4 font-medium text-gray-800">
                                    {{ $event->training->name ?? strtoupper($event->non_training_type) }}
                                </td>

                                <td class="px-6 py-4 text-gray-600">
                                    {{ optional($event->tanggal_start)->format('d M Y') }}
                                    @if ($event->tanggal_end)
                                        – {{ optional($event->tanggal_end)->format('d M Y') }}
                                    @endif
                                </td>

                                <!-- STATUS TRAINING -->
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold
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
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold
                                    {{ $finance['color'] === 'green'
                                        ? 'bg-green-100 text-green-700'
                                        : ($finance['color'] === 'yellow'
                                            ? 'bg-yellow-100 text-yellow-700'
                                            : 'bg-red-100 text-red-700') }}">
                                        {{ $finance['label'] }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('event-training.show', $event->id) }}"
                                        class="px-4 py-1.5 bg-gray-800 text-white rounded-md text-xs font-semibold hover:bg-black transition">
                                        Detail
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    Belum ada event dalam group ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>
</x-app-layout>
