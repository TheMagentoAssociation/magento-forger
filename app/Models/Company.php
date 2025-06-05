<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name', // Company name
        'is_magento_member',
        'is_recommended',
        'email',
        'phone',
        'website',
        'address',
        'zip',
        'city',
        'state',
        'country',
        'country_code',
        'logo',
    ];
}
