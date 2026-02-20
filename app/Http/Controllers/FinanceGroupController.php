<?php

namespace App\Http\Controllers;

use App\Models\EventTrainingGroup;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\InvoiceNumberGenerator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceGroupController extends Controller
{
    /* =====================================================
     * SHOW FINANCE GROUP
     * ===================================================== */
    public function show(EventTrainingGroup $group, Request $request)
{
    $this->authorize('approveFinance', $group);

    /**
     * ===============================
     * COMPANY CONTEXT
     * - REGULER  → company dari pivot peserta
     * - INHOUSE  → billing company induk
     * ===============================
     */
    if ($group->isInhouse()) {

        $companies = collect([$group->billingCompany])->filter();

    } else {

        $companies = Company::whereIn('id', function ($q) use ($group) {
            $q->select('event_participants.company_id')
                ->from('event_participants')
                ->join(
                    'event_trainings',
                    'event_trainings.id',
                    '=',
                    'event_participants.event_training_id'
                )
                ->where(
                    'event_trainings.event_training_group_id',
                    $group->id
                )
                ->whereNotNull('event_participants.company_id');
        })->get();
    }

    /**
     * ===============================
     * LOAD INVOICE CONTEXT
     * ===============================
     */
    $invoice = null;

    if ($request->filled('company_id')) {
        $invoice = Invoice::where('company_id', $request->company_id)
            ->where('master_training_id', $group->master_training_id)
            ->whereNotIn('status', ['cancelled'])
            ->latest()
            ->first();
    }

    /**
     * ===============================
     * 🔥 WAJIB LOAD PARTICIPANT + PIVOT
     * ===============================
     */
    $group->load('events.participants');

    return view('finance.group', [
        'group'     => $group,
        'companies' => $companies,
        'invoice'   => $invoice,
    ]);
}


    /* =====================================================
     * OPEN / CREATE INVOICE (SINGLE SOURCE OF TRUTH)
     * ===================================================== */
    public function openInvoice(Request $request, EventTrainingGroup $group)
    {
        $this->authorize('approveFinance', $group);

        /**
         * ===============================
         * KUNCI INHOUSE
         * ===============================
         */
        $group->assertBillingCompany();

        $request->validate([
            'company_id' => 'required|exists:companies,id',
        ]);

        return DB::transaction(function () use ($request, $group) {

            /**
             * ===============================
             * RESOLVE COMPANY
             * ===============================
             */
            $companyId = $group->isInhouse()
                ? $group->billing_company_id
                : (int) $request->company_id;

            // 🔒 BLOCK MANUAL ATTACK
            if ($group->isInhouse() && $request->company_id != $group->billing_company_id) {
                abort(403, 'Invoice INHOUSE hanya untuk perusahaan induk.');
            }

            /**
             * ===============================
             * CARI INVOICE AKTIF
             * ===============================
             */
            $invoice = Invoice::where('company_id', $companyId)
                ->where('master_training_id', $group->master_training_id)
                ->whereNotIn('status', ['cancelled'])
                ->latest()
                ->first();

            /**
             * ===============================
             * JIKA LUNAS → READ ONLY
             * ===============================
             */
            if ($invoice && $invoice->status === 'paid') {
                return redirect()
                    ->route('finance.group.show', [
                        'group'      => $group->id,
                        'company_id' => $companyId,
                    ])
                    ->with('info', 'Invoice sudah lunas dan bersifat read-only.');
            }

            /**
             * ===============================
             * CREATE JIKA BELUM ADA
             * ===============================
             */
            if (! $invoice) {
                $invoice = Invoice::create([
                    'company_id'         => $companyId,
                    'master_training_id' => $group->master_training_id,
                    'invoice_number'     => InvoiceNumberGenerator::generate(),
                    'status'             => 'draft',
                    'issued_at'          => now(),
                ]);
            }

            /**
             * ===============================
             * RESET ITEM (SYNC ULANG)
             * ===============================
             */
            $invoice->items()->delete();

            /**
             * ===============================
             * INVOICE ITEMS
             * ===============================
             */
            $total = 0;
            $order = 1;

            if ($group->isInhouse()) {
                // 🔒 INHOUSE → PAKET
                InvoiceItem::create([
                    'invoice_id'              => $invoice->id,
                    'event_training_group_id' => $group->id,
                    'description'             => 'Paket Training Inhouse',
                    'qty'                     => 1,
                    'price'                   => $group->harga_paket,
                    'subtotal'                => $group->harga_paket,
                    'order'                   => 1,
                ]);

                $total = (float) $group->harga_paket;
            } else {
    // REGULER → PER PESERTA
    $participants = $group->events
        ->flatMap(fn ($e) => $e->participants)
        ->filter(fn ($p) => $p->pivot->company_id == $companyId);

    $event = $group->events->first();

    if ($event && $event->tanggal_start && $event->tanggal_end) {
        $tgl =
            \Carbon\Carbon::parse($event->tanggal_start)->format('d')
            . ' - ' .
            \Carbon\Carbon::parse($event->tanggal_end)->format('d F Y');
    } else {
        $tgl = '-';
    }

    $training = $group->masterTraining->nama_training ?? '-';
    $sertifikasi = $group->jenis_sertifikasi ?? '-';

    foreach ($participants as $participant) {
        $harga = (float) ($participant->pivot->harga_peserta ?? 0);
        if ($harga <= 0) continue;

        $desc = "Training {$training} Sertifikasi {$sertifikasi} {$tgl} an. {$participant->nama}";

        InvoiceItem::create([
            'invoice_id'              => $invoice->id,
            'event_training_group_id' => $group->id,
            'description'             => $desc,
            'qty'                     => 1,
            'price'                   => $harga,
            'subtotal'                => $harga,
            'order'                   => $order++,
        ]);

        $total += $harga;
    }
}

            /**
             * ===============================
             * UPDATE TOTAL
             * ===============================
             */
            $invoice->update([
                'total_amount' => $total,
            ]);

            /**
             * ===============================
             * REDIRECT CONTEXTUAL
             * ===============================
             */
            return redirect()
                ->route('finance.group.show', [
                    'group'      => $group->id,
                    'company_id' => $companyId,
                ])
                ->with('success', 'Invoice siap digunakan.');
        });
    }

    /* =====================================================
 * PAY INVOICE
 * ===================================================== */
public function pay(Request $request, Invoice $invoice)
{
    // 🔒 AMBIL GROUP DARI MASTER TRAINING
    $group = EventTrainingGroup::where(
        'master_training_id',
        $invoice->master_training_id
    )->firstOrFail();

    // ✅ AUTHORIZE LEWAT GROUP
    $this->authorize('approveFinance', $group);

    $request->validate([
        'amount' => 'required|numeric|min:1',
    ]);

    DB::transaction(function () use ($request, $invoice) {

        $amount = (float) $request->amount;

        if ($amount > $invoice->remainingAmount()) {
            abort(422, 'Pembayaran melebihi sisa tagihan.');
        }

        $newPaid = $invoice->paid_amount + $amount;

        $status = $newPaid >= $invoice->total_amount
            ? 'paid'
            : 'partial';

        $invoice->update([
            'paid_amount' => $newPaid,
            'status'      => $status,
        ]);

        // 🔁 SYNC PARTICIPANT (DELTA)
        $invoice->syncParticipants($amount);
    });

    return back()->with('success', 'Pembayaran berhasil diproses.');
}

}
