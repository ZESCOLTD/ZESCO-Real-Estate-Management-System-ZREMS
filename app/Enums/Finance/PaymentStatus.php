<?php

namespace App\Enums\Finance;

enum PaymentStatus: int
{
    case Pending = 1;
    case Completed = 2;
    case Failed = 3;
    case Refunded = 4;

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Refunded => 'Refunded',
        };
    }
}
