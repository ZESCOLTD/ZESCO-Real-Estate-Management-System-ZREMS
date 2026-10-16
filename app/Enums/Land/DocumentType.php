<?php

namespace App\Enums\Land;

use App\HasEnumHelpers;

enum DocumentType: int
{
    use HasEnumHelpers;

    case TitleDeed = 1;
    case SaleAgreement = 2;
    case PurchaseOrder = 3;
    case Invoice = 4;
    case Receipt = 5;
    case ValuationReport = 6;
    case InspectionReport = 7;
    case InsuranceCertificate = 8;
    case TaxCertificate = 9;
    case RegistrationCertificate = 10;
    case LeaseAgreement = 11;
    case MaintenanceReport = 12;
    case WarrantyDocument = 13;
    case ComplianceCertificate = 14;
    case AssetPhoto = 15;
    case Blueprint = 16;
    case Contract = 17;
    case ApprovalDocument = 18;
    case DisposalDocument = 19;
    case TransferDocument = 20;
    case Other = 21;

    public function label(): string
    {
        return match ($this) {
            self::TitleDeed => 'Title Deed',
            self::SaleAgreement => 'Sale Agreement',
            self::PurchaseOrder => 'Purchase Order',
            self::Invoice => 'Invoice',
            self::Receipt => 'Receipt',
            self::ValuationReport => 'Valuation Report',
            self::InspectionReport => 'Inspection Report',
            self::InsuranceCertificate => 'Insurance Certificate',
            self::TaxCertificate => 'Tax Certificate',
            self::RegistrationCertificate => 'Registration Certificate',
            self::LeaseAgreement => 'Lease Agreement',
            self::MaintenanceReport => 'Maintenance Report',
            self::WarrantyDocument => 'Warranty Document',
            self::ComplianceCertificate => 'Compliance Certificate',
            self::AssetPhoto => 'Asset Photo',
            self::Blueprint => 'Blueprint',
            self::Contract => 'Contract',
            self::ApprovalDocument => 'Approval Document',
            self::DisposalDocument => 'Disposal Document',
            self::TransferDocument => 'Transfer Document',
            self::Other => 'Other',
        };
    }
}
