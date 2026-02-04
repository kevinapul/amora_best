<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoicePaymentService
{
    /**
     * Tambah pembayaran ke invoice (cicilan / full)
     */
    public function addPayment(Invoice $invoice, float $amount): Invoice
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('Jumlah pembayaran tidak valid');
        }

        if (in_array($invoice->status, ['paid', 'cancelled'])) {
            throw new InvalidArgumentException('Invoice sudah tidak bisa dibayar');
        }

        return DB::transaction(function () use ($invoice, $amount) {

            $newPaid = $invoice->paid_amount + $amount;

            if ($newPaid > $invoice->total_amount) {
                throw new InvalidArgumentException(
                    'Pembayaran melebihi total tagihan'
                );
            }

            // Tentukan status baru
            if ($newPaid == $invoice->total_amount) {
                $status = 'paid';
            } elseif ($newPaid > 0) {
                $status = 'partial';
            } else {
                $status = 'draft';
            }

            $invoice->update([
                'paid_amount' => $newPaid,
                'status'      => $status,
            ]);

            return $invoice->fresh();
        });
    }
}
