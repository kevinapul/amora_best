<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventParticipantController extends Controller
{
    /* ================== LIST PESERTA PER EVENT ================== */
    public function index(Request $request)
    {
        $search = $request->search;

        $events = EventTraining::with([
            'training',
            'participants',
            'participants.certificates'
        ])
        ->withCount('participants')
        ->when($search, function ($query) use ($search) {
            $query->whereHas('training', fn($q) => $q->where('name', 'like', "%{$search}%"));
        })
        ->orderBy('tanggal_start', 'DESC')
        ->paginate(10)
        ->withQueryString();

        return view('event_training.peserta_index', compact('events', 'search'));
    }

    /* ================== CREATE PESERTA ================== */
    public function create(EventTraining $event)
    {
        $event->load('training');
        $this->authorize('addParticipant', $event);

        return view('event_participant.create', compact('event'));
    }

    /* ================== STORE PESERTA ================== */
    public function store(Request $request, EventTraining $event)
    {
        $this->authorize('addParticipant', $event);

        $rules = [
            'nama'          => 'required|string|max:255',
            'perusahaan'    => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:255',
            'nik'           => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'catatan'       => 'nullable|string',
        ];

        if ($event->jenis_event === 'reguler') {
            $rules['harga_peserta'] = 'required|integer|min:0';
        }

        $validated = $request->validate($rules);

        // Cegah duplikasi NIK
        if (!empty($validated['nik'])) {
            $exists = $event->participants()
                ->where('nik', $validated['nik'])
                ->exists();
            if ($exists) {
                return back()->with('error', 'Peserta dengan NIK ini sudah terdaftar.');
            }
        }

        DB::transaction(function () use ($validated, $event) {
            $participant = Participant::create([
                'nama'          => $validated['nama'],
                'perusahaan'    => $validated['perusahaan'] ?? null,
                'no_hp'         => $validated['no_hp'] ?? null,
                'alamat'        => $validated['alamat'] ?? null,
                'nik'           => $validated['nik'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'catatan'       => $validated['catatan'] ?? null,
            ]);

            $event->participants()->attach($participant->id, [
                'harga_peserta' => $event->jenis_event === 'reguler'
                    ? $validated['harga_peserta']
                    : 0,
            ]);
        });

        return redirect()->route('event-training.show', $event)
            ->with('success', 'Peserta berhasil ditambahkan!');
    }

    /* ================== UPDATE PESERTA ================== */
    public function update(Request $request, EventTraining $event, Participant $participant)
    {
        $this->authorize('updateParticipant', $event);

        $rules = [
            'nama'          => 'required|string|max:255',
            'perusahaan'    => 'nullable|string|max:255',
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string|max:255',
            'nik'           => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'catatan'       => 'nullable|string',
        ];

        if ($event->jenis_event === 'reguler') {
            $rules['harga_peserta'] = 'required|integer|min:0';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($validated, $participant, $event) {
            $participant->update([
                'nama'          => $validated['nama'],
                'perusahaan'    => $validated['perusahaan'] ?? null,
                'no_hp'         => $validated['no_hp'] ?? null,
                'alamat'        => $validated['alamat'] ?? null,
                'nik'           => $validated['nik'] ?? null,
                'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
                'catatan'       => $validated['catatan'] ?? null,
            ]);

            if ($event->jenis_event === 'reguler' && isset($validated['harga_peserta'])) {
                $event->participants()->updateExistingPivot($participant->id, [
                    'harga_peserta' => $validated['harga_peserta']
                ]);
            }
        });

        return back()->with('success', 'Data peserta berhasil diperbarui.');
    }

    /* ================== DELETE PESERTA ================== */
    public function destroy(EventTraining $event, Participant $participant)
    {
        $this->authorize('deleteParticipant', $event);

        $event->participants()->detach($participant->id);
        $participant->delete();

        return back()->with('success', 'Peserta berhasil dihapus.');
    }

    /* ================== FINANCE PESERTA ================== */
    public function markPaid(EventTraining $event, Participant $participant)
    {
        $this->authorize('updateFinance', $event);

        abort_if($event->jenis_event !== 'reguler', 403);
        abort_if($event->status !== 'done', 403);
        abort_if($event->finance_approved, 403);

        $pivot = $event->participants()
            ->where('participant_id', $participant->id)
            ->firstOrFail()
            ->pivot;

        $pivot->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        if ($event->participants->every(fn ($p) => $p->pivot->is_paid)) {
            $event->update([
                'finance_approved' => true,
                'finance_approved_at' => now(),
            ]);
        }

        return back()->with('success', "Peserta {$participant->nama} telah lunas.");
    }

    /* ================== RECORD SERTIFIKAT (ADMINISTRASI) ================== */
    public function markCertificateRecorded(EventTraining $event, Participant $participant)
    {
        $this->authorize('updateCertificate', $event);

        $pivot = $event->participants()
            ->where('participant_id', $participant->id)
            ->firstOrFail()
            ->pivot;

        $pivot->certificate_ready = true;
        $pivot->certificate_issued_at = now();
        $pivot->save();

        return back()->with(
            'success',
            "Data sertifikat peserta {$participant->nama} berhasil dicatat.");
    }


    
}
