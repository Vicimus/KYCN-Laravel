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

    protected $appends = [
        'logo_url',
        'initials',
        'initials_bg',
    ];

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->dealership_logo_url ?? $this->dealership_logo ?? null;
    }

    public function getInitialsAttribute(): string
    {
        $name = trim((string) ($this->name ?? ''));
        if ($name === '') {
            return '—';
        }

        $clean = preg_replace('/[^\p{L}\p{N}\s\'\-]+/u', '', $name) ?? '';
        $parts = preg_split('/[\s\-]+/u', $clean, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if (count($parts) >= 2) {
            $initials = mb_substr($parts[0], 0, 1) . mb_substr($parts[1], 0, 1);
        } elseif (count($parts) === 1) {
            $initials = mb_substr($parts[0], 0, 2);
        } else {
            $initials = '—';
        }

        return mb_strtoupper($initials);
    }

    public function getInitialsBgAttribute(): string
    {
        $palette = ['#E9F2FF', '#EAF7F1', '#FFF5E6', '#F3E8FF', '#FFE9EF', '#EAF0FF'];
        $name = (string) ($this->name ?? '');
        $idx = hexdec(substr(md5($name), 0, 2)) % count($palette);

        return $palette[$idx];
    }

    /**
     * Resolve a web-friendly logo URL regardless of storage location.
     */
    public function getDealershipLogoUrlAttribute(): ?string
    {
        $value = $this->dealership_logo;

        if (empty($value)) {
            return null;
        }

        if (Str::startsWith($value, ['http://', 'https://', '//'])) {
            if (Str::startsWith($value, '//')) {
                return (request()->isSecure() ? 'https:' : 'http:') . $value;
            }
            return $value;
        }

        $path = ltrim($value, '/');

        return request()->isSecure() ? secure_url($path) : url($path);
    }

    protected static function booted()
    {
        static::creating(function ($dealer) {
            if (! $dealer->portal_token) {
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
