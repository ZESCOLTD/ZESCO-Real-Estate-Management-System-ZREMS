<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum Unit: int
{
    use HasEnumHelpers;

    case Hectare = 1;
    case Acre = 2;
    case SquareMeter = 3;
    case SquareKilometer = 4;
    case SquareFoot = 5;
    case SquareYard = 6;

    public function label(): string
    {
        return match ($this) {
            self::Hectare => 'Hectares (Ha)',
            self::Acre => 'Acres',
            self::SquareMeter => 'Square Metres (m²)',
            self::SquareKilometer => 'Square Kilometres (km²)',
            self::SquareFoot => 'Square Feet (ft²)',
            self::SquareYard => 'Square Yards (yd²)',
        };
    }
}
