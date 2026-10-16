<?php

namespace App\Models;

use App\Enums\Acquisition\AcquisitionType;
use App\Enums\Acquisition\ApprovalStatus;
use App\Enums\Land\OwnershipStatus;
use App\Enums\Land\Unit;
use App\Enums\Shared\YesNo;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
class AcquisitionDueDiligence extends Model
{
    use SoftDeletes;
    protected $casts = [
        'status'               => ApprovalStatus::class
    ];
    protected $fillable = [
        'ref_no',
        'request_date',
        'team_creation_action',
'acquisition_request_id',
        'acquisition_request_reference_no',
        'potential_properties',
        'offer_price',
        'findings_report_path',
        'findings_report_comment',

        'filter_viable_option',
        'shortlisted_properties',

        'property_no',
        'property_no_file_path',
        'property_no_comment',

        'coordinates',
        'coordinates_file_path',
        'coordinates_comment',

        'land_extent',
        'land_extent_file_path',
        'land_extent_comment',

        'province',
        'district',
        'township',
        'chiefdom',

        'owner_name',
        'land_tenure',
        'registration_status',
        'registration_file_path',
        'registration_comment',

        'encumbrances_file_path',
        'encumbrances_comment',

        'suitability',
        'mitigation_notes',

        'valuation_amount',
        'valuation_report_path',
        'valuation_report_comment',

        'final_offer',
        'contract_execution_path',
        'invoice_path',
        'payment_amount',
        'purchase_outcome',

        // Security section
        'security_property_reference_number',
        'security_acquisition_request_number',
        'security_province',
        'security_district',
        'security_gps_coordinates',
        'security_property_type',
        'security_intended_use',
        'security_property_documents',

        'security_property_occupied',
        'security_occupied_by',
        'security_activities',
        'security_occupancy_notes',
        'security_occupancy_evidence',

        'security_issues',
        'security_rating',
        'security_evidence',

        'security_traditional_authority',
        'security_chiefdom_name',
        'security_community_concerns',
        'security_community_documents',

        'security_road_access',
        'security_boundary_identifiable',
        'security_terrain_notes',
        'security_access_route_images',

        'security_encroachment_risk',
        'security_operational_interference',
        'security_community_conflict_risk',
        'security_vandalism_risk',
        'security_risk_assessment_files',

        'security_current_owner',
        'security_previous_owner',
        'security_ownership_disputes',
        'security_ownership_documents',

        'security_observations',
        'security_final_recommendation',
        'security_final_assessment_files',

        // Security: Protective infrastructure requirements
        'security_perimeter_fencing_required',
        'security_fencing_types',
        'security_fencing_other',
        'security_guard_post_required',
        'security_guard_post_types',
        'security_infrastructure_stages',
        'security_protective_measures',
        'all_team_signed_off',
        'signoff_completed_at',
        'status'
    ];

    protected static function booted()
    {
        static::created(function ($dueDiligence) {
            $dueDiligence->workflowInstance()->create([
                'workflow_definition_id' => 2,
                'status' => 'Draft',
                'initiated_by' => auth()->id(),
                'initiated_at' => now(),
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function acquisitionRequest(): BelongsTo
    {
        return $this->belongsTo(AcquisitionRequest::class);
    }

    public function landParcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class);
    }

    public function documents()
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }

    public function teamMembers()
    {
        return $this->hasMany(AcquisitionDueDiligenceTeam::class);
    }

    public function properties()
    {
        return $this->hasMany(AcquisitionDueDiligenceProperty::class);
    }

    public function signoffs()
    {
        return $this->hasMany(
            WorkflowActionsDueDiligenceSignoff::class,
            'acquisition_due_diligence_id'
        );
    }

    public function approvedSignoffs()
    {
        return $this->signoffs()->where('action', 'approved');
    }

    public function rejectedSignoffs()
    {
        return $this->signoffs()->where('action', 'rejected');
    }

    /*
    |--------------------------------------------------------------------------
    | Workflow (FIXED)
    |--------------------------------------------------------------------------
    */

    public function workflowInstance()
    {
        return $this->morphOne(WorkflowInstance::class, 'workflowable');
    }

    public function workflowActions()
    {
        return $this->hasManyThrough(
            WorkflowAction::class,
            WorkflowInstance::class,
            'workflowable_id',
            'workflow_instance_id',
            'id',
            'id'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getApprovalProgressAttribute()
    {
        $expected = $this->teamMembers()->count();

        $approved = $this->signoffs()
            ->where('action', 'approved')
            ->distinct('acted_by')
            ->count('acted_by');

        return (object) [
            'expected' => $expected,
            'approved' => $approved,
            'remaining' => max($expected - $approved, 0),
            'is_complete' => $approved >= $expected && $expected > 0,
        ];
    }

    public function getIsFullyApprovedAttribute(): bool
    {
        return $this->approval_progress->is_complete;
    }
}
