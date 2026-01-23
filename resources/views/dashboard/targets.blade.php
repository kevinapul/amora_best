
<div class="bg-white shadow rounded-lg p-6 mb-6">

    {{-- TABLE TARGET --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse text-sm">
            <thead>
                <tr class="bg-gray-100">
                    <th class="border px-4 py-2 text-left">Jenis Target</th>
                    <th class="border px-4 py-2 text-right">Nominal</th>
                    <th class="border px-4 py-2 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                {{-- TARGET BAWAH --}}
                <tr>
                    <td class="border px-4 py-2">Target Bawah</td>
                    <td class="border px-4 py-2 text-right">
                        Rp {{ number_format($targetBawah, 0, ',', '.') }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        @if($totalRealisasi >= $targetBawah)
                            <span class="text-green-600 font-semibold">✔ Tercapai</span>
                        @else
                            <span class="text-gray-500">Belum</span>
                        @endif
                    </td>
                </tr>

                {{-- TARGET TENGAH --}}
                <tr>
                    <td class="border px-4 py-2">Target Tengah</td>
                    <td class="border px-4 py-2 text-right">
                        Rp {{ number_format($targetTengah, 0, ',', '.') }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        @if($totalRealisasi >= $targetTengah)
                            <span class="text-green-600 font-semibold">✔ Tercapai</span>
                        @else
                            <span class="text-gray-500">Belum</span>
                        @endif
                    </td>
                </tr>

                {{-- TARGET ATAS --}}
                <tr>
                    <td class="border px-4 py-2">Target Atas</td>
                    <td class="border px-4 py-2 text-right">
                        Rp {{ number_format($targetAtas, 0, ',', '.') }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        @if($totalRealisasi >= $targetAtas)
                            <span class="text-green-700 font-bold">🏆 Tercapai</span>
                        @else
                            <span class="text-gray-500">Belum</span>
                        @endif
                    </td>
                </tr>

                {{-- REALISASI --}}
                <tr class="bg-green-50 font-semibold">
                    <td class="border px-4 py-2">Realisasi Tahun Ini</td>
                    <td class="border px-4 py-2 text-right text-green-700">
                        Rp {{ number_format($totalRealisasi, 0, ',', '.') }}
                    </td>
                    <td class="border px-4 py-2 text-center">
                        {{ $statusTarget }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- PROGRESS BAR --}}
@php
    $progress = $targetAtas > 0
        ? min(100, ($totalRealisasi / $targetAtas) * 100)
        : 0;
@endphp

    <div class="mt-4">
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-green-500 h-3 rounded-full transition-all"
                 style="width: {{ $progress }}%">
            </div>
        </div>
        <p class="text-xs text-gray-600 mt-1">
            {{ number_format($progress, 1) }}% menuju target atas
        </p>
    </div>

</div>