<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Builder
 */
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

    public function affiliations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CompanyAffiliation::class);
    }
}
