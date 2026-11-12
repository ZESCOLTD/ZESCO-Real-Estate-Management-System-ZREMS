<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TenantHistoryEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'tenantable_type', 'tenantable_id', 'actor_id', 'event_type', 'summary', 'details', 'occurred_at',
    ];

    protected $casts = [
        'details' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function tenantable(): MorphTo
    {
        return $this->morphTo();
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}