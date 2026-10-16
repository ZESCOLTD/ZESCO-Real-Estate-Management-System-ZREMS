<?php

namespace App\Enums\Asset;

use App\HasEnumHelpers;

enum AssetCondition: int
{
    use HasEnumHelpers;
    case Excellent = 1;
    case Good = 2;
    case Fair = 3;
    case Poor = 4;
    case Critical = 5;
    case NeedsRepair = 6;

    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Good => 'Good',
            self::Fair => 'Fair',
            self::Poor => 'Poor',
            self::Critical => 'Critical',
            self::NeedsRepair => 'Needs Repair',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Excellent => 'green',
            self::Good => 'blue',
            self::Fair => 'yellow',
            self::Poor => 'orange',
            self::Critical, self::NeedsRepair => 'red',
        };
    }
}
