<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectBudgetEstimate extends Model
{
    protected $fillable = [
        'form_data',
        'itemized_subtotal',
        'subtotal_before_contingency_tax',
        'total_estimated_cost',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'form_data' => 'array',
        'itemized_subtotal' => 'decimal:2',
        'subtotal_before_contingency_tax' => 'decimal:2',
        'total_estimated_cost' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(RealEstateProject::class, 'real_estate_project_id');
    }
}
