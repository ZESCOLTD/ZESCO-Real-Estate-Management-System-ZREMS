<?php

namespace App\Enums\Acquisition;

use App\HasEnumHelpers;

enum AcquisitionType: int
{
    use HasEnumHelpers;
    case Purchase = 1;
    case Lease = 2;

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Lease => 'Lease',
        };
    }
}
