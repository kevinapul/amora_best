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
        $event = EventTraining::with('training')->findOrFail($eventId);
        $staff = EventStaff::where('event_training_id', $eventId)->get();

        return view('event_staff.show', compact('event', 'staff'));
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
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:Instruktur,Training Officer',
        ]);

        EventStaff::create([
            'event_training_id' => $eventId,
            'name' => $request->name,
            'phone' => $request->phone,
            'role' => $request->role,
        ]);

        return redirect()->route('event-staff.show', $eventId)
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
