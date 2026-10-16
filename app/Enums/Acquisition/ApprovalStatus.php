<?php

namespace App\Enums\Acquisition;

use App\HasEnumHelpers;

enum ApprovalStatus: int
{
    use HasEnumHelpers;

    case Pending = 1;
    case Approved = 2;
    case Rejected = 3;
    case UnderReview = 4;
    case Reverted = 5;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Reverted => 'Reverted',
            self::UnderReview => 'Under Review',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::UnderReview => 'gray',
            self::Pending => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
            self::Reverted => 'yellow',
        };
    }
    public function badgeClass(): string
    {
        return match ($this->color()) {
            'green' => 'success',
            'red' => 'danger',
            'yellow' => 'warning',
            'gray' => 'secondary',
            default => 'info',
        };
    }
}
