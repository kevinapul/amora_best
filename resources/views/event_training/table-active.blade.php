<table class="w-full border-collapse bg-white">
    <thead>
        <tr class="border-b bg-gray-50">
            <th class="px-4 py-3 text-center w-12">No</th>
            <th class="px-4 py-3 text-left">Master Training</th>
            <th class="px-4 py-3 text-left">Job Number</th>
            <th class="px-4 py-3 text-center">Tanggal</th>
            <th class="px-4 py-3 text-center w-32">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($groups as $i => $group)

            @php
                $startDate = $group->events->min('tanggal_start');
                $endDate   = $group->events->max('tanggal_end');
            @endphp

            <tr class="border-b hover:bg-gray-50 transition">

                {{-- NO --}}
                <td class="px-4 py-3 text-center text-gray-600">
                    {{ $groups->firstItem() + $i }}
                </td>

                {{-- MASTER TRAINING --}}
                <td class="px-4 py-3 font-semibold text-gray-800">
                    {{ $group->masterTraining->nama_training ?? '-' }}
                </td>

                {{-- JOB NUMBER --}}
                <td class="px-4 py-3 text-gray-700">
                    {{ $group->job_number ?? '-' }}
                </td>

                {{-- TANGGAL --}}
                <td class="px-4 py-3 text-center text-gray-600">
                    @if ($startDate && $endDate)
                        {{ \Carbon\Carbon::parse($startDate)->format('d') }}
                        –
                        {{ \Carbon\Carbon::parse($endDate)->format('d') }}
                    @else
                        -
                    @endif
                </td>

                {{-- AKSI --}}
                <td class="px-4 py-3 text-center">
                    <a href="{{ route('event-training.group.show', $group->id) }}"
                       class="alkon-btn-secondary">
                        Detail
                    </a>
                </td>

            </tr>

        @empty
            <tr>
                <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                    Tidak ada data event
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $groups->links() }}
</div>
