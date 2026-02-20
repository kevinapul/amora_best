<div class="alkon-panel">


    <div class="alkon-panel-header flex justify-between items-center">
        <span>Daftar Event Training</span>

    </div>

    <div class="alkon-panel-body overflow-x-auto">

        <table class="w-full text-sm">
            <thead class="border-b text-gray-500">
                <tr>
                    <th class="py-3 text-left">Training</th>
                    <th class="text-center">Tanggal</th>
                    <th class="text-center">Tempat</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>

            <tbody>

                @forelse($events as $event)
                    <tr class="border-b hover:bg-gray-50">

                        <td class="py-3 font-semibold text-gray-800">
                            {{ $event->training->name ?? '-' }}
                        </td>

                        <td class="text-center text-gray-600">
                            {{ \Carbon\Carbon::parse($event->tanggal_start)->translatedFormat('d M') }}
                            -
                            {{ \Carbon\Carbon::parse($event->tanggal_end)->translatedFormat('d M Y') }}
                        </td>

                        <td class="text-center text-gray-600">
                            {{ $event->group->tempat ?? '-' }}
                        </td>

                        <td class="text-right">
                            <div class="flex justify-end gap-2">

                                <a href="{{ route('event-staff.show', $event->id) }}"
                                    class="alkon-btn-secondary text-xs">
                                    Detail
                                </a>

                                <a href="{{ route('event-staff.create', $event->id) }}"
                                    class="alkon-btn-primary text-xs">
                                    Tambah Staf
                                </a>

                            </div>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-400 py-10">
                            Tidak ada event ditemukan
                        </td>
                    </tr>
                @endforelse

            </tbody>
        </table>

    </div>


</div>

{{-- PAGINATION --}}
@if ($events->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $events->appends(['search' => $search])->links() }}
    </div>
@endif
