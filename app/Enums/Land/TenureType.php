<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum TenureType: int
{
    use HasEnumHelpers;
    case Freehold = 1;
    case Leasehold = 2;
    case Commonhold = 3;
    case Private = 4;
    case State = 5;
    case Customary = 6;

    public function label(): string
    {
        return match ($this) {
            self::Freehold => 'Freehold',
            self::Leasehold => 'Leasehold',
            self::Commonhold => 'Commonhold',
            self::Private => 'Private',
            self::State => 'State',
            self::Customary => 'Customary',
        };
    }
}
