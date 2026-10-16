<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FmsAssetRegister extends Model
{
    protected $table = 'fms_asset_register';

    protected $guarded = ['lams_asset_number'];

//    protected $fillable = [
//        'asset_number',
//        'lams_asset_number',
//        'business_unit',
//        'cost_centre',
//        'tag_number',
//        'serial_number',
//        'asset_natural_account',
//        'deprn_reserve_account',
//        'deprn_charge_account',
//        'description',
//        'link_id',
//        'major_category',
//        'sub_major_category',
//        'minor_category',
//        'sub_minor_category',
//        'units',
//        'life_in_months',
//        'date_placed_in_service',
//        'town',
//        'location_2',
//        'dept',
//    ];


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

    public function assetDocuments(): HasMany
    {
        return $this->hasMany(AssetDocument::class,'asset_id','id');
    }

    public function assetPayments(): HasMany
    {
        return $this->hasMany(AssetPayment::class,'asset_id','id');
    }

    public function assetDisputes(): HasMany
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
            'asset_stakeholders',
            'land_asset_id',
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
}
