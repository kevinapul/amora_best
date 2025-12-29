<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EventParticipant extends Pivot
{
    protected $table = 'event_participants';

    protected $fillable = [
        'event_training_id',
        'participant_id',
        'harga_peserta',
        'is_paid',
        'paid_at',
        'certificate_ready',
        'certificate_issued_at',
    ];

    /* ================= PAYMENT ================= */

    public function markAsPaid(): void
    {
        $this->is_paid = true;
        $this->paid_at = now();
        $this->save();
    }

    public function isPaid(): bool
    {
        return $this->is_paid === true;
    }

    /* ================= CERTIFICATE ================= */

    public function canGenerateCertificate(): bool
    {
        // reguler: harus sudah bayar
        return $this->is_paid === true;
    }

    public function markCertificateReady(): void
    {
        $this->certificate_ready = true;
        $this->certificate_issued_at = now();
        $this->save();
    }

    public function staff()
{
    return $this->hasMany(EventStaff::class, 'event_training_id');
}

}
