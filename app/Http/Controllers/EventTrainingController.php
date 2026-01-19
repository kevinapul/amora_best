<?php

namespace App\Http\Controllers;

use App\Models\EventTraining;
use App\Models\EventTrainingGroup;
use App\Models\MasterTraining;
use App\Models\Participant;
use App\Models\Certificate;
use App\Models\Training;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventTrainingController extends Controller
{
    /* =====================================================
     * LIST + SEARCH + MODE + PENDING
     * ===================================================== */
   public function index(Request $request)
{
    $this->authorize('viewAny', EventTraining::class);

    $search = $request->search;

    $base = EventTrainingGroup::with([
        'masterTraining',
        'events'
    ])->when($search, function ($q) use ($search) {
        $q->where('job_number', 'like', "%{$search}%")
          ->orWhereHas('masterTraining', fn ($m) =>
              $m->where('nama_training', 'like', "%{$search}%")
          );
    });

    // ================= AKTIF =================
    $groupsActive = (clone $base)
        ->whereDoesntHave('events', fn ($q) =>
            $q->where('status', 'pending')
        )
        ->latest()
        ->paginate(10, ['*'], 'active');

    // ================= PENDING =================
    $groupsPending = auth()->user()->can('approve', EventTraining::class)
        ? (clone $base)
            ->whereHas('events', fn ($q) =>
                $q->where('status', 'pending')
            )
            ->latest()
            ->paginate(10, ['*'], 'pending')
        : null;

    return view('event_training.index', compact(
        'groupsActive',
        'groupsPending',
        'search'
    ));
}


    /* ================== CREATE ================== */
    public function create()
    {
        $this->authorize('create', EventTraining::class);

        return view('event_training.create', [
            'masters' => MasterTraining::with('trainings')
                ->orderBy('nama_training')
                ->get()
        ]);
    }


    /* =====================================================
     * STORE (GROUP = DATA SAMA)
     * ===================================================== */
public function store(Request $request)
{
    $this->authorize('create', EventTraining::class);

    $request->validate([
        // GROUP
        'master_training_id' => 'required|exists:master_trainings,id',
        'job_number'         => 'nullable|string|unique:event_training_groups,job_number',
        'tempat'             => 'nullable|string',
        'jenis_sertifikasi'  => 'nullable|string',
        'sertifikasi'        => 'nullable|string',

        // EVENTS
        'events'               => 'required|array|min:1',
        'events.*.training_id' => 'required|exists:trainings,id',
        'events.*.jenis_event' => 'required|in:training,non_training',

        'events.*.start_day'   => 'required|integer|min:1|max:31',
        'events.*.start_month' => 'required|string',
        'events.*.start_year'  => 'required|integer|min:' . date('Y'),

        'events.*.end_day'     => 'nullable|integer|min:1|max:31',
        'events.*.end_month'   => 'nullable|string',
        'events.*.end_year'    => 'nullable|integer',
    ]);

    DB::transaction(function () use ($request) {

        $master = MasterTraining::findOrFail($request->master_training_id);

        /* ================= GROUP ================= */
        $group = EventTrainingGroup::create([
            'master_training_id' => $master->id,
            'nama_group'         => $master->nama_training,
            'job_number'         => $request->job_number,
            'tempat'             => $request->tempat,
            'jenis_sertifikasi'  => $request->jenis_sertifikasi,
            'sertifikasi'        => $request->sertifikasi,
        ]);

        /* ================= EVENTS ================= */
        foreach ($request->events as $event) {

            // VALIDASI KHUSUS NON TRAINING
            if (
                $event['jenis_event'] === 'non_training' &&
                empty($event['non_training_type'])
            ) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'events' => 'Jenis non training wajib dipilih jika event non training.'
                ]);
            }

            $bulan = [
                'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MEI'=>5,'JUN'=>6,
                'JUL'=>7,'AGU'=>8,'SEP'=>9,'OKT'=>10,'NOV'=>11,'DES'=>12
            ];

            $tanggalStart = Carbon::create(
                $event['start_year'],
                $bulan[$event['start_month']],
                $event['start_day']
            );

            $tanggalEnd = null;
            if (!empty($event['end_day'])) {
                $tanggalEnd = Carbon::create(
                    $event['end_year'],
                    $bulan[$event['end_month']],
                    $event['end_day']
                );
            }

            EventTraining::create([
                'event_training_group_id' => $group->id,
                'training_id'             => $event['training_id'],
                'jenis_event'             => $event['jenis_event'],
                'non_training_type'       =>
                    $event['jenis_event'] === 'non_training'
                        ? $event['non_training_type']
                        : null,
                'tanggal_start'           => $tanggalStart,
                'tanggal_end'             => $tanggalEnd,
                'status'                  => 'pending',
            ]);
        }
    });

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
    $eventTraining->load([
        'training',
        'eventTrainingGroup.masterTraining',
        'eventTrainingGroup.events',
        'participants'
    ]);

    return view('event_training.show', [
        'event' => $eventTraining,
        'group' => $eventTraining->eventTrainingGroup, // ⬅️ INI KUNCI
    ]);
}



    public function edit(EventTraining $eventTraining)
{
    $this->authorize('update', $eventTraining);

    return view('event_training.edit', [
        'event' => $eventTraining,
    ]);
}

