<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Unguarded]
class BuiltAsset extends Model
{
    use SoftDeletes;

    protected $casts = [
        'engineering_specs' => 'array',
        'commissioning_date' => 'date:Y-m-d',
        'replacement_due_date' => 'date:Y-m-d',
        'replacement_cost' => 'decimal:2',
    ];

    public function landParcel(): BelongsTo
    {
        return $this->belongsTo(LandParcel::class);
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(MaintenanceSchedule::class);
    }

    public function assetInspections(): HasMany
    {
        return $this->hasMany(AssetInspection::class);
    }

    public function assetFailureModes(): HasMany
    {
        return $this->hasMany(AssetFailureMode::class);
    }

    public function assetValuations(): HasMany
    {
        return $this->hasMany(AssetValuation::class);
    }

    public function documents()
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }

    public function payments()
    {
        return $this->morphMany(ParcelPayment::class, 'payable');
    }
}
