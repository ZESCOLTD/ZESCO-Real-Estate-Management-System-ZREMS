<?php

namespace App\Enums\Dispute;

use App\HasEnumHelpers;

enum DisputeStatus: int
{
    use HasEnumHelpers;

    case Open = 1;
    case InProgress = 2;
    case Resolved = 3;
    case Closed = 4;

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
        };
    }
}
