<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EventTraining extends Model
{
    protected $table = 'event_trainings';

    protected $fillable = [
        'training_id',
        'jenis_event',          // training | non_training
        'training_type',        // reguler | inhouse
        'non_training_type',    // perpanjangan | resertifikasi
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

    public function staff()
    {
        return $this->hasMany(EventStaff::class);
    }

    /* ================= EVENT TYPE ================= */

    public function isTraining(): bool
    {
        return $this->jenis_event === 'training';
    }

    public function isNonTraining(): bool
    {
        return $this->jenis_event === 'non_training';
    }

    public function isReguler(): bool
    {
        return $this->training_type === 'reguler';
    }

    public function isInhouse(): bool
    {
        return $this->training_type === 'inhouse';
    }

    public function isPerpanjangan(): bool
    {
        return $this->non_training_type === 'perpanjangan';
    }

    public function isResertifikasi(): bool
    {
        return $this->non_training_type === 'resertifikasi';
    }

    /* ================= STATUS ENGINE ================= */

 public function refreshStatus(): void
{
    // 🔒 JANGAN SENTUH EVENT 1 HARI
    if (
        $this->tanggal_start &&
        $this->tanggal_end &&
        $this->tanggal_start->equalTo($this->tanggal_end)
    ) {
        return;
    }

    // 🔒 JANGAN SENTUH PERPANJANGAN
    if (
        $this->jenis_event === 'non_training' &&
        $this->non_training_type === 'perpanjangan'
    ) {
        return;
    }

    // ⛔ kalau belum ada tanggal
    if (! $this->tanggal_start || ! $this->tanggal_end) {
        return;
    }

    $today = Carbon::today();

    // PENDING → ACTIVE
    if (
        $this->status === 'pending' &&
        $today->gte($this->tanggal_start)
    ) {
        $this->updateQuietly(['status' => 'active']);
        return;
    }

    // ACTIVE → ON PROGRESS
    if (
        $this->status === 'active' &&
        $today->between($this->tanggal_start, $this->tanggal_end)
    ) {
        $this->updateQuietly(['status' => 'on_progress']);
        return;
    }

    // ON PROGRESS → DONE
    if (
        in_array($this->status, ['active', 'on_progress']) &&
        $today->gt($this->tanggal_end)
    ) {
        $this->updateQuietly(['status' => 'done']);
        return;
    }
}

    /* ================= BUSINESS RULES ================= */

    public function needFinanceApproval(): bool
    {
        return $this->isTraining() && $this->isInhouse();
    }

    public function canInputCertificate(): bool
    {
        if ($this->isPerpanjangan()) {
            return true;
        }

        if ($this->isResertifikasi()) {
            return $this->status === 'done';
        }

        return $this->status === 'done' && $this->finance_approved;
    }

    public function certificateStatusLabel(): string
    {
        if ($this->isPerpanjangan()) {
            return 'Non Training';
        }

        if ($this->status !== 'done') {
            return 'Belum selesai';
        }

        if ($this->isInhouse()) {
            return $this->finance_approved
                ? 'Siap dibuat'
                : 'Menunggu finance';
        }

        return $this->participants()
            ->wherePivot('is_paid', true)
            ->exists()
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

    public function certificateValidityYears(): ?int
    {
        return match (strtoupper($this->jenis_sertifikasi)) {
            'KEMENTERIAN', 'KEMNAKER' => 5,
            'BNSP'                  => 4,
            default                 => null,
        };
    }
}
