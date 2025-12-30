<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Certificate extends Model
{
    protected $fillable = [
        'participant_id',
        'event_training_id',
        'nomor_sertifikat',
        'tanggal_terbit',
        'tanggal_expired',
        'masa_berlaku_tahun',
        'status',
        'input_by',
        'notes',
    ];

    protected $casts = [
        'tanggal_terbit'  => 'date',
        'tanggal_expired' => 'date',
    ];

    /* ================= RELATIONS ================= */

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function eventTraining()
    {
        return $this->belongsTo(EventTraining::class);
    }

    public function inputBy()
    {
        return $this->belongsTo(User::class, 'input_by');
    }

    /* ================= STATUS HELPERS ================= */

    public function isExpired(): bool
    {
        return $this->tanggal_expired?->isPast() ?? false;
    }

    public function isExpiring(int $days = 90): bool
    {
        return $this->tanggal_expired
            && now()->diffInDays($this->tanggal_expired, false) <= $days
            && ! $this->isExpired();
    }

    public function refreshStatus(): void
    {
        if ($this->isExpired()) {
            $this->status = 'expired';
        } elseif ($this->isExpiring()) {
            $this->status = 'expiring';
        } else {
            $this->status = 'active';
        }

        $this->save();
    }
}
