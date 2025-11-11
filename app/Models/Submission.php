<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'dealer_id',
        'event_id',
        'first_name',
        'last_name',
        'full_name',
        'email',
        'phone',
        'guest_count',
        'wants_appointment',
        'know_your_car_date',
        'vehicle_purchased',
        'notes',
        'meta_json',
        'fingerprint',
    ];

    protected $casts = [
        'wants_appointment' => 'boolean',
        'guest_count' => 'integer',
        'know_your_car_date' => 'date',
        'vehicle_purchased' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    public static function normalizeName(?string $name): string
    {
        $name = trim((string) $name);
        $name = preg_replace('/\s+/u', ' ', $name);

        return mb_strtolower($name);
    }

    /**
     * @param string|null $email
     *
     * @return string
     */
    public static function normalizeEmail(?string $email): string
    {
        return mb_strtolower(trim((string) $email));
    }

    /**
     * @param string|null $phone
     *
     * @return string
     */
    public static function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    /**
     * @param string|null $notes
     *
     * @return string
     */
    public static function normalizeNotes(?string $notes): string
    {
        $notes = trim((string) $notes);

        return preg_replace('/\s+/u', ' ', $notes);
    }

    public static function makeFingerprint(array $data): string
    {
        $payload = [
            'dealer_id' => (int) ($data['dealer_id'] ?? 0),
            'date' => (string) ($data['know_your_car_date'] ?? ''), // YYYY-MM-DD
            'full_name' => self::normalizeName($data['full_name'] ?? ''),
            'email' => self::normalizeEmail($data['email'] ?? ''),
            'phone' => self::normalizePhone($data['phone'] ?? ''),
            'guest_count' => (int) ($data['guest_count'] ?? 0),
            'wants_appointment' => (int) (!empty($data['wants_appointment'])),
            'notes' => self::normalizeNotes($data['notes'] ?? ''),
        ];

        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
}
