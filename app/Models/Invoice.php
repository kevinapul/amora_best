<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EventTrainingGroup;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'master_training_id',
        'invoice_number',
        'total_amount',
        'paid_amount',
        'status',
        'issued_at',
        'due_at',
        'notes',
        'footer_text',
    ];

    /* ================= RELATIONS ================= */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function masterTraining()
    {
        return $this->belongsTo(MasterTraining::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /* ================= HELPERS ================= */

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function remainingAmount(): float
    {
        return max(0, (float) $this->total_amount - (float) $this->paid_amount);
    }

    /* =====================================================
     * 🔁 SYNC PEMBAYARAN → PARTICIPANT (FINAL)
     * ===================================================== */

    /**
     * @param float $amount DELTA pembayaran (BUKAN total)
     */
    public function syncParticipants(float $amount): void
    {
        if ($amount <= 0) {
            return;
        }

        $groups = EventTrainingGroup::where(
            'master_training_id',
            $this->master_training_id
        )->get();

        foreach ($groups as $group) {

            if ($group->isInhouse()) {
                $this->syncInhouseParticipants($group);
            } else {
                $this->syncRegulerParticipants($group, $amount);
            }
        }
    }

    /* ================= REGULER ================= */

    protected function syncRegulerParticipants(
        EventTrainingGroup $group,
        float $amount
    ): void {
        if (! $this->company_id) return;

        $remaining = $amount;

        $participants = $group->events
            ->flatMap(fn ($e) => $e->participants)
            ->filter(fn ($p) => (int) $p->company_id === (int) $this->company_id);

        foreach ($participants as $p) {
            if ($remaining <= 0) break;

            $pivot = $p->pivot;

            if ($pivot->remaining_amount <= 0) continue;

            $pay = min($pivot->remaining_amount, $remaining);

            $newPaid   = $pivot->paid_amount + $pay;
            $newRemain = max(0, $pivot->harga_peserta - $newPaid);

            $pivot->update([
                'paid_amount'      => $newPaid,
                'remaining_amount' => $newRemain,
                'is_paid'          => $newRemain <= 0,
                'paid_at'          => $newRemain <= 0 ? now() : null,
            ]);

            $remaining -= $pay;
        }
    }

    /* ================= INHOUSE ================= */

    protected function syncInhouseParticipants(EventTrainingGroup $group): void
    {
        // 🔒 INHOUSE: hanya saat invoice LUNAS
        if (! $this->isPaid()) {
            return;
        }

        foreach ($group->events as $event) {
            foreach ($event->participants as $p) {
                $p->pivot->update([
                    'paid_amount'      => $p->pivot->harga_peserta,
                    'remaining_amount' => 0,
                    'is_paid'          => true,
                    'paid_at'          => now(),
                ]);
            }
        }
    }
}
