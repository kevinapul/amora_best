<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Participant extends Model
{
    protected $fillable = [
        'nama',
        'perusahaan',
        'no_hp',
        'alamat',
        'nik',
        'tanggal_lahir',
        'catatan',
    ];

    public function events()
    {
        return $this->belongsToMany(EventTraining::class, 'event_participants');
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
