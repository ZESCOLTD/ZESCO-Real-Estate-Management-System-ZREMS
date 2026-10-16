<?php

namespace App\Enums\Compliance;

use App\HasEnumHelpers;

enum EnvironmentalCategory: int
{
    use HasEnumHelpers;
    case AirQuality = 1;
    case WaterQuality = 2;
    case WasteManagement = 3;
    case EnergyEfficiency = 4;
    case Biodiversity = 5;

    public function label(): string
    {
        return match ($this) {
            self::AirQuality => 'Air Quality',
            self::WaterQuality => 'Water Quality',
            self::WasteManagement => 'Waste Management',
            self::EnergyEfficiency => 'Energy Efficiency',
            self::Biodiversity => 'Biodiversity',
        };
    }
}
