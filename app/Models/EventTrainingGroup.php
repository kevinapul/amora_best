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
        'training_type',
        'harga_paket',
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

    /* ================= SUMMARY HELPERS ================= */

    public function totalParticipants(): int
    {
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->count();
    }

    public function totalTagihan(): float
    {
        // INHOUSE → 1x harga paket
        if ($this->isInhouse()) {
            return (float) ($this->harga_paket ?? 0);
        }

        // REGULER → akumulasi semua peserta
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->sum(fn ($p) => $p->pivot->harga_peserta);
    }

public function totalLunas(): float
{
    return $this->events
        ->flatMap(fn ($e) => $e->participants)
        ->sum(fn ($p) => (float) ($p->pivot->paid_amount ?? 0));
}


    public function sisaTagihan(): float
    {
        return max(0, $this->totalTagihan() - $this->totalLunas());
    }

    /* ================= FINANCE ================= */

    public function isFinanceApproved(): bool
    {
        if ($this->events->isEmpty()) {
            return false;
        }

        return $this->events->every(fn ($e) => $e->finance_approved);
    }

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
