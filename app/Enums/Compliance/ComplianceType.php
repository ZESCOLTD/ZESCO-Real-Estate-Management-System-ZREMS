<?php

namespace App\Enums\Compliance;

use App\HasEnumHelpers;

enum ComplianceType: int
{
    use HasEnumHelpers;
    case Environmental = 1;
    case Planning = 2;
    case Lease = 3;
    case Safety = 4;
    case Regulatory = 5;
    case InternalPolicy = 6;

    public function label(): string
    {
        return match ($this) {
            self::Environmental => 'Environmental',
            self::Planning => 'Planning',
            self::Lease => 'Lease',
            self::Safety => 'Safety',
            self::Regulatory => 'Regulatory',
            self::InternalPolicy => 'Internal Policy',
        };
    }
}
