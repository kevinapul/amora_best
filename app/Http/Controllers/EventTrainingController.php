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
use App\Models\EventStaff;
use App\Models\Company;
use App\Helpers\CompanyResolver;
use App\Models\Invoice;


class EventTrainingController extends Controller
{
    /* =====================================================
     * LIST + SEARCH + MODE + PENDING
     * ===================================================== */
    public function index(Request $request)
    {
        $this->authorize('viewAny', EventTraining::class);

        $search = $request->search;
        $month  = $request->month ?? now()->format('Y-m');

        [$year,$mon] = explode('-', $month);

        $base = EventTrainingGroup::with([
            'masterTraining',
            'events'
        ])
        ->when($search, function ($q) use ($search) {
            $q->where('job_number', 'like', "%{$search}%")
            ->orWhereHas('masterTraining', fn ($m) =>
                $m->where('nama_training', 'like', "%{$search}%")
            );
        });

        /* ================= ACTIVE ================= */
        $groupsActive = (clone $base)
            ->whereHas('events', function($q) use ($year,$mon){
                $q->whereYear('tanggal_start',$year)
                ->whereMonth('tanggal_start',$mon);
            })
            ->whereDoesntHave('events', fn ($q) =>
                $q->where('status','pending')
            )
            ->latest()
            ->paginate(10,['*'],'active')
            ->withQueryString();

        /* ================= PENDING ================= */
        $groupsPending = auth()->user()->can('approve', EventTraining::class)
            ? (clone $base)
                ->whereHas('events', fn ($q) =>
                    $q->where('status','pending')
                )
                ->latest()
                ->paginate(10,['*'],'pending')
                ->withQueryString()
            : null;

        return view('event_training.index',[
            'groupsActive'=>$groupsActive,
            'groupsPending'=>$groupsPending,
            'search'=>$search,
            'month'=>$month
        ]);
    }


