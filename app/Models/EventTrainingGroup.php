<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EventTrainingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_training_id',
        'job_number',
        'nama_group',
        'training_type',   // reguler | inhouse
        'harga_paket',     // KHUSUS INHOUSE
        'tempat',
        'jenis_sertifikasi',
        'sertifikasi',
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

    /* ================= FINANCE (SINGLE SOURCE) ================= */

    /**
     * TOTAL TAGIHAN KONTRAK
     */
    public function totalTagihan(): float
    {
        // INHOUSE → 1x harga paket
        if ($this->isInhouse()) {
            return (float) ($this->harga_paket ?? 0);
        }

        // REGULER → akumulasi semua peserta semua event
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->sum(fn ($p) => (float) ($p->pivot->harga_peserta ?? 0));
    }

    /**
     * TOTAL YANG SUDAH DIBAYAR
     */
    public function totalLunas(): float
    {
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

    /* ================= FINANCE STATUS ================= */

    /**
     * Semua event sudah di-ACC finance
     */
    public function isFinanceApproved(): bool
    {
        if ($this->events->isEmpty()) {
            return false;
        }

        return $this->events->every(fn ($e) => $e->finance_approved);
    }

    /**
     * Label status keuangan (untuk view)
     */
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
}
