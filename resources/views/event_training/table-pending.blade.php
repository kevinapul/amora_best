<div class="overflow-x-auto">

    <table class="w-full text-sm">
        <thead>
            <tr class="border-b text-gray-500">
                <th class="py-4 px-6 text-left w-16">No</th>
                <th class="px-6 text-left">Master Training</th>
                <th class="px-6 text-left">Job</th>
                <th class="px-6 text-center">Total Event</th>
                <th class="px-6 text-center">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($groups as $i => $group)
                <tr class="border-b hover:bg-yellow-50/40 transition">

                    {{-- NO --}}
                    <td class="py-4 px-6 text-gray-500">
                        {{ $groups->firstItem() + $i }}
                    </td>

                    {{-- MASTER --}}
                    <td class="px-6 font-semibold text-gray-800">
                        {{ $group->masterTraining->nama_training }}
                    </td>

                    {{-- JOB --}}
                    <td class="px-6 text-gray-600">
                        {{ $group->job_number ?? '-' }}
                    </td>

                    {{-- TOTAL EVENT --}}
                    <td class="px-6 text-center">
                        <span class="font-semibold text-amber-600">
                            {{ $group->events->count() }}
                        </span>
                    </td>

                    {{-- AKSI --}}
                    <td class="px-6 text-center">
                        <div class="flex justify-end gap-2">

                            {{-- ACC --}}
                            @can('approve', $group)
                                <form method="POST"
                                    action="{{ route('master-training.event-training-group.approve', $group) }}">
                                    @csrf
                                    <button type="submit"
                                        class="alkon-btn-primary text-xs bg-green-600 hover:bg-green-700">
                                        ACC
                                    </button>
                                </form>
                            @endcan

                            {{-- EDIT --}}
<a href="{{ route('event-training.edit', $group->events->first()->id) }}"
    class="alkon-btn-secondary text-xs">
    Edit
</a>
                            {{-- DELETE --}}
                            <form method="POST" action="{{ route('event-training.destroy', $group->id) }}"
                                onsubmit="return confirm('Yakin hapus group ini?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                    class="text-xs px-3 py-1 rounded border border-red-300 text-red-600 hover:bg-red-50">
                                    Hapus
                                </button>
                            </form>

                        </div>
                    </td>

                </tr>

            @empty
                <tr>
                    <td colspan="5" class="text-center py-12 text-gray-400">
                        Tidak ada pending approval
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</div>

{{-- PAGINATION --}}
@if ($groups->hasPages())
    <div class="mt-6 flex justify-center">
        {{ $groups->links() }}
    </div>
@endif
