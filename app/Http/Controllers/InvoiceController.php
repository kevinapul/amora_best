<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Invoice;
use App\Models\EventTrainingGroup;
use App\Services\InvoiceNumberGenerator;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'company_id' => 'required|exists:companies,id',
            'master_training_id' => 'required|exists:master_trainings,id',
        ]);

        return DB::transaction(function () use ($request) {

            // ❌ CEK DUPLIKASI INVOICE AKTIF
            $exists = Invoice::where('company_id', $request->company_id)
                ->where('master_training_id', $request->master_training_id)
                ->whereNotIn('status', ['paid', 'cancelled'])
                ->exists();

            if ($exists) {
                abort(422, 'Invoice untuk company & master training ini masih aktif');
            }

            // ✅ CREATE INVOICE
            $invoice = Invoice::create([
                'company_id' => $request->company_id,
                'master_training_id' => $request->master_training_id,
                'invoice_number' => InvoiceNumberGenerator::generate(),
                'status' => 'draft',
            ]);

            // 🔄 AMBIL TRAINING GROUP TERKAIT
            $groups = EventTrainingGroup::where('master_training_id', $request->master_training_id)
                ->whereHas('participants', function ($q) use ($request) {
                    $q->where('company_id', $request->company_id);
                })
                ->get();

            $total = 0;

            foreach ($groups as $group) {
                $subtotal = $group->harga_paket ?? 0;

                $invoice->items()->create([
                    'event_training_group_id' => $group->id,
                    'description' => $group->nama_group ?? 'Training',
                    'qty' => 1,
                    'price' => $subtotal,
                    'subtotal' => $subtotal,
                ]);

                $total += $subtotal;
            }

            // UPDATE TOTAL INVOICE
            $invoice->update([
                'total_amount' => $total,
            ]);

            return $invoice;
        });
    }

public function pay(Request $request, Invoice $invoice)
    {
        $this->authorize('approveFinance', $invoice);

        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        DB::transaction(function () use ($request, $invoice) {

            // 🔒 SIMPAN KONDISI SEBELUM
            $beforePaid = (float) ($invoice->paid_amount ?? 0);

            $amount = (float) $request->amount;
            $newPaid = $beforePaid + $amount;

            if ($newPaid > (float) $invoice->total_amount) {
                abort(422, 'Pembayaran melebihi total invoice');
            }

            // 🏷 STATUS INVOICE
            $status = $newPaid >= (float) $invoice->total_amount
                ? 'paid'
                : 'partial';

            // ✅ UPDATE INVOICE (SUMBER KEBENARAN)
            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $status,
            ]);

            // 🔑 DELTA PEMBAYARAN (YANG DIDISTRIBUSIKAN)
            $delta = $newPaid - $beforePaid;

            // 🔁 SYNC KE PARTICIPANT (MODEL YANG URUS)
            if ($delta > 0) {
                $invoice->syncParticipants($delta);
            }
        });

        return back()->with('success', 'Pembayaran invoice berhasil diproses');
    }

    public function show(Invoice $invoice)
{
    $invoice->load([
        'company',
        'items',
    ]);

    return view('invoice.show', compact('invoice'));
}


}
