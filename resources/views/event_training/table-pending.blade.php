<table class="w-full bg-yellow-50 border-collapse">
<thead>
<tr class="border-b">
    <th>No</th>
    <th>Master Training</th>
    <th>Job</th>
    <th class="text-center">Event</th>
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
    <td>
        @can('approve', $group)
<form method="POST"
      action="{{ route('event-training-group.approve', $group) }}">
    @csrf
    <button class="px-3 py-1 bg-green-600 text-white rounded">
        ACC Semua
    </button>
</form>
@endcan
    </td>
</tr>
@endforeach
</tbody>
</table>

{{ $groups->links() }}
