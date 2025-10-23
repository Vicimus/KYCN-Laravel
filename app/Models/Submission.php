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
    ];

    protected $casts = [
        'wants_appointment' => 'boolean',
        'guest_count' => 'integer',
        'know_your_car_date' => 'date',
        'vehicle_purchased' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
