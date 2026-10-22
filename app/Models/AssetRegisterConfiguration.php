<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class AssetRegisterConfiguration extends Model
{
    public const FIELDS = [
        'business_unit' => 'BU',
        'cost_centre' => 'CC',
        'area' => 'Area',
        'location' => 'Location',
        'sub_location' => 'Sub Location',
        'major_category' => 'Major Category',
        'sub_major_category' => 'Sub Major Category',
        'minor_category' => 'Minor Category',
        'status' => 'Status',
        'reportline' => 'Reportline',
    ];

    protected $fillable = [
        'field_key',
        'value',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    public static function valuesFor(string $fieldKey): Collection
    {
        if (! array_key_exists($fieldKey, self::FIELDS)) {
            return collect();
        }

        $configured = self::query()
            ->where('field_key', $fieldKey)
            ->get(['value', 'is_active']);
        $configuredValues = $configured->pluck('value')->all();

        $configuredActive = $configured
            ->where('is_active', true)
            ->pluck('value');

        $existingAssetValues = AssetRegister::query()
            ->whereNotNull($fieldKey)
            ->distinct()
            ->orderBy($fieldKey)
            ->pluck($fieldKey)
            ->reject(fn ($value) => in_array($value, $configuredValues, true));

        return $configuredActive
            ->merge($existingAssetValues)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->unique()
            ->values();
    }
}
