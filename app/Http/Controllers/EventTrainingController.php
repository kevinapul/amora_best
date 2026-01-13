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
    $mode   = $request->get('mode', 'training');

    $baseQuery = EventTraining::query();

    /* ================= FILTER MODE ================= */

    if ($mode === 'training') {
        $baseQuery->where('jenis_event', 'training')
                  ->with('training')
                  ->withCount('participants');
    }

    if ($mode === 'resertifikasi') {
        $baseQuery->where('jenis_event', 'non_training')
                  ->where('non_training_type', 'resertifikasi')
                  ->with('training');
    }

    if ($mode === 'perpanjangan') {
        $baseQuery->where('jenis_event', 'non_training')
                  ->where('non_training_type', 'perpanjangan');
    }

    /* ================= SEARCH ================= */

    $baseQuery->when($search, function ($q) use ($search) {
        $q->where(function ($sub) use ($search) {
            $sub->where('job_number', 'like', "%{$search}%")
                ->orWhereHas('training', fn ($t) =>
                    $t->where('name', 'like', "%{$search}%")
                );
        });
    });

    /* ================= ACTIVE ================= */

    $eventsActive = (clone $baseQuery)
        ->whereIn('status', ['active', 'on_progress', 'done'])
        ->orderByDesc('tanggal_start')
        ->paginate(10)
        ->withQueryString();

    foreach ($eventsActive as $event) {
        $event->refreshStatus();
    }

    /* ================= PENDING ================= */

    $eventsPending = auth()->user()->can('viewPending', EventTraining::class)
    ? EventTraining::where('status', 'pending')
        ->latest()
        ->get()
    : null;

    return view('event_training.index', compact(
        'eventsActive',
        'eventsPending',
        'search',
        'mode'
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

    /* ================= VALIDATION ================= */
    $request->validate([
        'jenis_event' => 'required|in:training,non_training',

        // TRAINING
        'training_id'   => 'nullable|exists:trainings,id',
        'training_type' => 'nullable|in:reguler,inhouse',
        'harga_paket'   => 'nullable|numeric|min:0',

        // NON TRAINING
        'non_training_type' => 'nullable|in:perpanjangan,resertifikasi',

        // UMUM
        'job_number' => 'nullable|string|unique:event_trainings,job_number',

        // tanggal WAJIB kecuali perpanjangan
        'start_day'   => 'required_unless:non_training_type,perpanjangan|integer|min:1|max:31',
        'start_month' => 'required_unless:non_training_type,perpanjangan|integer|min:1|max:12',
        'start_year'  => 'required_unless:non_training_type,perpanjangan|integer|min:' . date('Y'),

        'end_day'   => 'nullable|integer|min:1|max:31',
        'end_month' => 'nullable|integer|min:1|max:12',
        'end_year'  => 'nullable|integer|min:' . date('Y'),

        'tempat'            => 'nullable|string',
        'jenis_sertifikasi' => 'nullable|string',
        'sertifikasi'       => 'nullable|string',
    ]);

    /* ================= BUILD DATE ================= */
    $tanggalStart = null;
    $tanggalEnd   = null;

    if ($request->non_training_type === 'perpanjangan') {
        // 1 hari, start = end
        $tanggalStart = Carbon::create(
            $request->start_year,
            $request->start_month,
            $request->start_day
        );
        $tanggalEnd = $tanggalStart->copy();
    } else {
        $tanggalStart = Carbon::create(
            $request->start_year,
            $request->start_month,
            $request->start_day
        );

        if ($request->end_day && $request->end_month && $request->end_year) {
            $tanggalEnd = Carbon::create(
                $request->end_year,
                $request->end_month,
                $request->end_day
            );
        }
    }

    /* ================= BASE DATA ================= */
    $data = [
        'jenis_event'        => $request->jenis_event,
        'job_number'         => $request->job_number,
        'tanggal_start'      => $tanggalStart,
        'tanggal_end'        => $tanggalEnd,
        'tempat'             => $request->tempat,
        'jenis_sertifikasi'  => $request->jenis_sertifikasi,
        'sertifikasi'        => $request->sertifikasi,
        'status'             => 'pending',
    ];

    /* ================= TRAINING ================= */
    if ($request->jenis_event === 'training') {

        if (! $request->training_id) {
            abort(422, 'Training wajib dipilih');
        }

        $data += [
            'training_id'       => $request->training_id,
            'training_type'     => $request->training_type,
            'harga_paket'       => $request->training_type === 'inhouse'
                ? $request->harga_paket
                : null,
            'non_training_type' => null,
        ];
    }

    /* ================= NON TRAINING ================= */
    if ($request->jenis_event === 'non_training') {

        if (! $request->non_training_type) {
            abort(422, 'Jenis non training wajib dipilih');
        }

        $data += [
            'training_type'     => null,
            'harga_paket'       => null,
            'non_training_type' => $request->non_training_type,
        ];

        // PERPANJANGAN → event administratif
        if ($request->non_training_type === 'perpanjangan') {
            $data += [
                'training_id' => null,
                'status'      => 'completed', // status admin
            ];
        }

        // RESERTIFIKASI → tetap pakai training
        if ($request->non_training_type === 'resertifikasi') {

            if (! $request->training_id) {
                abort(422, 'Resertifikasi wajib memilih training');
            }

            $data += [
                'training_id'       => $request->training_id,
                'jenis_sertifikasi' => 'BNSP',
            ];
        }
    }

    /* ================= SAVE ================= */
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
                : $event->participants->sum(fn ($p) => $p->pivot->harga_peserta);

            // TOTAL LUNAS
            $totalLunas = $event->participants
                ->where('pivot.is_paid', true)
                ->sum(fn ($p) => $p->pivot->harga_peserta);

            return (object) [
                'event'         => $event,
                'jenis_event'   => strtoupper($event->jenis_event),
                'status_event'  => $event->status,
                'total_peserta' => $event->participants->count(),
                'total_tagihan' => $totalTagihan,
                'total_lunas'   => $totalLunas,
                'finance_ok'    => (bool) $event->finance_approved,
            ];
        });

    return view('laporan.index', compact('events'));
}

