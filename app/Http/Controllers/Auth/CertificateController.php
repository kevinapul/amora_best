<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\EventTraining;
use App\Models\Participant;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    // List semua sertifikat untuk 1 event
    public function index($eventId)
    {
        $event = EventTraining::with(['participants'])->findOrFail($eventId);
        $certificates = Certificate::with('participant')
            ->where('event_training_id', $eventId)
            ->get();

        return view('certificates.index', compact('event', 'certificates'));
    }

    // Form tambah sertifikat
    public function create($eventId)
    {
        $event = EventTraining::with('participants')->findOrFail($eventId);

        // peserta event saja
        $participants = $event->participants;

        return view('certificates.create', compact('event', 'participants'));
    }

    // Simpan
    public function store(Request $request)
    {
        $request->validate([
            'event_training_id' => 'required|exists:event_trainings,id',
            'participant_id' => 'required|exists:participants,id',
            'nomor_sertifikat' => 'required|string|max:255',
            'tanggal_terbit' => 'required|date',
            'tanggal_expired' => 'nullable|date',
        ]);

        Certificate::create($request->all());

        return redirect()
            ->route('certificates.index', $request->event_training_id)
            ->with('success', 'Sertifikat berhasil ditambahkan!');
    }

    // Hapus
    public function destroy($id)
    {
        $cert = Certificate::findOrFail($id);
        $eventId = $cert->event_training_id;

        $cert->delete();

        return redirect()
            ->route('certificates.index', $eventId)
            ->with('success', 'Sertifikat berhasil dihapus!');
    }

    
}
