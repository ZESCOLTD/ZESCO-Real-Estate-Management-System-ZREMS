<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaseLifecycleEvent extends Model
{
    protected $fillable = [
        'lease_lifecycle_case_id',
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

    public function lifecycleCase(): BelongsTo
    {
        return $this->belongsTo(LeaseLifecycleCase::class, 'lease_lifecycle_case_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}