public function update(Request $request, EventTraining $eventTraining)
{
    $this->authorize('update', $eventTraining);

    $request->validate([
        'start_day'   => 'required|integer|min:1|max:31',
        'start_month' => 'required|integer|min:1|max:12',
        'start_year'  => 'required|integer',

        'end_day'   => 'nullable|integer|min:1|max:31',
        'end_month' => 'nullable|integer|min:1|max:12',
        'end_year'  => 'nullable|integer',
    ]);

    $eventTraining->update([
        'tanggal_start' => now()->setDate(
            $request->start_year,
            $request->start_month,
            $request->start_day
        ),
        'tanggal_end' => $request->end_day
            ? now()->setDate(
                $request->end_year,
                $request->end_month,
                $request->end_day
            )
            : null,
    ]);

    return back()->with('success', 'Tanggal event berhasil diperbarui');
}
public function laporan()
{
    $groups = EventTrainingGroup::with([
        'events.training',
        'events.participants'
    ])
    ->latest()
    ->get()
    ->map(function ($group) {

        $participants = $group->events
            ->flatMap(fn ($e) => $e->participants);

        // TAGIHAN
        $totalTagihan = $group->isInhouse()
            ? $group->harga_paket
            : $participants->sum(fn ($p) => $p->pivot->harga_peserta);

        // LUNAS
        $totalLunas = $participants
            ->where('pivot.is_paid', true)
            ->sum(fn ($p) => $p->pivot->harga_peserta);

        return (object) [
            'group'          => $group,
            'job_number'     => $group->job_number,
            'nama_group'     => $group->nama_group,
            'training_type'  => strtoupper($group->training_type),

            'total_event'    => $group->events->count(),
            'total_peserta'  => $participants->count(),

            'total_tagihan'  => $totalTagihan,
            'total_lunas'    => $totalLunas,

            'finance_ok'     => $group->isFinanceApproved(),
        ];
    });

    return view('laporan.index', compact('groups'));
}
/* =====================================================
 * D. CERTIFICATE DASHBOARD
 * ===================================================== */
public function certificateDashboard()
{
    $groups = EventTrainingGroup::with([
        'masterTraining',
        'events.training',
        'events.participants.certificates'
    ])
    ->whereHas('events', fn ($q) =>
        $q->where('status', 'done')
    )
    ->orderByDesc('created_at')
    ->get()
    ->map(function ($group) {

        $events = $group->events->where('status', 'done');

        $participants = $events
            ->flatMap(fn ($e) => $e->participants);

        $sertifikatSiap = $participants
            ->where('pivot.certificate_ready', true)
            ->count();

        return (object) [
            'group' => $group,

            'job_number' => $group->job_number,
            'nama_group' => $group->nama_group,
            'training_type' => strtoupper($group->training_type),

            'total_event' => $events->count(),
            'total_peserta' => $participants->count(),

            'sertifikat_siap' => $sertifikatSiap,
            'sertifikat_pending' =>
                $participants->count() - $sertifikatSiap,
        ];
    });

    return view('division.training.certificate_dashboard', compact('groups'));
}
public function modal(EventTraining $eventTraining)
{
    $this->authorize('view', $eventTraining);

    $eventTraining->load([
        'training',
        'eventTrainingGroup',
        'participants.certificates',
        'staff'
    ]);

    // ⚠️ RETURN PARTIAL, BUKAN show.blade.php
    return view('event_training._detail', [
        'event' => $eventTraining
    ]);
}

}
