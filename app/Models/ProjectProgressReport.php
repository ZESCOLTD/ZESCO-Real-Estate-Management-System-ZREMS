<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProgressReport extends Model
{
    protected $fillable = [
        'period_from', 'period_to', 'report_date', 'current_status',
        'overall_progress', 'form_data', 'created_by', 'updated_by',
    ];

    protected $casts = [
        'period_from' => 'date',
        'period_to' => 'date',
        'report_date' => 'date',
        'overall_progress' => 'integer',
        'form_data' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'real_estate_project_id');
    }
}
