<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">

    {{-- REGULER --}}
    <div class="bg-white p-5 shadow rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-blue-700">Training Reguler Bulan Ini</h3>

        <!-- Scroll wrapper -->
        <div class="overflow-y-auto max-h-56">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-3 py-2">Judul</th>
                        <th class="border px-3 py-2">Tanggal</th>
                        <th class="border px-3 py-2">Peserta</th>
                        <th class="border px-3 py-2">Tempat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reguler as $item)
                        <tr>
                            <td class="border px-3 py-2">{{ $item->training->name }}</td>
                            <td class="border px-3 py-2 text-center">
                                {{ \Carbon\Carbon::parse($item->tanggal_start)->format('d') }}
                                –
                                {{ \Carbon\Carbon::parse($item->tanggal_end)->format('d') }}
                            </td>

                            <td class="border px-3 py-2 text-center">{{ $item->participants_count }}</td>
                            <td class="border px-3 py-2">{{ $item->tempat }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border px-3 py-2 text-center text-gray-500">
                                Tidak ada training reguler bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- INHOUSE --}}
    <div class="bg-white p-5 shadow rounded-lg">
        <h3 class="text-lg font-semibold mb-3 text-green-700">Training Inhouse Bulan Ini</h3>

        <!-- Scroll wrapper -->
        <div class="overflow-y-auto max-h-56">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="border px-3 py-2">Judul</th>
                        <th class="border px-3 py-2">Tanggal</th>
                        <th class="border px-3 py-2">Peserta</th>
                        <th class="border px-3 py-2">Tempat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inhouse as $item)
                        <tr>
                            <td class="border px-3 py-2">{{ $item->training->name }}</td>
                            <td class="border px-3 py-2 text-center">
                                {{ \Carbon\Carbon::parse($item->tanggal_start)->format('d') }}
                                –
                                {{ \Carbon\Carbon::parse($item->tanggal_end)->format('d') }}
                            </td>
                            <td class="border px-3 py-2 text-center">{{ $item->participants_count }}</td>
                            <td class="border px-3 py-2">{{ $item->tempat }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="border px-3 py-2 text-center text-gray-500">
                                Tidak ada training inhouse bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
