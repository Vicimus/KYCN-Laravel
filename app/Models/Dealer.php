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
        'dealership_logo',
        'dealership_url',
    ];


    /**
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    protected static function booted()
    {
        static::creating(function ($dealer) {
            if (!$dealer->portal_token) {
                $dealer->portal_token = bin2hex(random_bytes(16));
            }
        });

        static::saving(function (Dealer $dealer) {
            if (empty($dealer->portal_token)) {
                $dealer->portal_token = hash_hmac('sha256', (string) $dealer->code, config('app.key'));
            }
        });
    }
}
