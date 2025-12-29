<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventStaff extends Model
{
    protected $table = 'event_staff';

    protected $fillable = [
        'event_training_id',
        'name',
        'phone',
        'role',
    ];

    public function event()
    {
        return $this->belongsTo(EventTraining::class, 'event_training_id');
    }
}
