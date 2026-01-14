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

    /* ================= HELPERS ================= */

    public function isInhouse(): bool
    {
        return $this->training_type === 'inhouse';
    }

    public function isReguler(): bool
    {
        return $this->training_type === 'reguler';
    }

    public function totalParticipants(): int
    {
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->count();
    }

    public function totalTagihan(): float
    {
        if ($this->isInhouse()) {
            return (float) $this->harga_paket;
        }

        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->sum(fn ($p) => $p->pivot->harga_peserta);
    }

    public function totalLunas(): float
    {
        return $this->events
            ->flatMap(fn ($e) => $e->participants)
            ->where('pivot.is_paid', true)
            ->sum(fn ($p) => $p->pivot->harga_peserta);
    }

    public function isFinanceApproved(): bool
    {
        if ($this->events->isEmpty()) {
            return false;
        }

        return $this->events->every(fn ($e) => $e->finance_approved);
    }
}
