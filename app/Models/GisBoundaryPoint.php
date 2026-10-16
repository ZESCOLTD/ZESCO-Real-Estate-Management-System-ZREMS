<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Unguarded;

#[Unguarded]
class GisBoundaryPoint extends Model
{
    protected $table = 'gis_boundary_points';

    protected $casts = [
        'easting'   => 'decimal:4',
        'northing'  => 'decimal:4',
        'latitude'  => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function boundary(): BelongsTo
    {
        return $this->belongsTo(GisLandBoundary::class, 'gis_land_boundary_id');
    }

    public function toCoordinateArray(): array
    {
        return [
            'point_number' => $this->point_number,
            'sequence'     => $this->sequence,
            'easting'      => (float) $this->easting,
            'northing'     => (float) $this->northing,
            'latitude'     => (float) $this->latitude,
            'longitude'    => (float) $this->longitude,
        ];
    }
}
