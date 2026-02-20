<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'nama',
        'nik',
        'no_hp',
        'alamat',
        'tanggal_lahir',
        'catatan',
        'last_company_id'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /* =====================================================
     * RELATIONS
     * ===================================================== */

    /**
     * RELASI KE EVENT (pivot utama sistem)
     * pivot = sumber kebenaran finance + company
     */
    public function events()
    {
        return $this->belongsToMany(EventTraining::class, 'event_participants')
            ->using(EventParticipant::class) // 🔥 WAJIB agar pivot model aktif
            ->withPivot([
                'company_id',
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

    /**
     * RELASI KE SERTIFIKAT
     */
    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    /**
     * COMPANY TERAKHIR PESERTA
     */
    public function lastCompany()
    {
        return $this->belongsTo(Company::class, 'last_company_id');
    }

    /* =====================================================
     * HELPERS
     * ===================================================== */

    /**
     * Ambil sertifikat peserta untuk event tertentu
     */
    public function certificateForEvent(int $eventTrainingId)
    {
        return $this->certificates()
            ->where('event_training_id', $eventTrainingId)
            ->first();
    }

    /**
     * Ambil semua company yang pernah diikuti peserta
     */
    public function companies()
    {
        return $this->events()
            ->pluck('event_participants.company_id')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Helper: cek pernah ikut training tertentu
     */
    public function hasTraining(int $trainingId): bool
    {
        return $this->events()
            ->where('training_id', $trainingId)
            ->exists();
    }
}
