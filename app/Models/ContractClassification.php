<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContractClassification extends Model
{
    use HasFactory;

    protected $table = 'contract_classifications';

    protected $fillable = [
        'contract_request_id',

        'contract_type',
        'contract_value',
        'risk_rating',
        'procurement_category',

        'is_strategic',
        'is_international',
        'counterparty_type',
        'is_land_property_related',
        'is_financing_related',
        'is_long_term_commitment',
        'has_regulatory_implications',
        'attorney_general_review_required',

        'legal_review_level',
        'approval_authority',
        'required_reviewers',
        'required_checks',
        'execution_requirements',
        'mandatory_documents',

        'status',

        'classified_by',
        'classified_at',
        'classification_notes',
    ];

    protected $casts = [
        'contract_value' => 'decimal:2',

        'is_strategic' => 'boolean',
        'is_international' => 'boolean',
        'is_land_property_related' => 'boolean',
        'is_financing_related' => 'boolean',
        'is_long_term_commitment' => 'boolean',
        'has_regulatory_implications' => 'boolean',
        'attorney_general_review_required' => 'boolean',

        'required_reviewers' => 'array',
        'required_checks' => 'array',
        'execution_requirements' => 'array',
        'mandatory_documents' => 'array',

        'classified_at' => 'datetime',
    ];


    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function contractRequest()
    {
        return $this->belongsTo(
            ContractRequest::class,
            'contract_request_id'
        );
    }

    public function classifiedBy()
    {
        return $this->belongsTo(
            User::class,
            'classified_by'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClassified(): bool
    {
        return in_array(
            $this->status,
            ['classified', 'reclassified']
        );
    }

    public function requiresAttorneyGeneralReview(): bool
    {
        return $this->attorney_general_review_required;
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

    public function scopeClassified($query)
    {
        return $query->whereIn(
            'status',
            ['classified', 'reclassified']
        );
    }

    public function scopeRequiresAttorneyGeneralReview($query)
    {
        return $query->where(
            'attorney_general_review_required',
            true
        );
    }
}
