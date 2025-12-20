<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin Builder
 */
class Company extends Model
{
    /**
     * The attributes that aren't mass assignable.
     * These flags should only be set by admins through the Filament panel.
     *
     * @var list<string>
     */
    protected $guarded = ['is_magento_member', 'is_recommended', 'status'];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'pending',
    ];

    public function affiliations(): HasMany
    {
        return $this->hasMany(CompanyAffiliation::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_owners')->withTimestamps();
    }
}
