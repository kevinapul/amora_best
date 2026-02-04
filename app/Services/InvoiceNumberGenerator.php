<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceNumberGenerator
{
    public static function generate(): string
    {
        $date = now()->format('Ymd');

        do {
            $number = 'INV-' . $date . '-' . rand(1000, 9999);
        } while (Invoice::where('invoice_number', $number)->exists());

        return $number;
    }
}
