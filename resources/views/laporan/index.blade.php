<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl">
            Laporan Event Training
        </h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto">

        <table class="w-full bg-white border rounded">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 border">Event</th>
                    <th class="p-3 border text-center">Peserta</th>
                    <th class="p-3 border text-center">Total Tagihan</th>
                    <th class="p-3 border text-center">Total Lunas</th>
                    <th class="p-3 border text-center">Finance</th>
                    <th class="p-3 border text-center">Sertifikat</th>
                    <th class="p-3 border text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($events as $row)
                    <tr class="border-t hover:bg-gray-50">

                        {{-- EVENT --}}
                        <td class="p-3">
                            <div class="font-semibold">
                                {{ $row->event->training->name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $row->event->job_number }}
                            </div>
                            <div class="text-xs text-gray-400">
                                {{ strtoupper($row->jenis_event) }}
                            </div>
                        </td>

                        {{-- PESERTA --}}
                        <td class="p-3 text-center">
                            {{ $row->total_peserta }}
                        </td>

                        {{-- TOTAL TAGIHAN --}}
                        <td class="p-3 text-center">
                            Rp {{ number_format($row->total_tagihan, 0, ',', '.') }}
                        </td>

                        {{-- TOTAL LUNAS --}}
                        <td class="p-3 text-center">
                            Rp {{ number_format($row->total_lunas, 0, ',', '.') }}
                        </td>

                        {{-- FINANCE --}}
                        <td class="p-3 text-center">
                            @if($row->finance_ok)
                                <span class="text-green-700 font-semibold">
                                    ✔ ACC
                                </span>
                            @else
                                <span class="text-red-600">
                                    Belum
                                </span>
                            @endif
                        </td>

                        {{-- SERTIFIKAT --}}
                        <td class="p-3 text-center">
                            @if($row->finance_ok && $row->status_event === 'done')
                                <span class="text-green-700">
                                    Siap Dicatat
                                </span>
                            @else
                                <span class="text-gray-500">
                                    Belum Bisa
                                </span>
                            @endif
                        </td>

                        {{-- AKSI --}}
                        <td class="p-3 text-center space-x-2">

                            {{-- INHOUSE --}}
                            @if($row->jenis_event === 'INHOUSE')

                                @if(!$row->finance_ok && $row->status_event === 'done')
                                    <form
                                        action="{{ route('event-training.approve', $row->event->id) }}"
                                        method="POST"
                                        class="inline"
                                    >
                                        @csrf
                                        <button
                                            class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                                        >
                                            ACC Finance
                                        </button>
                                    </form>
                                @else
                                    <span class="text-xs text-gray-400">
                                        -
                                    </span>
                                @endif

                            {{-- REGULER --}}
                            @else
                                <a
                                    href="{{ route('event-training.show', $row->event->id) }}"
                                    class="px-3 py-1 text-sm bg-blue-600 text-white rounded hover:bg-blue-700"
                                >
                                    Detail
                                </a>
                            @endif

                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500">
                            Belum ada data laporan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</x-app-layout>
