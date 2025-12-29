<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Participant;
use App\Models\Certificate;
use App\Models\Training;
use Illuminate\Http\Request;

class EventTrainingController extends Controller
{
    /* ================== LIST & SEARCH ================== */
    public function index(Request $request)
    {
        $this->authorize('viewAny', EventTraining::class);

        $search = $request->search;

        $eventsActive = EventTraining::with('training')
            ->withCount('participants')
            ->whereIn('status', ['active', 'on_progress', 'done'])
            ->when($search, function ($query) use ($search) {
                $query->whereHas('training', fn ($q) =>
                    $q->where('name', 'like', "%{$search}%")
                )->orWhere('job_number', 'like', "%{$search}%");
            })
            ->orderBy('tanggal_start', 'DESC')
            ->paginate(10)
            ->withQueryString();

        foreach ($eventsActive as $event) {
            $event->refreshStatus();
        }

        $eventsPending = auth()->user()->can('viewPending', EventTraining::class)
            ? EventTraining::with('training')
                ->withCount('participants')
                ->where('status', 'pending')
                ->orderBy('created_at', 'DESC')
                ->get()
            : null;

        return view('event_training.index', compact(
            'eventsActive',
            'eventsPending',
            'search'
        ));
    }

    /* ================== CRUD EVENT ================== */
    public function create()
    {
        $this->authorize('create', EventTraining::class);
        return view('event_training.create', [
            'trainings' => Training::all()
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', EventTraining::class);

        $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'jenis_event' => 'required|in:reguler,inhouse',
            'harga_paket' => 'nullable|numeric|required_if:jenis_event,inhouse',
            'job_number'  => 'required|string|unique:event_trainings,job_number',
            'start_day'   => 'required|integer',
            'start_month' => 'required|integer',
            'start_year'  => 'required|integer',
            'end_day'     => 'required|integer',
            'end_month'   => 'required|integer',
            'end_year'    => 'required|integer',
            'tempat' => 'required|string',
            'jenis_sertifikasi' => 'required|in:Kementrian,Bnsp,Alkon Best Mandiri',
            'sertifikasi' => 'nullable|string',
        ]);

        EventTraining::create([
            'training_id' => $request->training_id,
            'jenis_event' => $request->jenis_event,
            'harga_paket' => $request->jenis_event === 'inhouse'
                ? $request->harga_paket
                : null,
            'job_number' => $request->job_number,
            'tanggal_start' => "{$request->start_year}-{$request->start_month}-{$request->start_day}",
            'tanggal_end'   => "{$request->end_year}-{$request->end_month}-{$request->end_day}",
            'tempat' => $request->tempat,
            'jenis_sertifikasi' => $request->jenis_sertifikasi,
            'sertifikasi' => $request->sertifikasi,
            'status' => 'pending',
        ]);

        return redirect()
            ->route('event-training.index')
            ->with('success', 'Event training berhasil ditambahkan (Pending ACC).');
    }

    public function edit(EventTraining $eventTraining)
    {
        $this->authorize('update', $eventTraining);
        return view('event_training.edit', [
            'eventTraining' => $eventTraining,
            'trainings' => Training::all()
        ]);
    }

    public function update(Request $request, EventTraining $eventTraining)
    {
        $this->authorize('update', $eventTraining);

        $request->validate([
            'training_id' => 'required|exists:trainings,id',
            'jenis_event' => 'required|in:reguler,inhouse',
            'harga_paket' => 'nullable|numeric|required_if:jenis_event,inhouse',
            'job_number'  => 'required|string',
            'start_day'   => 'required|integer',
            'start_month' => 'required|integer',
            'start_year'  => 'required|integer',
            'end_day'     => 'required|integer',
            'end_month'   => 'required|integer',
            'end_year'    => 'required|integer',
            'tempat' => 'required|string',
            'jenis_sertifikasi' => 'required|in:Kementrian,Bnsp,Alkon Best Mandiri',
            'sertifikasi' => 'nullable|string',
        ]);

        $eventTraining->update([
            'training_id' => $request->training_id,
            'jenis_event' => $request->jenis_event,
            'harga_paket' => $request->jenis_event === 'inhouse'
                ? $request->harga_paket
                : null,
            'job_number' => $request->job_number,
            'tanggal_start' => "{$request->start_year}-{$request->start_month}-{$request->start_day}",
            'tanggal_end'   => "{$request->end_year}-{$request->end_month}-{$request->end_day}",
            'tempat' => $request->tempat,
            'jenis_sertifikasi' => $request->jenis_sertifikasi,
            'sertifikasi' => $request->sertifikasi,
        ]);

        return redirect()
            ->route('event-training.index')
            ->with('success', 'Event training berhasil diperbarui!');
    }

