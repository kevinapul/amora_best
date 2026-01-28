@php
    $reguler = $reguler ?? collect();
    $inhouse = $inhouse ?? collect();
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

    {{-- ================= REGULER ================= --}}
    <div class="alkon-panel">
        <div class="alkon-panel-header">
            <h3 class="text-base font-semibold text-[var(--alkon-text)]">
                 Training Reguler Bulan Ini
            </h3>
        </div>

        <div class="alkon-panel-body p-0">
            <div class="overflow-y-auto max-h-64">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-center">Tanggal</th>
                            <th class="px-4 py-3 text-center">Peserta</th>
                            <th class="px-4 py-3 text-left">Tempat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($reguler as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium">
                                    {{ $item->training->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
                                    {{ optional($item->tanggal_start)->format('d') ?? '-' }}
                                    –
                                    {{ optional($item->tanggal_end)->format('d') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $item->participants_count ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $item->tempat ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                    Tidak ada training reguler bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ================= INHOUSE ================= --}}
    <div class="alkon-panel">
        <div class="alkon-panel-header">
            <h3 class="text-base font-semibold text-[var(--alkon-text)]">
                 Training Inhouse Bulan Ini
            </h3>
        </div>

        <div class="alkon-panel-body p-0">
            <div class="overflow-y-auto max-h-64">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Judul</th>
                            <th class="px-4 py-3 text-center">Tanggal</th>
                            <th class="px-4 py-3 text-center">Peserta</th>
                            <th class="px-4 py-3 text-left">Tempat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($inhouse as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 font-medium">
                                    {{ $item->training->name ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">
                                    {{ optional($item->tanggal_start)->format('d') ?? '-' }}
                                    –
                                    {{ optional($item->tanggal_end)->format('d') ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ $item->participants_count ?? 0 }}
                                </td>
                                <td class="px-4 py-3 text-gray-600">
                                    {{ $item->tempat ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-6 text-center text-gray-400">
                                    Tidak ada training inhouse bulan ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
