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

    /* =====================================================
     * RELATIONS
     * ===================================================== */

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
                'jenis_layanan',
                'harga_peserta',
                'paid_amount',
                'remaining_amount',
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

    public function staffs()
    {
        return $this->hasMany(EventStaff::class, 'event_training_id');
    }

    /* =====================================================
     * STAFF HELPERS (UNTUK VIEW)
     * ===================================================== */

    public function staffsByRole(): array
    {
        return $this->staffs
            ->groupBy('role')
            ->map(fn ($items) => $items->pluck('name')->implode(', '))
            ->toArray();
    }

    public function instrukturs(): string
    {
        return $this->staffsByRole()['Instruktur'] ?? '-';
    }

    public function trainingOfficers(): string
    {
        return $this->staffsByRole()['Training Officer'] ?? '-';
    }

    /* =====================================================
     * EVENT TYPE HELPERS
     * ===================================================== */

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

    /* =====================================================
     * GROUP SHORTCUTS
     * ===================================================== */

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

    /* =====================================================
     * STATUS ENGINE
     * ===================================================== */

    public function refreshStatus(): void
    {
        if ($this->status === 'pending') return;
        if ($this->isPerpanjangan()) return;
        if (! $this->tanggal_start || ! $this->tanggal_end) return;

        $now = Carbon::now();

        if ($now->lt($this->tanggal_start)) {
            $this->updateQuietly(['status' => 'active']);
            return;
        }

        if ($now->between($this->tanggal_start, $this->tanggal_end)) {
            $this->updateQuietly(['status' => 'on_progress']);
            return;
        }

        if ($now->gt($this->tanggal_end)) {
            $this->updateQuietly(['status' => 'done']);
        }
    }

    /* =====================================================
     * FINANCE HELPERS (INI INTI BULK PAYMENT)
     * ===================================================== */

    /** daftar perusahaan (NULL = individu) */
    public function companies()
    {
        return $this->participants
            ->pluck('perusahaan')
            ->filter()
            ->unique()
            ->values();
    }

    /** summary keuangan per perusahaan / individu */
    public function financeSummary(?string $company = null): array
    {
        $participants = $this->participants
            ->when($company, fn ($c) => $c->where('perusahaan', $company))
            ->when($company === null, fn ($c) => $c->whereNull('perusahaan'));

        return [
            'total'  => $participants->sum(fn ($p) => $p->pivot->harga_peserta),
            'paid'   => $participants->sum(fn ($p) => $p->pivot->paid_amount),
            'remain' => $participants->sum(fn ($p) => $p->pivot->remaining_amount),
        ];
    }

    /** cek semua peserta lunas */
public function isFullyPaid(): bool
{
    if ($this->isInhouse()) {
        return $this->eventTrainingGroup?->harga_paket > 0;
    }

    return $this->participants->every(fn ($p) =>
        ($p->pivot->remaining_amount ?? $p->pivot->harga_peserta) <= 0
    );
}

    /** bulk bayar per perusahaan / individu */
    public function bulkPay(?string $company, float $amount): void
    {
        $participants = $this->participants
            ->when($company, fn ($c) => $c->where('perusahaan', $company))
            ->when($company === null, fn ($c) => $c->whereNull('perusahaan'));

        $remaining = $amount;

        foreach ($participants as $p) {
            if ($remaining <= 0) break;

            $pivot = $p->pivot;

            if ($pivot->remaining_amount <= 0) continue;

            $pay = min($remaining, $pivot->remaining_amount);
            $pivot->pay($pay);

            $remaining -= $pay;
        }
    }

    /* =====================================================
     * BUSINESS RULES
     * ===================================================== */

    public function needFinanceApproval(): bool
    {
        return $this->isTraining() && $this->isInhouse();
    }

    public function approveFinance(): void
    {
        if (! $this->isFullyPaid()) {
            throw new \Exception('Masih ada peserta belum lunas');
        }

        $this->update([
            'finance_approved'    => true,
            'finance_approved_at' => now(),
        ]);
    }

    public function canInputCertificate(): bool
    {
        if ($this->isPerpanjangan()) return true;
        if ($this->isResertifikasi()) return $this->status === 'done';

        return $this->status === 'done'
            && $this->eventTrainingGroup?->finance_approved;
    }

    public function certificateValidityYears(): ?int
    {
        return match (strtoupper($this->eventTrainingGroup?->jenis_sertifikasi)) {
            'KEMENTERIAN', 'KEMNAKER' => 5,
            'BNSP'                  => 4,
            default                 => null,
        };
    }
    /* =====================================================
 * FINANCE TOTAL HELPERS (UNTUK VIEW)
 * ===================================================== */

public function totalTagihan(): float
{
    // INHOUSE → 1x harga paket
    if ($this->isInhouse()) {
        return (float) ($this->eventTrainingGroup?->harga_paket ?? 0);
    }

    // REGULER → total semua peserta
    return $this->participants
        ->sum(fn ($p) => $p->pivot->harga_peserta);
}

public function totalPaid(): float
{
    return $this->participants
        ->sum(fn ($p) => $p->pivot->paid_amount ?? 0);
}

public function totalRemaining(): float
{
    return max(0, $this->totalTagihan() - $this->totalPaid());
}

public function financeBadge(): array
{
    $total = $this->totalTagihan();
    $paid  = $this->totalPaid();

    if ($paid <= 0) {
        return ['label' => 'BELUM BAYAR', 'color' => 'red'];
    }

    if ($paid < $total) {
        return ['label' => 'CICILAN', 'color' => 'yellow'];
    }

    return ['label' => 'LUNAS', 'color' => 'green'];
}

}