    public function destroy(EventTraining $eventTraining)
    {
        $this->authorize('delete', $eventTraining);
        $eventTraining->delete();

        return back()->with('success', 'Event training berhasil dihapus!');
    }

    public function approve(EventTraining $eventTraining)
    {
        $this->authorize('approve', $eventTraining);

        $eventTraining->update(['status' => 'active']);
        $eventTraining->refreshStatus();

        return back()->with('success', 'Event berhasil di-ACC.');
    }

    /* ================== FINANCE ================== */
    public function approveFinance(EventTraining $eventTraining)
    {
        $this->authorize('approveFinance', $eventTraining);

        abort_if($eventTraining->status !== 'done', 403);
        abort_if($eventTraining->finance_approved, 403);

        // INHOUSE
        if ($eventTraining->jenis_event === 'inhouse') {
            foreach ($eventTraining->participants as $p) {
                $p->pivot->update([
                    'is_paid' => true,
                    'paid_at' => now(),
                ]);
            }
        }

        // REGULER CHECK
        if ($eventTraining->jenis_event === 'reguler') {
            abort_if(
                ! $eventTraining->participants->every(fn ($p) => $p->pivot->is_paid),
                403,
                'Masih ada peserta yang belum lunas.'
            );
        }

        $eventTraining->update([
            'finance_approved' => true,
            'finance_approved_at' => now(),
        ]);

        return back()->with('success', 'Finance berhasil di-ACC.');
    }

    

    /* ================== CERTIFICATE ================== */
    public function generateCertificate(EventTraining $eventTraining, Participant $participant = null)
    {
        $this->authorize('generateCertificate', $eventTraining);

        abort_if(! $eventTraining->finance_approved, 403);

        $participants = $participant
            ? collect([$participant])
            : $eventTraining->participants;

        foreach ($participants as $p) {
            $p->pivot->update([
                'certificate_ready' => true,
                'certificate_issued_at' => now(),
            ]);

            Certificate::updateOrCreate(
                [
                    'participant_id' => $p->id,
                    'event_training_id' => $eventTraining->id,
                ],
                [
                    'nomor_sertifikat' => "CERT-{$p->id}-{$eventTraining->id}",
                    'tanggal_terbit' => now(),
                    'tanggal_expired' => now()->addYears(2),
                ]
            );
        }

        return back()->with('success', 'Sertifikat berhasil dibuat.');
    }

    /* ================== SHOW EVENT ================== */
public function show(EventTraining $eventTraining)
{
    $this->authorize('view', $eventTraining);

    $eventTraining->load([
        'training',
        'participants.certificates',
        'participants' => function ($q) {
            $q->orderBy('nama');
        },
        'staff'
    ]);

    return view('event_training.show', [
        'event' => $eventTraining
    ]);
}


    /* ================== LAPORAN ================== */
    public function laporan()
{
    $events = EventTraining::with(['training', 'participants'])
        ->where('status', 'done')
        ->orderBy('tanggal_end', 'DESC')
        ->get()
        ->map(function ($event) {

            // TOTAL TAGIHAN
            $totalTagihan = $event->jenis_event === 'inhouse'
                ? $event->harga_paket
                : $event->participants->sum(fn ($p) => $p->pivot->harga_peserta);

            // TOTAL LUNAS
            $totalLunas = $event->participants
                ->where('pivot.is_paid', true)
                ->sum(fn ($p) => $p->pivot->harga_peserta);

            return (object) [
                'event'          => $event,
                'jenis_event'    => strtoupper($event->jenis_event),
                'status_event'   => $event->status,
                'total_peserta'  => $event->participants->count(),
                'total_tagihan'  => $totalTagihan,
                'total_lunas'    => $totalLunas,
                'finance_ok'     => (bool) $event->finance_approved,
            ];
        });

    return view('laporan.index', compact('events'));
}

    public function syncFinance(EventTraining $event)
{
    if (
        $event->jenis_event === 'reguler' &&
        $event->status === 'done' &&
        ! $event->finance_approved &&
        $event->participants->every(fn ($p) => $p->pivot->is_paid)
    ) {
        $event->update([
            'finance_approved' => true,
            'finance_approved_at' => now(),
        ]);
    }

    return back()->with('success', 'Finance berhasil disinkronkan.');
}
}
