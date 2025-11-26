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

    /**
     * The attributes that aren't mass assignable.
     * These flags should only be set by admins through the Filament panel
     *
     * @var list<string>
     */
    protected $guarded = ['is_magento_member', 'is_recommended'];

    public function affiliations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\CompanyAffiliation::class);
    }
}
