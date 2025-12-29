<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
    'code',           // ditambahkan
    'name',
    'description',    // ditambahkan
    'master_training_id', // tetap ada kalau nanti pakai relasi
];


    // Relasi ke master training (judul besar)
    public function masterTraining()
    {
        return $this->belongsTo(MasterTraining::class);
    }
}
