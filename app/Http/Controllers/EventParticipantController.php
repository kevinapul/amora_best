<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Company;

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

    // ================= VALIDASI =================
$rules = [
    'participants' => 'required|array|min:1',

    'participants.*.nama' => 'required|string|max:255',
    'participants.*.no_hp' => 'nullable|string|max:20',
    'participants.*.nik'   => 'nullable|string|max:100',

    'participants.*.jenis_layanan' =>
        'required|in:pelatihan,pelatihan_sertifikasi,sertifikasi_resertifikasi',
];

// 🔒 REGULER → perusahaan WAJIB
if ($event->isReguler()) {
    $rules['perusahaan'] = 'required|string|max:255';
    $rules['participants.*.harga_peserta'] = 'required|integer|min:0';
}

// 🟡 INHOUSE → perusahaan boleh kosong
if ($event->isInhouse()) {
    $rules['perusahaan'] = 'nullable|string|max:255';
}

    $validated = $request->validate($rules);

    // ================= STORE =================
DB::transaction(function () use ($request, $event) {

    $companyId = null;
    $companyName = trim((string) $request->input('perusahaan'));

    if ($companyName !== '') {
        $company = Company::firstOrCreate([
            'name' => $companyName,
        ]);

        $companyId = $company->id;
    }

    foreach ($request->input('participants') as $p) {

        $participant = Participant::create([
            'company_id' => $companyId,
            'nama'       => $p['nama'],
            'perusahaan' => $companyName ?: null,
            'no_hp'      => $p['no_hp'] ?? null,
            'nik'        => $p['nik'] ?? null,
        ]);

        $hargaPeserta = $event->isInhouse()
            ? 0
            : (int) $p['harga_peserta'];

        $event->participants()->attach($participant->id, [
            'jenis_layanan'    => $p['jenis_layanan'],
            'harga_peserta'    => $hargaPeserta,
            'paid_amount'      => 0,
            'remaining_amount' => $hargaPeserta,
            'is_paid'          => $event->isInhouse(),
        ]);
    }
});

    return redirect()
        ->route('event-training.show', $event)
        ->with('success', 'Peserta berhasil ditambahkan');
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

        if ($event->isReguler()) {
    $rules['harga_peserta'] = 'required|integer|min:0';
}

// 🔒 INHOUSE: harga TIDAK BOLEH DIUBAH
if ($event->isInhouse()) {
    unset($rules['harga_peserta']);
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

            if ($event->isReguler() && isset($validated['harga_peserta'])) {
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
    /**
 * @deprecated
 * DO NOT USE.
 * Replaced by Invoice payment flow.
 */
    public function markPaid(EventTraining $event, Participant $participant)
    {
        $this->authorize('updateFinance', $event);

        abort_if(! $event->isReguler(), 403);
        $this->authorize('updateFinance', $event);
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
