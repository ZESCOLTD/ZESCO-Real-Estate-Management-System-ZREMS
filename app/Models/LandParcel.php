<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class LandParcel extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'parcel_no',
        'fms_asset_number',
        'plot_no',
        'cadastral_id',
        'title_number',
        'survey_number',
        'tenure_type',
        'title_type',
        'ownership_status',
        'area_hectares',
        'province',
        'district',
        'chiefdom',
        'village',
        'coordinates',
        'zoning',
        'planning_status',
        'permitted_use',
        'lease_start_date',
        'lease_expiry_date',
        'lease_term_years',
        'status',
        'notes',
    ];

    public function builtAssets(): HasMany
    {
        return $this->hasMany(BuiltAsset::class);
    }

    public function wayleaves(): HasMany
    {
        return $this->hasMany(Wayleave::class);
    }

    public function complianceRecords(): HasMany
    {
        return $this->hasMany(ComplianceRecord::class);
    }

    public function parcelDocuments(): HasMany
    {
        return $this->hasMany(ParcelDocument::class);
    }

    public function parcelPayments(): HasMany
    {
        return $this->hasMany(ParcelPayment::class);
    }

    public function parcelDisputes(): HasMany
    {
        return $this->hasMany(ParcelDispute::class);
    }

    public function acquisitionDueDiligences(): HasMany
    {
        return $this->hasMany(AcquisitionDueDiligence::class);
    }

    public function stakeholders(): BelongsToMany
    {
        return $this->belongsToMany(
            Stakeholder::class,
            'parcel_stakeholders',
            'land_parcel_id',
            'stakeholder_id'
        )
            ->using(ParcelStakeholder::class)
            ->withPivot([
                'role',
                'is_primary',
                'effective_from',
                'effective_to',
                'notes',
            ])
            ->withTimestamps();
    }


    public function documents()
    {
        return $this->morphMany(ParcelDocument::class, 'documentable');
    }


    public function payments()
    {
        return $this->morphMany(ParcelPayment::class, 'payable');
    }






//    public function builtAssets() { return $this->hasMany(BuiltAsset::class); }
    public function planningRequisitions() { return $this->hasMany(PlanningRequisition::class); }
//    public function wayleaves() { return $this->hasMany(Wayleave::class); }
//    public function stakeholders() { return $this->belongsToMany(Stakeholder::class)->withPivot('role'); }
//    public function documents() { return $this->hasMany(ParcelDocument::class); }
//    public function payments() { return $this->hasMany(ParcelPayment::class); }
    public function disputes() { return $this->hasMany(ParcelDispute::class); }
//    public function complianceRecords() { return $this->hasMany(ComplianceRecord::class); }

}
