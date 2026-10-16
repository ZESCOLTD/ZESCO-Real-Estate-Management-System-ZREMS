<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Unguarded;

#[Unguarded]
class GisLandBoundary extends Model
{
    protected $table = 'gis_land_boundaries';

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function land(): BelongsTo
    {
        return $this->belongsTo(GisLand::class, 'gis_land_id');
    }

    public function points(): HasMany
    {
        return $this->hasMany(GisBoundaryPoint::class)->orderBy('sequence');
    }

    public function getProjectionLabelAttribute(): string
    {
        return match ($this->projection) {
            'arc1950_utm35s' => 'Arc 1950 / UTM Zone 35S',
            'arc1950_utm36s' => 'Arc 1950 / UTM Zone 36S',
            'wgs84_utm35s'   => 'WGS84 / UTM Zone 35S',
            'wgs84_utm36s'   => 'WGS84 / UTM Zone 36S',
            'wgs84'          => 'WGS84 Geographic',
            default          => $this->projection,
        };
    }
}