        /* ================== CREATE ================== */
        public function create()
    {
        $this->authorize('create', EventTraining::class);

        return view('event_training.create', [
            'masters' => MasterTraining::with('trainings')
                ->orderBy('nama_training')
                ->get(),

            'companies' => Company::orderBy('name')->get(),
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

            'training_type' => 'required|in:reguler,inhouse',
            'harga_paket'   => 'nullable|required_if:training_type,inhouse|numeric|min:0',

            // COMPANY (FLEX)
            'billing_company_id' => 'nullable',
            'manual_company'     => 'nullable|string|max:255',

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

            /* ================= COMPANY RESOLVER ================= */
            $billingCompanyId = null;

            if ($request->training_type === 'inhouse') {

                // jika pilih manual
                if ($request->billing_company_id === 'manual') {

                    $company = \App\Helpers\CompanyResolver::resolve(
                        $request->manual_company
                    );

                    $billingCompanyId = $company?->id;

                } else {
                    $billingCompanyId = $request->billing_company_id;
                }
            }

            /* ================= CREATE GROUP ================= */
            $group = EventTrainingGroup::create([
                'master_training_id' => $master->id,
                'nama_group'         => $master->nama_training,
                'job_number'         => $request->job_number,
                'tempat'             => $request->tempat,
                'jenis_sertifikasi'  => $request->jenis_sertifikasi,
                'sertifikasi'        => $request->sertifikasi,
                'training_type'      => $request->training_type,

                'harga_paket' => $request->training_type === 'inhouse'
                    ? $request->harga_paket
                    : null,

                'billing_company_id' => $billingCompanyId,
            ]);

            /* ================= EVENTS ================= */
            foreach ($request->events as $event) {

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
    public function approve(EventTraining $event)
    {
        $this->authorize('approve', $event);

        abort_if($event->status !== 'pending', 403);

        $event->update([
            'status' => 'active'
        ]);

        return back()->with('success', 'Event berhasil di-ACC marketing');
    }



        /* ================== FINANCE ================== */
    public function approveFinance(EventTraining $eventTraining)
    {
        $this->authorize('approveFinance', $eventTraining);

        $group = $eventTraining->eventTrainingGroup;

        abort_if(! $group->isFullyPaid(), 403, 'Belum lunas');

        $group->update([
            'finance_approved'    => true,
            'finance_approved_at' => now(),
        ]);

        foreach ($group->events as $event) {
            $event->updateQuietly([
                'finance_approved'    => true,
                'finance_approved_at' => now(),
            ]);
        }

        return back()->with('success', 'Finance berhasil di-ACC');
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

        $staffs = EventStaff::where('event_training_id', $eventTraining->id)
            ->get()
            ->groupBy('role')
            ->map(fn ($items) => $items->pluck('name')->implode(', '));

        return view('event_training.show', [
            'event'  => $eventTraining,
            'group'  => $eventTraining->eventTrainingGroup,
            'staffs' => $staffs, 
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

        abort_if($eventTraining->status !== 'pending', 403, 'Event sudah tidak bisa diedit.');

        $request->validate([
            'jenis_event' => 'required|in:training,non_training',

            'start_day'   => 'required|integer|min:1|max:31',
            'start_month' => 'required|integer|min:1|max:12',
            'start_year'  => 'required|integer',

            'end_day'   => 'nullable|integer|min:1|max:31',
            'end_month' => 'nullable|integer|min:1|max:12',
            'end_year'  => 'nullable|integer',
        ]);

        DB::transaction(function () use ($request, $eventTraining) {

            $eventTraining->update([
                'jenis_event'       => $request->jenis_event,
                'non_training_type' => $request->non_training_type,

                'tanggal_start' => Carbon::create(
                    $request->start_year,
                    $request->start_month,
                    $request->start_day
                ),

                'tanggal_end' => $request->end_day
                    ? Carbon::create(
                        $request->end_year,
                        $request->end_month,
                        $request->end_day
                    )
                    : null,
            ]);
        });

        return redirect()
            ->route('event-training.show', $eventTraining)
            ->with('success', 'Event berhasil diperbarui');
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
    public function certificateAdmin(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        [$year, $mon] = explode('-', $month);

        $groups = EventTrainingGroup::with([
            'masterTraining',
            'events.training',
            'events.participants.certificates'
        ])
        ->whereHas('events', function ($q) use ($year, $mon) {
            $q->whereYear('tanggal_start', $year)
            ->whereMonth('tanggal_start', $mon)
            ->where('status', 'done');
        })
        ->latest()
        ->paginate(5)
        ->withQueryString();

        $groups->getCollection()->transform(function ($group) {

            $events = $group->events->where('status','done');

            $participants = $events
                ->flatMap(fn ($e) => $e->participants)
                ->unique('id');

            $totalPeserta = $participants->count();

            $sertifikatSiap = $participants
                ->filter(function ($p) use ($events) {
                    return $p->certificates
                        ->whereIn('event_training_id', $events->pluck('id'))
                        ->isNotEmpty();
                })
                ->count();

            $group->total_peserta = $totalPeserta;
            $group->sertifikat_siap = $sertifikatSiap;

            return $group;
        });

        return view('division.training.admin_index', compact('groups', 'month'));
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


        return view('event_training._detail', [
            'event' => $eventTraining
        ]);
    }


    /**
     * @deprecated
     * DO NOT USE.
     * Replaced by Invoice payment flow.
     */
    public function bulkPayment(Request $request, EventTraining $eventTraining)
    {
        $this->authorize('bulkPayment', $eventTraining);

        abort_if($eventTraining->status !== 'done', 403);
        abort_if($eventTraining->finance_approved, 403);

        $request->merge([
            'amount' => preg_replace('/\D/', '', $request->amount),
        ]);

        $rules = [
            'amount' => 'required|numeric|min:1',
        ];

        if ($eventTraining->isReguler()) {
            $rules['company'] = 'required|string';
            $rules['participants'] = 'nullable|array';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($eventTraining, $validated) {

            // ================= INHOUSE =================
            if ($eventTraining->isInhouse()) {

                $group = $eventTraining->eventTrainingGroup;

                $group->addPayment($validated['amount']);

                return;
            }

            // ================= REGULER =================
            if ($validated['company'] === 'INDIVIDU') {

                abort_if(
                    empty($validated['participants']),
                    422,
                    'Pilih peserta individu'
                );

                foreach ($validated['participants'] as $pid) {
                    $pivot = $eventTraining->participants()
                        ->where('participant_id', $pid)
                        ->firstOrFail()
                        ->pivot;

                    if ($pivot->remaining_amount > 0) {
                        $pivot->pay($pivot->remaining_amount);
                    }
                }

            } else {
                $eventTraining->bulkPay(
                    $validated['company'],
                    $validated['amount']
                );
            }
        });

        return back()->with('success', 'Pembayaran berhasil diproses');
    }



    public function finance(EventTraining $eventTraining)
    {
        $this->authorize('approveFinance', $eventTraining);

        abort_if($eventTraining->status !== 'done', 403);
        abort_if($eventTraining->finance_approved, 403);

        $eventTraining->load('participants');

        $companies = $eventTraining->participants
            ->groupBy(fn ($p) => $p->perusahaan ?? 'INDIVIDU');

        return view('event_training.finance', [
            'event'     => $eventTraining,
            'companies' => $companies,
        ]);
    }

    public function certificateGroup(EventTrainingGroup $group, Request $request)
    {
        $group->load([
            'events.training',
            'events.participants.certificates',
            'events.participants'
        ]);

        $companies = Company::whereIn('id', function ($q) use ($group) {
            $q->select('company_id')
            ->from('event_participants')
            ->join('event_trainings','event_trainings.id','=','event_participants.event_training_id')
            ->where('event_trainings.event_training_group_id',$group->id)
            ->whereNotNull('company_id');
        })->get();

        $selectedCompany = $request->company_id;

        $invoice = null;
        if($selectedCompany){
            $invoice = Invoice::where('company_id',$selectedCompany)
                ->where('master_training_id',$group->master_training_id)
                ->latest()
                ->first();
        }

        return view('division.training.admin_group',[
            'group'=>$group,
            'companies'=>$companies,
            'invoice'=>$invoice,
            'selectedCompany'=>$selectedCompany
        ]);
    }


}
