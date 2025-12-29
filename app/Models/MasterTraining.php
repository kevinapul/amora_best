<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTraining extends Model
{
    protected $fillable = ['name'];

    public function events()
    {
        return $this->hasMany(EventTraining::class, 'master_training_id');
    }
}
