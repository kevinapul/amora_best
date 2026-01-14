<table class="w-full bg-white border-collapse">
<thead>
<tr class="border-b">
    <th>No</th>
    <th>Master Training</th>
    <th>Job</th>
    <th class="text-center">Event</th>
    <th>Tempat</th>
    <th>Status</th>
    <th>Aksi</th>
</tr>
</thead>

<tbody>
@foreach($groups as $i => $group)
<tr class="border-b">
    <td>{{ $i+1 }}</td>
    <td class="font-semibold">{{ $group->masterTraining->nama_training }}</td>
    <td>{{ $group->job_number ?? '-' }}</td>
    <td class="text-center">{{ $group->events->count() }}</td>
    <td>{{ $group->tempat ?? '-' }}</td>
    <td>
        <span class="bg-green-200 px-2 py-1 text-xs rounded">ACTIVE</span>
    </td>
    <td>
        <a href="{{ route('event-training-group.edit', $group) }}"
           class="px-3 py-1 bg-blue-600 text-white rounded">
            Detail
        </a>
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $groups->links() }}
