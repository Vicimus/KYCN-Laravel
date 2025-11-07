<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Dealer extends Model
{
    protected $fillable = [
        'name',
        'code',
        'portal_token',
        'dealership_logo',
        'dealership_url',
        'know_your_car_date',
    ];

    protected $casts = [
        'know_your_car_date' => 'date',
    ];


    /**
     * @return HasMany
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Resolve a web-friendly logo URL regardless of storage location.
     */
    public function getDealershipLogoUrlAttribute(): ?string
    {
        $logo = $this->dealership_logo;

        if (empty($logo)) {
            return null;
        }

        if (Str::startsWith($logo, ['http://', 'https://', '//'])) {
            return $logo;
        }

        return asset(ltrim($logo, '/'));
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
