<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\Unguarded;

#[Unguarded]
class GisLand extends Model
{
    use SoftDeletes;

    protected $table = 'gis_lands';

    protected $casts = [
        'acquisition_date' => 'date',
        'area'             => 'decimal:6',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function boundaries(): HasMany
    {
        return $this->hasMany(GisLandBoundary::class);
    }

    public function activeBoundary(): HasOne
    {
        return $this->hasOne(GisLandBoundary::class)->where('is_active', true)->latestOfMany();
    }

    public function surveyImports(): HasMany
    {
        return $this->hasMany(GisSurveyImport::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function serviceRequestService(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestService::class, 'service_request_service_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'acquired'       => '#198754',
            'negotiations'    => '#ffc107',
            'disputed'       => '#dc3545',
            'survey_pending' => '#0d6efd',
            'archived'       => '#6c757d',
            default          => '#6c757d',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'acquired'       => 'Acquired',
            'negotiations'    => 'Negotiation',
            'disputed'       => 'Disputed',
            'survey_pending' => 'Survey Pending',
            'archived'       => 'Archived',
            default          => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'acquired'       => 'badge-success',
            'negotiations'    => 'badge-warning',
            'disputed'       => 'badge-danger',
            'survey_pending' => 'badge-info',
            'archived'       => 'badge-secondary',
            default          => 'badge-secondary',
        };
    }

    public function getHasCoordinatesAttribute(): bool
    {
        return $this->activeBoundary?->points()->exists() ?? false;
    }

    public function toGeoJsonFeature(): array
    {
        $boundary = $this->relationLoaded('boundaries')
            ? $this->boundaries->where('is_active', true)->first()
            : $this->activeBoundary;

        $coords = [];
        if ($boundary && $boundary->relationLoaded('points')) {
            $coords = $boundary->points
                ->sortBy('sequence')
                ->map(fn($p) => [(float) $p->longitude, (float) $p->latitude])
                ->values()
                ->toArray();
            if (!empty($coords)) {
                $coords[] = $coords[0]; // close ring
            }
        }

        return [
            'type'       => 'Feature',
            'geometry'   => [
                'type'        => 'Polygon',
                'coordinates' => [$coords],
            ],
            'properties' => [
                'id'               => $this->id,
                'plot_number'      => $this->plot_number,
                'property_name'    => $this->property_name,
                'owner'            => $this->owner ?? 'N/A',
                'area'             => $this->area ? number_format((float) $this->area, 4) . ' ha' : 'N/A',
                'status'           => $this->status,
                'status_label'     => $this->status_label,
                'status_color'     => $this->status_color,
                'province'         => $this->province ?? 'N/A',
                'district'         => $this->district ?? 'N/A',
                'chiefdom'         => $this->chiefdom ?? 'N/A',
                'purpose'          => $this->purpose ?? 'N/A',
                'acquisition_date' => $this->acquisition_date
                    ? $this->acquisition_date->format('d M Y') : 'N/A',
            ],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeSearch($query, string $term)
    {
        if (empty($term)) return $query;
        return $query->where(function ($q) use ($term) {
            $q->where('plot_number', 'like', "%{$term}%")
              ->orWhere('property_name', 'like', "%{$term}%")
              ->orWhere('owner', 'like', "%{$term}%")
              ->orWhere('district', 'like', "%{$term}%")
              ->orWhere('province', 'like', "%{$term}%")
              ->orWhere('survey_reference', 'like', "%{$term}%");
        });
    }

    public static function statusOptions(): array
    {
        return [
            'acquired'       => 'Acquired',
            'negotiations'    => 'Negotiation',
            'disputed'       => 'Disputed',
            'survey_pending' => 'Survey Pending',
            'archived'       => 'Archived',
        ];
    }
}
