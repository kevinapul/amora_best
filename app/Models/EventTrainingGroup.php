<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Validation\ValidationException;
use App\Models\Invoice;

class EventTrainingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_training_id',
        'job_number',
        'nama_group',
        'training_type',   // reguler | inhouse
        'harga_paket',     // KHUSUS INHOUSE
        'paid_amount',     // KHUSUS INHOUSE (DOMPET)
        'billing_company_id',
        'tempat',
        'jenis_sertifikasi',
        'sertifikasi',
        'finance_approved',
        'finance_approved_at',
    ];

    /* ================= RELATIONS ================= */

    public function masterTraining()
    {
        return $this->belongsTo(MasterTraining::class);
    }

    public function events()
    {
        return $this->hasMany(EventTraining::class);
    }

    /* ================= TYPE CHECK ================= */

    public function isInhouse(): bool
    {
        return $this->training_type === 'inhouse';
    }

    public function isReguler(): bool
    {
        return $this->training_type === 'reguler';
    }

    /* ================= PARTICIPANT ================= */

    public function totalParticipants(): int
    {
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->count();
    }

    /* =====================================================
     * FINANCE CORE — SINGLE SOURCE OF TRUTH
     * ===================================================== */

    /**
     * TOTAL TAGIHAN
     * - INHOUSE  → harga paket
     * - REGULER  → akumulasi harga peserta
     */
    public function totalTagihan(): float
    {
        if ($this->isInhouse()) {
            return (float) ($this->harga_paket ?? 0);
        }

        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->sum(fn ($p) => (float) ($p->pivot->harga_peserta ?? 0));
    }

    /**
     * TOTAL SUDAH DIBAYAR
     * - INHOUSE  → paid_amount (GROUP DOMPET)
     * - REGULER  → sum paid participant
     */
    public function totalLunas(): float
    {
        if ($this->isInhouse()) {
            return (float) ($this->paid_amount ?? 0);
        }

        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->sum(fn ($p) => (float) ($p->pivot->paid_amount ?? 0));
    }

    /**
     * SISA TAGIHAN
     */
    public function sisaTagihan(): float
    {
        return max(0, $this->totalTagihan() - $this->totalLunas());
    }

    /**
     * CEK LUNAS
     */
    public function isFullyPaid(): bool
    {
        if ($this->isInhouse()) {
            return ($this->paid_amount ?? 0) >= ($this->harga_paket ?? 0);
        }

        return $this->sisaTagihan() <= 0;
    }

    /* ================= PAYMENT HANDLER ================= */

    /**
     * Tambah pembayaran INHOUSE
     * (SATU-SATUNYA tempat update uang inhouse)
     */
    public function addPayment(float $amount): void
    {
        if (! $this->isInhouse()) {
            throw new \Exception('addPayment hanya untuk INHOUSE');
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'Jumlah pembayaran tidak valid',
            ]);
        }

        $currentPaid = (float) ($this->paid_amount ?? 0);
        $newTotal    = $currentPaid + $amount;
        $max         = (float) ($this->harga_paket ?? 0);

        if ($newTotal > $max) {
            throw ValidationException::withMessages([
                'amount' => 'Pembayaran melebihi harga paket',
            ]);
        }

        $this->update([
            'paid_amount' => $newTotal,
        ]);
    }

    /* ================= FINANCE STATUS ================= */

    public function financeStatus(): string
    {
        $total = $this->totalTagihan();
        $paid  = $this->totalLunas();

        if ($paid <= 0) {
            return 'BELUM DIBAYAR';
        }

        if ($paid < $total) {
            return 'SEBAGIAN DIBAYAR';
        }

        return 'LUNAS';
    }

    public function invoiceForCompany(int $companyId): ?Invoice
{
    return Invoice::where('company_id', $companyId)
        ->where('master_training_id', $this->master_training_id)
        ->whereNotIn('status', ['cancelled'])
        ->latest()
        ->first();
}

public function isCompanyPaid(int $companyId): bool
{
    $invoice = $this->invoiceForCompany($companyId);
    return $invoice?->isPaid() ?? false;
}

public function billingCompany()
{
    return $this->belongsTo(Company::class, 'billing_company_id');
}

public function resolveBillingCompanyId(): ?int
{
    if ($this->isInhouse()) {
        return $this->billing_company_id;
    }

    return null; // reguler ditentukan dari participant
}

public function assertBillingCompany(): void
{
    if ($this->isInhouse() && ! $this->billing_company_id) {
        abort(422, 'Billing company belum ditentukan untuk training inhouse.');
    }
}

}
