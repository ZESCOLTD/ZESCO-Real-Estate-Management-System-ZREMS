<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RealEstateProject extends Model
{
    use SoftDeletes;

    protected $attributes = [
        'record_origin' => 'proposal',
        'lifecycle_status' => 'Proposed',
        'proposal_status' => 'Draft',
    ];

    protected $fillable = [
        'reference_no',
        'record_origin',
        'lifecycle_status',
        'proposal_status',
        'project_title',
        'project_category',
        'property_name',
        'property_reference',
        'town_location',
        'responsible_officer',
        'indicative_cost',
        'approved_budget',
        'start_date',
        'expected_completion_date',
        'proposal_data',
        'created_by',
    ];

    protected $casts = [
        'indicative_cost' => 'decimal:2',
        'approved_budget' => 'decimal:2',
        'start_date' => 'date:Y-m-d',
        'expected_completion_date' => 'date:Y-m-d',
        'proposal_data' => 'array',
    ];

    protected static function booted(): void
    {
        static::created(function (self $project): void {
            if (blank($project->reference_no)) {
                $project->forceFill([
                    'reference_no' => sprintf(
                        'REP-%s-%03d',
                        $project->created_at->format('Y'),
                        $project->id
                    ),
                ])->saveQuietly();
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workflowInstances(): MorphMany
    {
        return $this->morphMany(WorkflowInstance::class, 'workflowable')->latest();
    }
}
