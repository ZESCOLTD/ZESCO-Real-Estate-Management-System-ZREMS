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
    ];

    protected $casts = [
        'assessment_date' => 'date',
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