<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum ParcelStatus: int
{
    use HasEnumHelpers;

    case Active = 1;
    case Inactive = 2;
    case Disputed = 3;
    case UnderReview = 4;
    case Archived = 5;

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Disputed => 'Disputed',
            self::UnderReview => 'Under Review',
            self::Archived => 'Archived',
        };
    }
}
