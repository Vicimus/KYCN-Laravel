<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dealer extends Model
{
    protected $fillable = [
        'name',
        'code',
        'portal_token',
        'dealership_url',
        'dealership_logo',
    ];

    /**
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
