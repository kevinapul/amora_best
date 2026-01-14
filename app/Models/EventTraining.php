<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EventTraining extends Model
{
    protected $table = 'event_trainings';

    protected $fillable = [
        'event_training_group_id',
        'training_id',

        'jenis_event',          // training | non_training
        'non_training_type',    // perpanjangan | resertifikasi

        'tanggal_start',
        'tanggal_end',

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

    public function eventTrainingGroup()
    {
        return $this->belongsTo(EventTrainingGroup::class);
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

    public function isPerpanjangan(): bool
    {
        return $this->non_training_type === 'perpanjangan';
    }

    public function isResertifikasi(): bool
    {
        return $this->non_training_type === 'resertifikasi';
    }

    /* ================= GROUP SHORTCUT ================= */

    public function isReguler(): bool
    {
        return $this->eventTrainingGroup?->training_type === 'reguler';
    }

    public function isInhouse(): bool
    {
        return $this->eventTrainingGroup?->training_type === 'inhouse';
    }

    public function hargaPaket(): ?float
    {
        return $this->eventTrainingGroup?->harga_paket;
    }

    /* ================= STATUS ENGINE ================= */

    public function refreshStatus(): void
    {
        // event 1 hari
        if (
            $this->tanggal_start &&
            $this->tanggal_end &&
            $this->tanggal_start->equalTo($this->tanggal_end)
        ) {
            return;
        }

        // perpanjangan
        if ($this->isPerpanjangan()) {
            return;
        }

        if (! $this->tanggal_start || ! $this->tanggal_end) {
            return;
        }

        $today = Carbon::today();

        if ($this->status === 'pending' && $today->gte($this->tanggal_start)) {
            $this->updateQuietly(['status' => 'active']);
            return;
        }

        if (
            $this->status === 'active' &&
            $today->between($this->tanggal_start, $this->tanggal_end)
        ) {
            $this->updateQuietly(['status' => 'on_progress']);
            return;
        }

        if (
            in_array($this->status, ['active', 'on_progress']) &&
            $today->gt($this->tanggal_end)
        ) {
            $this->updateQuietly(['status' => 'done']);
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

        return $this->status === 'done'
            && $this->eventTrainingGroup?->finance_approved;
    }

    public function approveFinance(): void
    {
        $this->eventTrainingGroup?->approveFinance();
    }

    public function certificateValidityYears(): ?int
    {
        return match (strtoupper($this->eventTrainingGroup?->jenis_sertifikasi)) {
            'KEMENTERIAN', 'KEMNAKER' => 5,
            'BNSP'                  => 4,
            default                 => null,
        };
    }
}
