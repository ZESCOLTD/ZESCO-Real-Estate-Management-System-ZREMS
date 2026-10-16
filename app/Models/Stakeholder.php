<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
#[Unguarded]
class Stakeholder extends Model
{
    use SoftDeletes;

    public function landParcels(): BelongsToMany
    {
        return $this->belongsToMany(
            LandParcel::class,
            'parcel_stakeholders',
            'stakeholder_id',
            'land_parcel_id'
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
