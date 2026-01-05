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
    /* ================== LIST ================== */
    public function index(Request $request)
    {
        $this->authorize('viewAny', EventTraining::class);

        $search = $request->search;

        $eventsActive = EventTraining::with('training')
            ->withCount('participants')
            ->whereIn('status', ['active', 'on_progress', 'done'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->whereHas('training', fn ($t) =>
                        $t->where('name', 'like', "%{$search}%")
                    )->orWhere('job_number', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('tanggal_start')
            ->paginate(10)
            ->withQueryString();

        foreach ($eventsActive as $event) {
            $event->refreshStatus();
        }

        $eventsPending = EventTraining::with('training')
            ->withCount('participants')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('event_training.index', compact(
            'eventsActive',
            'eventsPending',
            'search'
        ));
    }

    /* ================== CREATE ================== */
    public function create()
    {
        $this->authorize('create', EventTraining::class);

        return view('event_training.create', [
            'trainings' => Training::orderBy('code')->get()
        ]);
    }

    /* ================== STORE ================== */
public function store(Request $request)
{
    $this->authorize('create', EventTraining::class);

    // validasi
    $request->validate([
        'jenis_event' => 'required|in:training,non_training',
        'training_id'   => 'required_if:jenis_event,training|exists:trainings,id',
        'training_type' => 'required_if:jenis_event,training|in:reguler,inhouse',
        'harga_paket'   => 'nullable|required_if:training_type,inhouse|numeric',
        'non_training_type' => 'nullable|required_if:jenis_event,non_training|in:perpanjangan,resertifikasi',
        'job_number' => 'nullable|string|unique:event_trainings,job_number',
        'start_day'   => 'required|integer|min:1|max:31',
        'start_month' => 'required|integer|min:1|max:12',
        'start_year'  => 'required|integer|min:'.date('Y').'|max:'.(date('Y')+3),
        'end_day'     => 'required|integer|min:1|max:31',
        'end_month'   => 'required|integer|min:1|max:12',
        'end_year'    => 'required|integer|min:'.date('Y').'|max:'.(date('Y')+3),
        'tempat' => 'nullable|string',
        'jenis_sertifikasi' => 'nullable|string',
        'sertifikasi' => 'nullable|string',
    ]);

    // gabungkan dropdown jadi tanggal Carbon
    $tanggal_start = Carbon::create(
        $request->start_year,
        $request->start_month,
        $request->start_day
    )->startOfDay();

    $tanggal_end = Carbon::create(
        $request->end_year,
        $request->end_month,
        $request->end_day
    )->endOfDay();

    $data = [
        'jenis_event' => $request->jenis_event,
        'job_number'  => $request->job_number,
        'tanggal_start' => $tanggal_start,
        'tanggal_end'   => $tanggal_end,
        'tempat' => $request->tempat,
        'status' => 'pending',
    ];

    /* ===== TRAINING ===== */
    if ($request->jenis_event === 'training') {
        $data += [
            'training_id'   => $request->training_id,
            'training_type' => $request->training_type,
            'harga_paket'   => $request->training_type === 'inhouse'
                                ? $request->harga_paket
                                : null,
            'non_training_type' => null,
        ];
    }

    /* ===== NON TRAINING ===== */
    if ($request->jenis_event === 'non_training') {
        $data += [
            'training_id' => null,
            'training_type' => null,
            'harga_paket' => null,
            'non_training_type' => $request->non_training_type,
        ];

        if ($request->non_training_type === 'perpanjangan') {
            $data['status'] = 'done';
        }

        if ($request->non_training_type === 'resertifikasi') {
            $data['jenis_sertifikasi'] = 'Bnsp';
        }
    }

    EventTraining::create($data);

    return redirect()
        ->route('event-training.index')
        ->with('success', 'Event berhasil dibuat');
}

    /* ================== APPROVAL ================== */
    public function approve(EventTraining $eventTraining)
    {
        $this->authorize('approve', $eventTraining);

        abort_if($eventTraining->status !== 'pending', 403);

        $eventTraining->update(['status' => 'active']);
        $eventTraining->refreshStatus();

        return back()->with('success', 'Event di-ACC');
    }

    /* ================== FINANCE ================== */
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
                'Masih ada peserta belum lunas'
            );
        }

        $eventTraining->approveFinance();

        return back()->with('success', 'Finance di-ACC');
    }

    /* ================== SHOW ================== */
    public function show(EventTraining $eventTraining)
    {
        $this->authorize('view', $eventTraining);

        $eventTraining->load([
            'training',
            'participants.certificates',
            'staff'
        ]);

        return view('event_training.show', [
            'event' => $eventTraining
        ]);
    }

    /* ================== CERTIFICATE ================== */
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

        if ($event->isTraining() && $event->isReguler() && ! $pivot->isPaid()) {
            abort(403, 'Peserta belum lunas');
        }

        $expired = $event->certificateValidityYears()
            ? Carbon::parse($request->tanggal_terbit)
                ->addYears($event->certificateValidityYears())
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

        return back()->with('success', 'Sertifikat dicatat');
    }
}
