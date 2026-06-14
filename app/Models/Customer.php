<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'email',
        'phone',
        'company_name',
        'address'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
