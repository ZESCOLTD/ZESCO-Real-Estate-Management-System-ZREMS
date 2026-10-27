<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectRiskAssessment extends Model
{
    protected $fillable = [
        'form_data',
        'risk_count',
        'overall_position',
        'next_review_date',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_data' => 'array',
        'risk_count' => 'integer',
        'next_review_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'real_estate_project_id');
    }
}
