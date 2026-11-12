<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseExpiryNotificationLog extends Model
{
    protected $fillable = [
        'lease_agreement_id',
        'recipient_id',
        'days_before_end',
        'lease_end_date',
        'queued_at',
    ];

    protected $casts = [
        'days_before_end' => 'integer',
        'lease_end_date' => 'date',
        'queued_at' => 'datetime',
    ];

    public function leaseAgreement(): BelongsTo
    {
        return $this->belongsTo(LeaseAgreement::class);
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}