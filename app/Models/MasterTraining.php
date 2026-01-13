<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTraining extends Model
{
    protected $fillable = [
        'nama_training',
        'kategori',
    ];

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
