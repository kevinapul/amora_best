<table class="w-full border-collapse bg-yellow-50">
    <thead>
        <tr class="border-b">
            <th class="p-2">No</th>
            <th class="p-2">Training</th>
            <th class="p-2">Tanggal</th>
            <th class="p-2">Tempat</th>
            <th class="p-2 text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>
        @foreach($events as $i => $event)
            <tr class="border-b hover:bg-yellow-100">
                <td class="p-2 text-center">{{ $i + 1 }}</td>

                <td class="p-2 font-semibold">
                    {{ $event->training->name }}
                </td>

                <td class="py-2 px-3">
                    {{ $event->tanggal_start->format('d M Y') }}
                    –
                    {{ $event->tanggal_end->format('d M Y') }}
                </td>

                <td class="p-2">{{ $event->tempat }}</td>

                <td class="p-2 text-center flex gap-2 justify-center">

                    @can('approve', $event)
                        <form method="POST"
                              action="{{ route('event-training.approve', $event->id) }}">
                            @csrf
                            <button class="px-3 py-1 bg-green-600 text-white rounded">
                                ACC
                            </button>
                        </form>
                    @endcan

                    @can('update', $event)
                        <a href="{{ route('event-training.edit', $event->id) }}"
                           class="px-3 py-1 bg-blue-500 text-white rounded">
                            Edit
                        </a>
                    @endcan

                    @can('delete', $event)
                        <form method="POST"
                              action="{{ route('event-training.destroy', $event->id) }}">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 bg-red-600 text-white rounded">
                                Hapus
                            </button>
                        </form>
                    @endcan

                </td>
            </tr>
        @endforeach
    </tbody>
</table>
