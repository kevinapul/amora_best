<table class="w-full border-collapse bg-white">
    <thead>
        <tr class="border-b">
            <th class="p-2">No</th>
            <th class="p-2">Master Training</th>
            <th class="p-2">Job</th>
            <th class="p-2 text-center">Event</th>
            <th class="p-2">Tempat</th>
            <th class="p-2 text-center">Status</th>
            <th class="p-2 text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse($groups as $i => $group)
            <tr class="border-b hover:bg-gray-50">
                <td class="p-2 text-center">
                    {{ $i + 1 }}
                </td>

                <td class="p-2 font-semibold">
                    {{ $group->masterTraining->nama_training }}
                </td>

                <td class="p-2">
                    {{ $group->job_number ?? '-' }}
                </td>

                <td class="p-2 text-center">
                    {{ $group->events->count() }}
                </td>

                <td class="p-2">
                    {{ $group->tempat ?? '-' }}
                </td>

@php
    $statusClass = match ($group->status) {
        'active' => 'bg-green-200 text-green-800',
        'pending' => 'bg-yellow-200 text-yellow-800',
        'done' => 'bg-blue-200 text-blue-800',
        default => 'bg-gray-200 text-gray-800',
    };
@endphp

<td class="p-2 text-center">
    <span class="px-2 py-1 rounded text-xs font-semibold {{ $statusClass }}">
        {{ strtoupper($group->status) }}
    </span>
</td>


                <td class="p-2 text-center">
                    <a href="{{ route('event-training.group.show', $group->id) }}"
                        class="px-3 py-1 bg-indigo-600 text-white rounded">
                        Detail
                    </a>

                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="p-4 text-center text-gray-500">
                    Tidak ada data aktif
                </td>
            </tr>
        @endforelse
    </tbody>
</table>

{{ $groups->links() }}
