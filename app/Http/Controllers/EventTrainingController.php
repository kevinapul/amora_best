<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\Participant;
use App\Models\Certificate;
use App\Models\Training;
use Illuminate\Http\Request;
use Carbon\Carbon;

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

        return redirect()->route('event-training.index')
            ->with('success', 'Event training berhasil ditambahkan (Pending ACC).');
    }

    /* ================== APPROVAL & FINANCE ================== */
    public function approve(EventTraining $eventTraining)
    {
        $this->authorize('approve', $eventTraining);
        $eventTraining->update(['status' => 'active']);
        $eventTraining->refreshStatus();

        return back()->with('success', 'Event berhasil di-ACC.');
    }

    public function approveFinance(EventTraining $eventTraining)
    {
        $this->authorize('approveFinance', $eventTraining);

        abort_if($eventTraining->status !== 'done', 403);
        abort_if($eventTraining->finance_approved, 403);

        if ($eventTraining->isInhouse()) {
            foreach ($eventTraining->participants as $p) {
                $p->pivot->markAsPaid();
            }
        }

        if ($eventTraining->isReguler()) {
            abort_if(
                ! $eventTraining->participants->every(fn ($p) => $p->pivot->is_paid),
                403,
                'Masih ada peserta yang belum lunas.'
            );
        }

        $eventTraining->approveFinance();

        return back()->with('success', 'Finance berhasil di-ACC.');
    }

    /* ================== SHOW EVENT ================== */
    public function show(EventTraining $eventTraining)
    {
        $this->authorize('view', $eventTraining);

        $eventTraining->load([
            'training',
            'participants.certificates',
            'participants' => fn ($q) => $q->orderBy('nama'),
            'staff'
        ]);

        return view('event_training.show', [
            'event' => $eventTraining
        ]);
    }

    /* ================== CERTIFICATE (INPUT DATA) ================== */
    public function recordCertificate(
        Request $request,
        EventTraining $event,
        Participant $participant
    ) {
        abort_if(! $event->canInputCertificate(), 403);

        $request->validate([
            'nomor_sertifikat' => 'required|string|max:100',
            'tanggal_terbit'   => 'required|date',
        ]);

        $pivot = $event->participants()
            ->where('participants.id', $participant->id)
            ->first()
            ?->pivot;

        abort_if(! $pivot, 404);

        if ($event->isReguler() && ! $pivot->isPaid()) {
            abort(403, 'Peserta belum lunas');
        }

        $years = $event->certificateValidityYears();
        $expired = $years
            ? Carbon::parse($request->tanggal_terbit)->addYears($years)
            : null;

        Certificate::updateOrCreate(
            [
                'event_training_id' => $event->id,
                'participant_id'    => $participant->id,
            ],
            [
                'nomor_sertifikat' => $request->nomor_sertifikat,
                'tanggal_terbit'   => $request->tanggal_terbit,
                'tanggal_expired'  => $expired,
            ]
        );

        $pivot->markCertificateReady();

        return back()->with('success', 'Sertifikat berhasil dicatat.');
    }

    /* ================== DIVISION TRAINING ================== */
    public function certificateDashboard()
    {
        $events = EventTraining::with([
            'training',
            'participants.certificates'
        ])
        ->where('status', 'done')
        ->orderBy('tanggal_end', 'DESC')
        ->get();

        return view('division.training.index', compact('events'));
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
            $totalTagihan = $event->isInhouse()
                ? $event->harga_paket
                : $event->participants->sum(
                    fn ($p) => $p->pivot->harga_peserta
                );

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
}
