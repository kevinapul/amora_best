<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'event_training_group_id',
        'description',
        'qty',
        'price',
        'subtotal',
        'order',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function eventTrainingGroup()
    {
        return $this->belongsTo(EventTrainingGroup::class);
    }
}
