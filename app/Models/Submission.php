<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    protected $fillable = [
        'dealer_id',
        'event_id',
        'full_name',
        'email',
        'phone',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'guest_count',
        'wants_appointment',
        'notes',
        'meta_json',
    ];

    protected $casts = [
        'meta_json' => 'array',
        'know_your_car_date' => 'date',
        'vehicle_purchased' => 'date',
        'wants_appointment' => 'boolean',
    ];

    /**
     * @return BelongsTo
     */
    public function dealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class);
    }
}
