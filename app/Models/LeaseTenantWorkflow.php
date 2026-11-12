<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeaseTenantWorkflow extends Model
{
    public const STATUS_DEPARTMENT_CONFIRMATION = 'department_confirmation';
    public const STATUS_AWAITING_LANDLORD_LEASE = 'awaiting_landlord_lease';
    public const STATUS_LEGAL_REVIEW = 'legal_review';
    public const STATUS_MD_SIGNATURE = 'md_signature';
    public const STATUS_AWAITING_LANDLORD_SIGNATURE = 'awaiting_landlord_signature';
    public const STATUS_EXECUTED_COPY_RECEIVED = 'executed_copy_received';
    public const STATUS_DEPARTMENT_DECLINED = 'department_declined';

    protected $fillable = [
        'lease_agreement_id',
        'department_name',
        'status',
        'initiated_by',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function leaseAgreement(): BelongsTo
    {
        return $this->belongsTo(LeaseAgreement::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeaseTenantWorkflowEvent::class)->orderBy('acted_at')->orderBy('id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TenantDocument::class, 'documentable')->latest('uploaded_at');
    }

    public function isOpen(): bool
    {
        return ! in_array($this->status, [
            self::STATUS_EXECUTED_COPY_RECEIVED,
            self::STATUS_DEPARTMENT_DECLINED,
        ], true);
    }
}