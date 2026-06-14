<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_no',
        'notes'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
