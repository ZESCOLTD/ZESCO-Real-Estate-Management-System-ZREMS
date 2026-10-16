<?php

namespace App\Enums\Compliance;

use App\HasEnumHelpers;

enum ComplianceStatus: int
{
    use HasEnumHelpers;
    case Compliant = 1;
    case NonCompliant = 2;
    case Pending = 3;
    case Exempt = 4;

    public function label(): string
    {
        return match ($this) {
            self::Compliant => 'Compliant',
            self::NonCompliant => 'Non-Compliant',
            self::Pending => 'Pending',
            self::Exempt => 'Exempt',
        };
    }
}
