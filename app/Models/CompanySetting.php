<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'email',
        'phone',
        'address',
        'currency',
        'tax_name',
        'default_tax_rate',
        'logo',
        'gemini_api_key'
    ];
}
