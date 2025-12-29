<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = [
        'participant_id',
        'event_training_id',
        'nomor_sertifikat',
        'tanggal_terbit',
        'tanggal_expired',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class);
    }

    public function eventTraining()
    {
        return $this->belongsTo(EventTraining::class);
    }
}
