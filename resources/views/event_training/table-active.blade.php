<div class="overflow-x-auto">

<table class="w-full text-sm">
<thead>
<tr class="border-b text-gray-500">
    <th class="py-4 px-6 text-left">Master Training</th>
    <th class="px-6 text-left">Job</th>
    <th class="px-6 text-left">Tanggal</th>
    <th class="px-6 text-center">Total Event</th>
    <th class="px-6 text-right">Aksi</th>
</tr>
</thead>

<tbody>
@forelse($groups as $group)

@php
$start = $group->events->min('tanggal_start');
$end   = $group->events->max('tanggal_end');
@endphp

<tr class="border-b hover:bg-gray-50 transition">

    {{-- MASTER --}}
    <td class="py-4 px-6 font-semibold text-gray-800">
        {{ $group->masterTraining->nama_training }}
    </td>

    {{-- JOB --}}
    <td class="px-6 text-gray-600">
        {{ $group->job_number }}
    </td>

    {{-- TANGGAL --}}
    <td class="px-6 text-gray-600">
        @if($start && $end)
            {{ \Carbon\Carbon::parse($start)->format('d M Y') }}
            —
            {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
        @else
            -
        @endif
    </td>

    {{-- TOTAL EVENT --}}
    <td class="px-6 text-center">
        <span class="font-semibold text-[#0f3d2e]">
            {{ $group->events->count() }}
        </span>
    </td>

    {{-- AKSI --}}
    <td class="px-6 text-right">
        <a href="{{ route('event-training.group.show',$group->id) }}"
           class="alkon-btn-primary text-xs">
            Detail
        </a>
    </td>

</tr>

@empty
<tr>
<td colspan="5" class="text-center py-12 text-gray-400">
Tidak ada event bulan ini
</td>
</tr>
@endforelse
</tbody>
</table>

</div>

{{-- PAGINATION --}}
@if($groups->hasPages())
<div class="mt-6 flex justify-center">
{{ $groups->links() }}
</div>
@endif