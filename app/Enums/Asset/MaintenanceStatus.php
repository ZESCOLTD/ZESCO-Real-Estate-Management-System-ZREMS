<?php

namespace App\Enums\Asset;

use App\HasEnumHelpers;

enum MaintenanceStatus: int
{
    use HasEnumHelpers;

    case UpToDate = 1;
    case DueSoon = 2;
    case Overdue = 3;

    public function label(): string
    {
        return match ($this) {
            self::UpToDate => 'Up to Date',
            self::DueSoon => 'Due Soon',
            self::Overdue => 'Overdue',
        };
    }
}
