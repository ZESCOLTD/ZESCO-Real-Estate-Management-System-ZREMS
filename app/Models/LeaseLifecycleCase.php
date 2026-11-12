<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeaseLifecycleCase extends Model
{
    public const TYPE_RENEWAL = 'renewal';
    public const TYPE_TERMINATION = 'termination';
    public const TYPE_HANDOVER = 'handover';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_RECORDED = 'recorded';

    protected $fillable = [
        'lease_agreement_id',
        'case_type',
        'status',
        'reason',
        'proposed_start_date',
        'proposed_end_date',
        'proposed_monthly_payment',
        'proposed_effective_date',
        'notice_date',
        'details',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date' => 'date',
        'proposed_monthly_payment' => 'decimal:2',
        'proposed_effective_date' => 'date',
        'notice_date' => 'date',
        'details' => 'array',
        'submitted_at' => 'datetime',
    ];

    public function leaseAgreement(): BelongsTo
    {
        return $this->belongsTo(LeaseAgreement::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeaseLifecycleEvent::class)->orderBy('occurred_at')->orderBy('id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TenantDocument::class, 'documentable')->latest('uploaded_at');
    }
}