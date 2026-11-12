<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LeaseLandlordApplication extends Model
{
    public const ORIGIN_EXTERNAL_REQUEST = 'external_request';
    public const ORIGIN_ZESCO_OFFER = 'zesco_offer';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_TENANT_REVIEW = 'tenant_review';
    public const STATUS_TENANT_FEEDBACK = 'tenant_feedback';
    public const STATUS_LEGAL_REVIEW = 'legal_review';
    public const STATUS_AG_REVIEW = 'ag_review';
    public const STATUS_LEGAL_FINALIZED = 'legal_finalized';
    public const STATUS_AG_FINALIZED = 'ag_finalized';

    public const RENT_PERIODS = ['monthly', '3_months', '6_months'];

    protected $fillable = [
        'reference_no',
        'origin',
        'status',
        'external_tenant_name',
        'contact_name',
        'contact_phone',
        'contact_email',
        'property_reference',
        'property_description',
        'proposed_start_date',
        'proposed_end_date',
        'proposed_monthly_rent',
        'proposed_rent_period',
        'proposed_rent_amount',
        'lease_agreement_id',
        'proposal_notes',
        'created_by',
        'submitted_at',
    ];

    protected $casts = [
        'proposed_start_date' => 'date',
        'proposed_end_date' => 'date',
        'proposed_monthly_rent' => 'decimal:2',
        'proposed_rent_amount' => 'decimal:2',
        'submitted_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function leaseAgreement(): BelongsTo
    {
        return $this->belongsTo(LeaseAgreement::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(LeaseLandlordApplicationEvent::class)
            ->orderBy('occurred_at')
            ->orderBy('id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(TenantDocument::class, 'documentable')->latest('uploaded_at');
    }
}