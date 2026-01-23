<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\EventStaff;
use Illuminate\Http\Request;

class EventStaffController extends Controller
{
    // Tampilkan list staf untuk 1 event
    public function show($eventId)
{
    $event = EventTraining::with(['training', 'participants'])->findOrFail($eventId);

    $staffs = EventStaff::where('event_training_id', $eventId)
        ->get()
        ->groupBy('role')
        ->map(fn ($items) => $items->pluck('name')->implode(', '));

    return view('event_training.show', compact('event', 'staffs'));
}


    public function eventIndex(Request $request)
{
    $search = $request->search;

    $events = EventTraining::with('training')
        ->when($search, function ($query) use ($search) {
            $query->whereHas('training', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%");
            });
        })
        ->orderBy('id', 'DESC')
        ->paginate(5);

    return view('event_staff.event_index', compact('events', 'search'));
}


    // Form tambah staf
    public function create($eventId)
    {
        $event = EventTraining::with('training')->findOrFail($eventId);

        return view('event_staff.create', compact('event'));
    }

    // Store staf
    public function store(Request $request, $eventId)
{
    $request->validate([
        'instrukturs' => 'nullable|array|max:2',
        'instrukturs.*' => 'string|max:255',

        'training_officers' => 'nullable|array|max:2',
        'training_officers.*' => 'string|max:255',
    ]);

    // Simpan Instruktur
    foreach ($request->instrukturs ?? [] as $name) {
        EventStaff::create([
            'event_training_id' => $eventId,
            'name' => $name,
            'role' => 'Instruktur',
        ]);
    }

    // Simpan Training Officer
    foreach ($request->training_officers ?? [] as $name) {
        EventStaff::create([
            'event_training_id' => $eventId,
            'name' => $name,
            'role' => 'Training Officer',
        ]);
    }

    return redirect()
        ->route('event-staff.show', $eventId)
        ->with('success', 'Staf berhasil ditambahkan');
}


    // Hapus staf
    public function destroy($id)
    {
        $staff = EventStaff::findOrFail($id);
        $eventId = $staff->event_training_id;

        $staff->delete();

        return redirect()->route('event-staff.show', $eventId)
                         ->with('success', 'Data staf berhasil dihapus');
    }
}
