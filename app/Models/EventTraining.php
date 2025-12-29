<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EventTraining extends Model
{
    /* ================= BASIC CONFIG ================= */

    protected $table = 'event_trainings';

    protected $fillable = [
        'training_id',
        'jenis_event',
        'harga_paket',
        'job_number',
        'tanggal_start',
        'tanggal_end',
        'tempat',
        'jenis_sertifikasi',
        'sertifikasi',
        'status',
        'finance_approved',
        'finance_approved_at',
    ];

    protected $casts = [
        'tanggal_start'       => 'date',
        'tanggal_end'         => 'date',
        'finance_approved'    => 'boolean',
        'finance_approved_at' => 'datetime',
    ];

    /* ================= RELATIONS ================= */

    public function training()
    {
        return $this->belongsTo(Training::class);
    }

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'event_participants')
            ->using(EventParticipant::class)
            ->withPivot([
                'id',
                'harga_peserta',
                'is_paid',
                'paid_at',
                'certificate_ready',
                'certificate_issued_at',
            ])
            ->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /* ================= STATUS CORE ================= */

    /**
     * Update status otomatis berdasarkan tanggal
     * Panggil di controller / scheduler
     */
    public function refreshStatus(): void
    {
        $today = Carbon::today();

        // pending = manual
        if ($this->status === 'pending') {
            return;
        }

        if ($today->lt($this->tanggal_start)) {
            $this->status = 'active';
        } elseif ($today->between($this->tanggal_start, $this->tanggal_end)) {
            $this->status = 'on_progress';
        } elseif ($today->gt($this->tanggal_end)) {
            $this->status = 'done';
        }

        $this->save();
    }

    /* ================= STATUS HELPERS ================= */

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnProgress(): bool
    {
        return $this->status === 'on_progress';
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    /* ================= EVENT TYPE ================= */

    public function isReguler(): bool
    {
        return $this->jenis_event === 'reguler';
    }

    public function isInhouse(): bool
    {
        return $this->jenis_event === 'inhouse';
    }

    /* ================= BUSINESS RULES ================= */

    /**
     * Peserta boleh ditambah?
     */
    public function canAddParticipants(): bool
    {
        return in_array($this->status, ['active', 'on_progress']);
    }

    /**
     * Event boleh diedit?
     */
    public function canEdit(): bool
    {
        return in_array($this->status, ['pending', 'active']);
    }

    /**
     * Event boleh dihapus?
     * DONE = LOCK TOTAL
     */
    public function canDelete(): bool
    {
        return $this->status === 'pending';
    }

    /* ================= CERTIFICATE RULE ENGINE ================= */

    /**
     * 🔹 INHOUSE
     * Satu kali ACC finance → semua boleh dibuat
     */
    public function canGenerateCertificateInhouse(): bool
    {
        return $this->isInhouse()
            && $this->status === 'done'
            && $this->finance_approved;
    }

    /**
     * 🔹 REGULER
     * Minimal ada 1 peserta yang sudah bayar
     */
    public function hasParticipantReadyForCertificate(): bool
    {
        return $this->participants()
            ->wherePivot('is_paid', true)
            ->exists();
    }

    /**
     * Untuk badge laporan
     */
    public function certificateStatusLabel(): string
    {
        if ($this->status !== 'done') {
            return 'Belum selesai';
        }

        if ($this->isInhouse()) {
            return $this->finance_approved
                ? 'Siap dibuat'
                : 'Menunggu finance';
        }

        // reguler
        return $this->hasParticipantReadyForCertificate()
            ? 'Sebagian siap'
            : 'Belum ada pembayaran';
    }

    /* ================= FINANCE ================= */

    public function approveFinance(): void
    {
        $this->finance_approved = true;
        $this->finance_approved_at = now();
        $this->save();
    }

    public function staff()
    {
        return $this->hasMany(EventStaff::class, 'event_training_id');
    }

    public function certificateValidityYears(): ?int
    {
        return match ($this->jenis_sertifikasi) {
            'Kementrian', 'Kemnaker' => 5,
            'Bnsp', 'BNSP'           => 4,
            default                  => null, // Alkon Best Mandiri
        };
    }

    public function canInputCertificate(): bool
{
    return $this->status === 'done'
        && $this->finance_approved === true;
}

}
