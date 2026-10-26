<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class ProjectFeasibilityAssessment extends Model
{
    protected $fillable = [
        'project_title',
        'proposal_reference',
        'property_site_name',
        'building_structure_name',
        'town_location',
        'assessment_date',
        'lead_assessing_department',
        'prepared_by',
        'other_departments_consulted',
        'created_by',
        'prepared_by_user_id',
        'proposed_works_type',
        'site_condition',
        'existing_use',
        'site_information_availability',
        'key_existing_issue',
        'site_building_suitability',
        'structural_capacity',
        'space_layout_suitability',
        'site_access',
        'electricity_supply',
        'water_supply',
        'drainage_sewerage',
        'fire_safety_access',
        'environmental_sheq_concerns',
        'permits_statutory_requirements',
        'technical_limitations_measures',
        'options_considered',
        'preferred_option_number',
        'preference_reason',
        'preliminary_cost',
        'cost_not_available',
        'basis_of_estimate',
        'funding_position',
        'implementation_months',
        'period_not_established',
        'cost_position',
        'resource_availability',
        'operations_impact',
        'delivery_timeframe',
        'cost_implementation_constraints',
        'significant_risks',
        'overall_finding',
        'finding_basis',
        'next_stage_conditions',
        'supporting_records',
        'other_supporting_record',
        'assessed_by_name_designation',
        'assessed_department',
        'signature_text',
        'confirmation_date',
    ];

    protected $casts = [
        'assessment_date' => 'date',
        'options_considered' => 'array',
        'preferred_option_number' => 'integer',
        'preliminary_cost' => 'decimal:2',
        'cost_not_available' => 'boolean',
        'implementation_months' => 'integer',
        'period_not_established' => 'boolean',
        'significant_risks' => 'array',
        'supporting_records' => 'array',
        'confirmation_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'real_estate_project_id');
    }
    public function preparer(): BelongsTo
{
    return $this->belongsTo(User::class, 'prepared_by_user_id');
}
}
