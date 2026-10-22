<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TenantRegister extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'unit_key', 'serial_no', 'station_location', 'category', 'current_tenant', 'man_no',
        'employer', 'location', 'physical_address', 'area_m2', 'bedrooms',
        'open_closed_verandah_m2', 'garage_m2', 'servants_quarter_m2', 'condition',
        'current_rental_charge', 'market_rental_charge_gvd', 'proposed_rental_charge_50_percent',
        'proposed_rentals_after_verification', 'proposed_percentage_increment', 'water_charges',
        'sewer_charges', 'proposed_total_amount', 'source_row', 'last_import_job_id', 'imported_at',
    ];

    protected $casts = [
        'area_m2' => 'decimal:2',
        'bedrooms' => 'integer',
        'open_closed_verandah_m2' => 'decimal:2',
        'garage_m2' => 'decimal:2',
        'servants_quarter_m2' => 'decimal:2',
        'current_rental_charge' => 'decimal:2',
        'market_rental_charge_gvd' => 'decimal:2',
        'proposed_rental_charge_50_percent' => 'decimal:2',
        'proposed_rentals_after_verification' => 'decimal:2',
        'proposed_percentage_increment' => 'decimal:2',
        'water_charges' => 'decimal:2',
        'sewer_charges' => 'decimal:2',
        'proposed_total_amount' => 'decimal:2',
        'source_row' => 'array',
        'imported_at' => 'datetime',
    ];

    public static function unitKeyFor(array $attributes): ?string
    {
        $station = mb_strtolower(trim((string) ($attributes['station_location'] ?? '')));
        $category = mb_strtolower(trim((string) ($attributes['category'] ?? '')));
        $location = mb_strtolower(trim((string) ($attributes['location'] ?? '')));
        $address = mb_strtolower(trim((string) ($attributes['physical_address'] ?? '')));
        $serial = mb_strtolower(trim((string) ($attributes['serial_no'] ?? '')));

        if ($station === '' || $category === '' || ($address === '' && $serial === '')) {
            return null;
        }

        return hash('sha256', implode('|', [$station, $category, $location, $serial, $address]));
    }
}