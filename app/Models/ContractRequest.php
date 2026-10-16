<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContractRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'contract_requests';

    protected $fillable = [
        'request_no',
        'contract_type',
        'priority',
        'business_unit',
        'proposed_contract_title',
        'project_programme',
        'land_asset_number',
        'purpose',
        'business_need',
        'expected_outcome',
        'strategic_objective',
        'consequences_if_not_entered',
        'counterparty_legal_name',
        'counterparty_registration_number',
        'counterparty_supplier_no',
        'counterparty_address',
        'counterparty_contact_person',
        'counterparty_email',
        'counterparty_telephone',
        'estimated_contract_value',
        'currency',
        'contract_duration',
        'funding_source',
        'budget_code',
        'cost_centre',
        'procurement_reference',
        'proposed_payment_structure',
        'preliminary_risk',
        'status',
        'requesting_officer_id',
        'date_requested',
    ];

    protected $casts = [
        'date_requested' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',

        'estimated_contract_value' => 'decimal:2',

        'preliminary_risk' => 'array',
    ];

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isReturned(): bool
    {
        return $this->status === 'returned';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function requestingOfficer()
    {
        return $this->belongsTo(User::class, 'requesting_officer_id');
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function classification()
    {
        return $this->hasOne(
            ContractClassification::class,
            'contract_request_id'
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            ContractRequestAttachment::class
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'supervisor_review');
    }
}
