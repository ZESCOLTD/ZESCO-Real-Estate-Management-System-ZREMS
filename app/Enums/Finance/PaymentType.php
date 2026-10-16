<?php

namespace App\Enums\Finance;

use App\HasEnumHelpers;

enum PaymentType: int
{
    use HasEnumHelpers;

    case Purchase = 1;
    case DownPayment = 2;
    case Installment = 3;
    case LeasePayment = 4;
    case RentalPayment = 5;
    case Maintenance = 6;
    case Repair = 7;
    case Insurance = 8;
    case Registration = 9;
    case LicenseFee = 10;
    case Tax = 11;
    case Transport = 12;
    case Installation = 13;
    case Upgrade = 14;
    case DisposalCost = 15;
    case Penalty = 16;
    case Refund = 17;
    case Other = 18;

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::DownPayment => 'Down Payment',
            self::Installment => 'Installment',
            self::LeasePayment => 'Lease Payment',
            self::RentalPayment => 'Rental Payment',
            self::Maintenance => 'Maintenance',
            self::Repair => 'Repair',
            self::Insurance => 'Insurance',
            self::Registration => 'Registration',
            self::LicenseFee => 'License Fee',
            self::Tax => 'Tax',
            self::Transport => 'Transport',
            self::Installation => 'Installation',
            self::Upgrade => 'Upgrade',
            self::DisposalCost => 'Disposal Cost',
            self::Penalty => 'Penalty',
            self::Refund => 'Refund',
            self::Other => 'Other',
        };
    }
}
