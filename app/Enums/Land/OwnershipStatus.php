<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum OwnershipStatus: int
{
    use HasEnumHelpers;
    case Leasehold = 1;
    case Customary = 2;

    public function label(): string
    {
        return match ($this) {
            self::Leasehold => 'Leasehold',
            self::Customary => 'Customary',
        };
    }
}
