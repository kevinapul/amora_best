<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'master_training_id',
        'code',
        'name',
        'description',
    ];

    public function masterTraining()
    {
        return $this->belongsTo(MasterTraining::class);
    }
}
