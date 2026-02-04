<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventParticipant extends Pivot
{
    protected $table = 'event_participants';

    protected $fillable = [
        'event_training_id',
        'participant_id',
        'jenis_layanan',
        'harga_peserta',
        'is_paid',
        'paid_at',
        'certificate_ready',
        'certificate_issued_at',
    ];

    /* ================= PAYMENT ================= */

    public function markAsPaid(): void
    {
        if ($this->is_paid) return;

        $this->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);
    }

    public function isPaid(): bool
    {
        return $this->is_paid === true;
    }

    /* ================= CERTIFICATE ================= */

    public function canHaveCertificate(): bool
    {
        return in_array($this->jenis_layanan, [
            'pelatihan_sertifikasi',
            'sertifikasi_resertifikasi',
        ]);
    }

    public function markCertificateReady(): void
    {
        if (! $this->canHaveCertificate()) return;

        $this->update([
            'certificate_ready' => true,
            'certificate_issued_at' => now(),
        ]);
    }
    protected static function booted()
{
    static::creating(function ($pivot) {
        if ($pivot->paid_amount === null) {
            $pivot->paid_amount = 0;
        }

        if ($pivot->remaining_amount === null) {
            $pivot->remaining_amount = $pivot->harga_peserta;
        }

        if ($pivot->is_paid === null) {
            $pivot->is_paid = false;
        }
    });
}

/* 🟡 DIBEKUKAN (READ ONLY) */
public function pay(float $amount): void
{
    if ($amount <= 0) return;
    if ($this->remaining_amount <= 0) return;

    $this->paid_amount += $amount;

    $this->remaining_amount = max(
        0,
        $this->harga_peserta - $this->paid_amount
    );

    if ($this->remaining_amount <= 0) {
        $this->is_paid = true;
        $this->paid_at = now();
    }

    $this->save();
}

}