/* ================== DELETE EVENT ================== */
public function destroy(EventTraining $eventTraining)
{
    $this->authorize('delete', $eventTraining);

    // Hapus event beserta relasi sertifikat (opsional)
    $eventTraining->participants()->detach(); // lepaskan pivot
    Certificate::where('event_training_id', $eventTraining->id)->delete(); // hapus sertifikat
    $eventTraining->delete();

    return redirect()
        ->route('event-training.index')
        ->with('success', 'Event berhasil dihapus.');
}

public function update(Request $request, EventTraining $eventTraining)
{
    $this->authorize('update', $eventTraining);

    $request->validate([
        'jenis_event' => 'required|in:training,non_training',

        'training_id'   => 'nullable|exists:trainings,id',
        'training_type' => 'nullable|in:reguler,inhouse',
        'harga_paket'   => 'nullable|numeric|min:0',

        'non_training_type' => 'nullable|in:perpanjangan,resertifikasi',

        'job_number' => 'nullable|string|unique:event_trainings,job_number,' . $eventTraining->id,
        'start_day'  => 'required|integer|min:1|max:31',
        'start_month'=> 'required|integer|min:1|max:12',
        'start_year' => 'required|integer',
        'end_day'    => 'nullable|integer|min:1|max:31',
        'end_month'  => 'nullable|integer|min:1|max:12',
        'end_year'   => 'nullable|integer',

        'tempat'            => 'nullable|string',
        'jenis_sertifikasi' => 'nullable|string',
        'sertifikasi'       => 'nullable|string',
    ]);

    $tanggalStart = Carbon::create(
        $request->start_year,
        $request->start_month,
        $request->start_day
    );

    $tanggalEnd = null;
    if ($request->end_day && $request->end_month && $request->end_year) {
        $tanggalEnd = Carbon::create(
            $request->end_year,
            $request->end_month,
            $request->end_day
        );
    }

    $data = [
        'jenis_event'       => $request->jenis_event,
        'job_number'        => $request->job_number,
        'tanggal_start'     => $tanggalStart,
        'tanggal_end'       => $tanggalEnd,
        'tempat'            => $request->tempat,
        'jenis_sertifikasi' => $request->jenis_sertifikasi,
        'sertifikasi'       => $request->sertifikasi,
    ];

    /* ===== TRAINING ===== */
    if ($request->jenis_event === 'training') {
        if (! $request->training_id) {
            abort(422, 'Training wajib dipilih');
        }

        $data += [
            'training_id'        => $request->training_id,
            'training_type'      => $request->training_type,
            'harga_paket'        => $request->training_type === 'inhouse'
                ? $request->harga_paket
                : null,
            'non_training_type'  => null,
        ];
    }

    /* ===== NON TRAINING ===== */
    if ($request->jenis_event === 'non_training') {
        if (! $request->non_training_type) {
            abort(422, 'Jenis non training wajib dipilih');
        }

        $data += [
            'training_type' => null,
            'harga_paket'   => null,
            'non_training_type' => $request->non_training_type,
        ];

        if ($request->non_training_type === 'perpanjangan') {
            $data['training_id'] = null;
        }

        if ($request->non_training_type === 'resertifikasi') {
            if (! $request->training_id) {
                abort(422, 'Resertifikasi wajib memilih training');
            }

            $data += [
                'training_id' => $request->training_id,
                'jenis_sertifikasi' => 'BNSP',
            ];
        }
    }

    $eventTraining->update($data);

    return redirect()
        ->route('event-training.index')
        ->with('success', 'Event berhasil diperbarui');
}

public function edit(EventTraining $eventTraining)
{
    $this->authorize('update', $eventTraining);

    return view('event_training.edit', [
        'event'     => $eventTraining,
        'trainings' => Training::orderBy('code')->get(),

        // pecah tanggal
        'start' => [
            'day'   => optional($eventTraining->tanggal_start)->day,
            'month' => optional($eventTraining->tanggal_start)->month,
            'year'  => optional($eventTraining->tanggal_start)->year,
        ],
        'end' => [
            'day'   => optional($eventTraining->tanggal_end)->day,
            'month' => optional($eventTraining->tanggal_end)->month,
            'year'  => optional($eventTraining->tanggal_end)->year,
        ],
    ]);
}

}
