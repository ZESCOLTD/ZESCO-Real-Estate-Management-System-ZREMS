<?php

namespace App\Enums\Finance;

use App\HasEnumHelpers;

enum PaymentMode: int
{
    use HasEnumHelpers;

    case Cash = 1;
    case BankTransfer = 2;
    case MobileMoney = 3;
    case Cheque = 4;
    case POS = 5;
    case CreditCard = 6;
    case DebitCard = 7;
    case OnlinePayment = 8;
    case EFT = 9;
    case StandingOrder = 10;
    case DirectDebit = 11;
    case LoanFinancing = 12;
    case LeaseFinancing = 13;
    case Grant = 14;
    case Donation = 15;
    case Other = 16;

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Cash',
            self::BankTransfer => 'Bank Transfer',
            self::MobileMoney => 'Mobile Money',
            self::Cheque => 'Cheque',
            self::POS => 'POS',
            self::CreditCard => 'Credit Card',
            self::DebitCard => 'Debit Card',
            self::OnlinePayment => 'Online Payment',
            self::EFT => 'EFT',
            self::StandingOrder => 'Standing Order',
            self::DirectDebit => 'Direct Debit',
            self::LoanFinancing => 'Loan Financing',
            self::LeaseFinancing => 'Lease Financing',
            self::Grant => 'Grant',
            self::Donation => 'Donation',
            self::Other => 'Other',
        };
    }
}
