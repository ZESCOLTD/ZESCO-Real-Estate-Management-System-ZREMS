<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseLandlordApplicationEvent extends Model
{
    protected $fillable = [
        'lease_landlord_application_id',
        'action',
        'from_status',
        'to_status',
        'comment',
        'actor_id',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(LeaseLandlordApplication::class, 'lease_landlord_application_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}