<?php
/**
 * Seed Property Department → Disposal → Disposal of Property (child #241)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $parent = ServiceParent::where('department_name', 'Property Department')
        ->where('service_name', 'Disposal')
        ->first();
    if (!$parent) { throw new RuntimeException('Parent Property Department / Disposal not found'); }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Disposal of Property'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Disposal of Property',
            'description' => 'ZESCO Limited — Disposal of Property Application, Valuation, Due Diligence, Approvals and Completion.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];
    $approvedNot = ['Approved', 'Not Approved'];
    $propertyTypes = ['Land', 'House', 'Commercial Building', 'Farm', 'Other'];
    $disposalReasons = [
        'Surplus to requirements',
        'Uneconomical to maintain',
        'Obsolete for organizational needs',
        'Exchange for another property',
        'Redevelopment requirements',
        'Relocation of operations',
        'Court order / Government directive',
        'Gift',
        'Other',
    ];
    $occupancy = ['Vacant', 'Occupied by Staff', 'Occupied by Tenant', 'Occupied by Other Parties'];
    $ratesStatus = ['Up to Date', 'Outstanding'];
    $disposalMethods = [
        'Public Auction',
        'Open Tender',
        'Private Treaty',
        'Transfer to Government Institution',
        'Donation',
        'Exchange/Swap',
        'Demolition and Sale of Materials',
        'Other',
    ];

    $fields = [
        // A. GENERAL INFORMATION
        ['s' => 'A. General Information', 'l' => 'Disposal Reference No.', 'n' => 'disposal_reference_no', 't' => 'text', 'r' => 1],
        ['s' => 'A. General Information', 'l' => 'Date of Application', 'n' => 'date_of_application', 't' => 'date', 'r' => 1],
        ['s' => 'A. General Information', 'l' => 'Department / Unit', 'n' => 'department_unit', 't' => 'text', 'r' => 1],
        ['s' => 'A. General Information', 'l' => 'Responsible Officer', 'n' => 'responsible_officer', 't' => 'text', 'r' => 1],
        ['s' => 'A. General Information', 'l' => 'Position', 'n' => 'responsible_officer_position', 't' => 'text', 'r' => 1],
        ['s' => 'A. General Information', 'l' => 'Contact Details', 'n' => 'responsible_officer_contact', 't' => 'text', 'r' => 1],

        // B. ASSET DETAILS
        ['s' => 'B. Asset Details', 'l' => 'Property Name / Description', 'n' => 'property_name_description', 't' => 'textarea', 'r' => 1],
        ['s' => 'B. Asset Details', 'l' => 'Property Type', 'n' => 'property_type', 't' => 'select', 'r' => 1, 'o' => $propertyTypes],
        ['s' => 'B. Asset Details', 'l' => 'Property Type (Other - specify)', 'n' => 'property_type_other', 't' => 'text', 'r' => 0],
        ['s' => 'B. Asset Details', 'l' => 'Property Location', 'n' => 'property_location', 't' => 'text', 'r' => 1],
        ['s' => 'B. Asset Details', 'l' => 'Title Number / Property Reference', 'n' => 'title_number_property_reference', 't' => 'text', 'r' => 1],
        ['s' => 'B. Asset Details', 'l' => 'Plot Number', 'n' => 'plot_number', 't' => 'text', 'r' => 1],
        ['s' => 'B. Asset Details', 'l' => 'Size / Extent', 'n' => 'size_extent', 't' => 'text', 'r' => 1],
        ['s' => 'B. Asset Details', 'l' => 'Acquisition Date', 'n' => 'acquisition_date', 't' => 'date', 'r' => 0],
        ['s' => 'B. Asset Details', 'l' => 'Original Purchase Cost (K)', 'n' => 'original_purchase_cost', 't' => 'number', 'r' => 0],
        ['s' => 'B. Asset Details', 'l' => 'Current Book Value (K)', 'n' => 'current_book_value', 't' => 'number', 'r' => 0],
        ['s' => 'B. Asset Details', 'l' => 'Estimated Market Value (K)', 'n' => 'estimated_market_value', 't' => 'number', 'r' => 0],

        // C. REASON FOR DISPOSAL
        ['s' => 'C. Reason for Disposal', 'l' => 'Reason for Disposal', 'n' => 'reason_for_disposal', 't' => 'select', 'r' => 1, 'o' => $disposalReasons],
        ['s' => 'C. Reason for Disposal', 'l' => 'Reason (Other - specify)', 'n' => 'reason_for_disposal_other', 't' => 'text', 'r' => 0],
        ['s' => 'C. Reason for Disposal', 'l' => 'Detailed Justification for Disposal', 'n' => 'detailed_justification', 't' => 'textarea', 'r' => 1],

        // D. PROPERTY STATUS
        ['s' => 'D. Property Status', 'l' => 'Title Deed Available', 'n' => 'title_deed_available', 't' => 'checkbox', 'r' => 0],
        ['s' => 'D. Property Status', 'l' => 'Lease Agreement Available', 'n' => 'lease_agreement_available', 't' => 'checkbox', 'r' => 0],
        ['s' => 'D. Property Status', 'l' => 'No Encumbrances', 'n' => 'no_encumbrances', 't' => 'checkbox', 'r' => 0],
        ['s' => 'D. Property Status', 'l' => 'Encumbrances Exist (specify)', 'n' => 'encumbrances_specify', 't' => 'textarea', 'r' => 0],
        ['s' => 'D. Property Status', 'l' => 'Occupancy Status', 'n' => 'occupancy_status', 't' => 'select', 'r' => 1, 'o' => $occupancy],
        ['s' => 'D. Property Status', 'l' => 'Occupancy Details', 'n' => 'occupancy_details', 't' => 'textarea', 'r' => 0],
        ['s' => 'D. Property Status', 'l' => 'Rates and Taxes Status', 'n' => 'rates_taxes_status', 't' => 'select', 'r' => 1, 'o' => $ratesStatus],
        ['s' => 'D. Property Status', 'l' => 'Amount Outstanding (K)', 'n' => 'rates_amount_outstanding', 't' => 'number', 'r' => 0],

        // E. VALUATION DETAILS
        ['s' => 'E. Valuation Details', 'l' => 'Valuation Date', 'n' => 'valuation_date', 't' => 'date', 'r' => 1],
        ['s' => 'E. Valuation Details', 'l' => 'Valuer / Valuation Firm', 'n' => 'valuer_firm', 't' => 'text', 'r' => 1],
        ['s' => 'E. Valuation Details', 'l' => 'Valuation Amount (K)', 'n' => 'valuation_amount', 't' => 'number', 'r' => 1],
        ['s' => 'E. Valuation Details', 'l' => 'Valuation Report Attached', 'n' => 'valuation_report_attached', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => 'E. Valuation Details', 'l' => 'Valuation Report Upload', 'n' => 'valuation_report_upload', 't' => 'file', 'r' => 0],

        // F. RECOMMENDED METHOD OF DISPOSAL
        ['s' => 'F. Recommended Method of Disposal', 'l' => 'Method of Disposal', 'n' => 'method_of_disposal', 't' => 'select', 'r' => 1, 'o' => $disposalMethods],
        ['s' => 'F. Recommended Method of Disposal', 'l' => 'Method of Disposal (Other - specify)', 'n' => 'method_of_disposal_other', 't' => 'text', 'r' => 0],
        ['s' => 'F. Recommended Method of Disposal', 'l' => 'Expected Disposal Value (K)', 'n' => 'expected_disposal_value', 't' => 'number', 'r' => 1],

        // G. DUE DILIGENCE CHECKLIST
        ['s' => 'G. Due Diligence Checklist', 'l' => 'Due Diligence Checklist', 'n' => 'due_diligence_checklist', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Requirement', 'Yes', 'No', 'Remarks'],
                    'rows' => [
                        'Physical Inspection Conducted',
                        'Ownership Verified',
                        'Title Verified',
                        'Valuation Conducted',
                        'Encumbrance Search Conducted',
                        'Environmental Issues Assessed',
                        'Planning / Zoning Status Confirmed',
                        'Board Approval Obtained',
                    ]]],

        // H. RECOMMENDATION
        ['s' => 'H. Recommendation', 'l' => 'Recommended By - Name', 'n' => 'recommended_by_name', 't' => 'text', 'r' => 1],
        ['s' => 'H. Recommendation', 'l' => 'Recommended By - Position', 'n' => 'recommended_by_position', 't' => 'text', 'r' => 1],
        ['s' => 'H. Recommendation', 'l' => 'Recommended By - Signature', 'n' => 'recommended_by_signature', 't' => 'signature', 'r' => 0],
        ['s' => 'H. Recommendation', 'l' => 'Recommended By - Date', 'n' => 'recommended_by_date', 't' => 'date', 'r' => 0],

        // I. APPROVALS
        ['s' => 'I. Approvals - Head of Department', 'l' => 'HOD Name', 'n' => 'hod_name', 't' => 'text', 'r' => 0],
        ['s' => 'I. Approvals - Head of Department', 'l' => 'HOD Signature', 'n' => 'hod_signature', 't' => 'signature', 'r' => 0],
        ['s' => 'I. Approvals - Head of Department', 'l' => 'HOD Date', 'n' => 'hod_date', 't' => 'date', 'r' => 0],
        ['s' => 'I. Approvals - Head of Department', 'l' => 'HOD Recommendation', 'n' => 'hod_recommendation', 't' => 'select', 'r' => 0, 'o' => $approvedNot],

        ['s' => 'I. Approvals - Asset Management Committee', 'l' => 'AMC Resolution No.', 'n' => 'amc_resolution_no', 't' => 'text', 'r' => 0],
        ['s' => 'I. Approvals - Asset Management Committee', 'l' => 'AMC Date', 'n' => 'amc_date', 't' => 'date', 'r' => 0],
        ['s' => 'I. Approvals - Asset Management Committee', 'l' => 'AMC Recommendation', 'n' => 'amc_recommendation', 't' => 'select', 'r' => 0, 'o' => $approvedNot],

        ['s' => 'I. Approvals - Controlling Officer / CEO', 'l' => 'CEO Name', 'n' => 'ceo_name', 't' => 'text', 'r' => 0],
        ['s' => 'I. Approvals - Controlling Officer / CEO', 'l' => 'CEO Signature', 'n' => 'ceo_signature', 't' => 'signature', 'r' => 0],
        ['s' => 'I. Approvals - Controlling Officer / CEO', 'l' => 'CEO Date', 'n' => 'ceo_date', 't' => 'date', 'r' => 0],
        ['s' => 'I. Approvals - Controlling Officer / CEO', 'l' => 'CEO Decision', 'n' => 'ceo_decision', 't' => 'select', 'r' => 0, 'o' => $approvedNot],

        // J. DISPOSAL COMPLETION
        ['s' => 'J. Disposal Completion', 'l' => 'Actual Disposal Date', 'n' => 'actual_disposal_date', 't' => 'date', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Purchaser / Beneficiary', 'n' => 'purchaser_beneficiary', 't' => 'text', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Disposal Method Used', 'n' => 'disposal_method_used', 't' => 'text', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Actual Disposal Amount (K)', 'n' => 'actual_disposal_amount', 't' => 'number', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Receipt Number', 'n' => 'receipt_number', 't' => 'text', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Comments', 'n' => 'completion_comments', 't' => 'textarea', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Prepared By - Name', 'n' => 'prepared_by_name', 't' => 'text', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Prepared By - Signature', 'n' => 'prepared_by_signature', 't' => 'signature', 'r' => 0],
        ['s' => 'J. Disposal Completion', 'l' => 'Prepared By - Date', 'n' => 'prepared_by_date', 't' => 'date', 'r' => 0],

        // ATTACHMENTS
        ['s' => 'K. Attachments', 'l' => 'Copy of Title Deed / Lease', 'n' => 'attach_title_deed_lease', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Property Valuation Report', 'n' => 'attach_valuation_report', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Site Inspection Report', 'n' => 'attach_site_inspection', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Photographs of Property', 'n' => 'attach_photographs', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Rates Clearance Certificate', 'n' => 'attach_rates_clearance', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Encumbrance / Search Report', 'n' => 'attach_encumbrance_search', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Board / Committee Approval Minutes', 'n' => 'attach_board_minutes', 't' => 'file', 'r' => 0],
        ['s' => 'K. Attachments', 'l' => 'Disposal Advertisement / Tender Documents', 'n' => 'attach_tender_documents', 't' => 'file', 'r' => 0],
    ];

    $order = 1;
    foreach ($fields as $f) {
        ServiceFormField::create([
            'service_form_template_id' => $template->id,
            'section' => $f['s'],
            'field_label' => $f['l'],
            'field_name' => $f['n'],
            'field_type' => $f['t'],
            'is_required' => $f['r'],
            'options' => isset($f['o']) ? json_encode($f['o']) : null,
            'sort_order' => $order++,
        ]);
    }

    echo "OK  parent=#{$parent->id}  child=#{$child->id}  template=#{$template->id}  fields=" . ($order - 1) . PHP_EOL;
});
