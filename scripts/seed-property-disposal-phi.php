<?php
/**
 * Seed Property Department → Disposal → Disposal of PHI (child #245)
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Disposal of PHI'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'PHI Disposal',
            'description' => 'ZESCO Limited — Personal Housing Initiative (PHI) Disposal Form.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];
    $yesNoNA = ['Yes', 'No', 'N/A'];
    $paymentStatus = ['Fully Paid', 'Partially Paid', 'Outstanding', 'Payment Records Require Reconciliation'];
    $titleStatus = ["Registered in ZESCO's Name", "Lease/Interest Registered in ZESCO's Name", 'Other'];
    $conveyStatus = ['Transfer Not Commenced', 'Documents Being Prepared', 'Awaiting Execution', 'Awaiting Consent/Approval', 'Lodged for Registration', 'Query Raised by Registry', 'Registered', 'Other'];
    $disposalStatus = ['Disposal Pending Completion', 'Transfer Registered – Disposal Completed', 'Disposal Subject to Outstanding Conditions', 'Other'];

    $fields = [
        // 1. DISPOSAL IDENTIFICATION
        ['s' => '1. Disposal Identification', 'l' => 'Reference No.', 'n' => 'reference_no', 't' => 'text', 'r' => 1],
        ['s' => '1. Disposal Identification', 'l' => 'Officer Responsible', 'n' => 'officer_responsible', 't' => 'text', 'r' => 1],
        ['s' => '1. Disposal Identification', 'l' => 'Designation', 'n' => 'officer_designation', 't' => 'text', 'r' => 1],

        // 2. PROPERTY DETAILS
        ['s' => '2. Property Details', 'l' => 'House / Plot / Stand No.', 'n' => 'plot_stand_no', 't' => 'text', 'r' => 1],
        ['s' => '2. Property Details', 'l' => 'Certificate of Title No.', 'n' => 'certificate_of_title_no', 't' => 'text', 'r' => 0],
        ['s' => '2. Property Details', 'l' => 'Property Description', 'n' => 'property_description', 't' => 'textarea', 'r' => 1],
        ['s' => '2. Property Details', 'l' => 'Street Name', 'n' => 'street_name', 't' => 'text', 'r' => 0],
        ['s' => '2. Property Details', 'l' => 'District', 'n' => 'district', 't' => 'text', 'r' => 1],
        ['s' => '2. Property Details', 'l' => 'Province', 'n' => 'province', 't' => 'text', 'r' => 1],
        ['s' => '2. Property Details', 'l' => 'Land Extent', 'n' => 'land_extent', 't' => 'text', 'r' => 0],
        ['s' => '2. Property Details', 'l' => 'Current Registered Owner', 'n' => 'current_registered_owner', 't' => 'text', 'r' => 1],

        // 3. PURCHASER DETAILS
        ['s' => '3. Purchaser Details', 'l' => 'Full Name of Purchaser', 'n' => 'purchaser_full_name', 't' => 'text', 'r' => 1],
        ['s' => '3. Purchaser Details', 'l' => 'Employee / Staff No.', 'n' => 'employee_staff_no', 't' => 'text', 'r' => 1],
        ['s' => '3. Purchaser Details', 'l' => 'NRC No.', 'n' => 'purchaser_nrc_no', 't' => 'text', 'r' => 1],
        ['s' => '3. Purchaser Details', 'l' => 'Postal / Physical Address', 'n' => 'purchaser_address', 't' => 'textarea', 'r' => 0],
        ['s' => '3. Purchaser Details', 'l' => 'Telephone / Contact', 'n' => 'purchaser_contact', 't' => 'text', 'r' => 0],

        // 4. POLICY AND AUTHORITY FOR DISPOSAL
        ['s' => '4. Policy and Authority for Disposal', 'l' => 'Government Policy / Directive Under Which Disposal Was Undertaken', 'n' => 'government_policy_directive', 't' => 'textarea', 'r' => 1],
        ['s' => '4. Policy and Authority for Disposal', 'l' => 'Basis for Eligibility', 'n' => 'basis_for_eligibility', 't' => 'text', 'r' => 0],

        // 5. SALE DETAILS
        ['s' => '5. Sale Details', 'l' => 'Date of Sale / Offer', 'n' => 'date_of_sale_offer', 't' => 'date', 'r' => 1],
        ['s' => '5. Sale Details', 'l' => 'Date Contract of Sale Was Executed', 'n' => 'contract_of_sale_execution_date', 't' => 'date', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Purchase Price (K)', 'n' => 'purchase_price', 't' => 'number', 'r' => 1],
        ['s' => '5. Sale Details', 'l' => 'Deposit Required (K)', 'n' => 'deposit_required', 't' => 'number', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Deposit Paid (K)', 'n' => 'deposit_paid', 't' => 'number', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Outstanding Balance (K)', 'n' => 'outstanding_balance', 't' => 'number', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Payment Status', 'n' => 'payment_status', 't' => 'select', 'r' => 1, 'o' => $paymentStatus],
        ['s' => '5. Sale Details', 'l' => 'Date Purchase Price Fully Paid', 'n' => 'date_fully_paid', 't' => 'date', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Finance Confirmation / Receipt Reference', 'n' => 'finance_receipt_reference', 't' => 'text', 'r' => 0],
        ['s' => '5. Sale Details', 'l' => 'Proof of Payment Attached', 'n' => 'proof_of_payment_attached', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '5. Sale Details', 'l' => 'Proof of Payment Upload', 'n' => 'proof_of_payment_upload', 't' => 'file', 'r' => 0],

        // 7. TITLE AND LEGAL STATUS
        ['s' => '7. Title and Legal Status', 'l' => 'Registered Owner According to Current Title', 'n' => 'title_registered_owner', 't' => 'text', 'r' => 1],
        ['s' => '7. Title and Legal Status', 'l' => 'Current Title / Lease Status', 'n' => 'current_title_lease_status', 't' => 'select', 'r' => 1, 'o' => $titleStatus],
        ['s' => '7. Title and Legal Status', 'l' => 'Current Title / Lease Status (Other - specify)', 'n' => 'current_title_lease_status_other', 't' => 'text', 'r' => 0],
        ['s' => '7. Title and Legal Status', 'l' => 'Has Title Been Transferred to the Purchaser?', 'n' => 'title_transferred_to_purchaser', 't' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => '7. Title and Legal Status', 'l' => 'If No, Reason Title Has Not Passed', 'n' => 'reason_title_not_passed', 't' => 'textarea', 'r' => 0],
        ['s' => '7. Title and Legal Status', 'l' => 'Has a Contract of Sale Been Prepared?', 'n' => 'contract_of_sale_prepared', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '7. Title and Legal Status', 'l' => 'Has the Contract of Sale Been Executed?', 'n' => 'contract_of_sale_executed', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '7. Title and Legal Status', 'l' => 'Has the Transfer Been Lodged at the Lands and Deeds Registry?', 'n' => 'transfer_lodged_at_registry', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '7. Title and Legal Status', 'l' => 'If Not Lodged, Reason', 'n' => 'reason_not_lodged', 't' => 'textarea', 'r' => 0],
        ['s' => '7. Title and Legal Status', 'l' => 'Current Conveyancing Status', 'n' => 'current_conveyancing_status', 't' => 'select', 'r' => 1, 'o' => $conveyStatus],
        ['s' => '7. Title and Legal Status', 'l' => 'Current Conveyancing Status (Other - specify)', 'n' => 'current_conveyancing_status_other', 't' => 'text', 'r' => 0],

        // 11. CONVEYANCING AND TRANSFER ACTIONS
        ['s' => '11. Conveyancing and Transfer Actions', 'l' => 'Conveyancing Actions Log', 'n' => 'conveyancing_actions_log', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Action', 'Status', 'Reference / Date'],
                    'rows' => [
                        'Contract of Sale executed / verified',
                        'Proof of payment verified',
                        'Consent obtained',
                        'Assignment prepared',
                        'Assignment executed',
                        'Transfer lodged',
                        'Property Transfer Tax',
                        'Title transferred',
                        'ZESCO records updated',
                        'Asset Register updated',
                    ]]],
        ['s' => '11. Conveyancing and Transfer Actions', 'l' => 'Outstanding Conveyancing Actions', 'n' => 'outstanding_conveyancing_actions', 't' => 'textarea', 'r' => 0],

        // 12. DISPOSAL COMPLETION STATUS
        ['s' => '12. Disposal Completion Status', 'l' => 'Current Disposal Status', 'n' => 'current_disposal_status', 't' => 'select', 'r' => 1, 'o' => $disposalStatus],
        ['s' => '12. Disposal Completion Status', 'l' => 'Current Disposal Status (Other - specify)', 'n' => 'current_disposal_status_other', 't' => 'text', 'r' => 0],
        ['s' => '12. Disposal Completion Status', 'l' => 'Outstanding Conditions / Actions', 'n' => 'outstanding_conditions_actions', 't' => 'textarea', 'r' => 0],
        ['s' => '12. Disposal Completion Status', 'l' => 'Responsible Officer', 'n' => 'completion_responsible_officer', 't' => 'text', 'r' => 0],
        ['s' => '12. Disposal Completion Status', 'l' => 'Target Completion Date', 'n' => 'target_completion_date', 't' => 'date', 'r' => 0],

        // 14. DOCUMENT CHECKLIST
        ['s' => '14. Document Checklist', 'l' => 'Offer Letter', 'n' => 'doc_offer_letter', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Contract of Sale', 'n' => 'doc_contract_of_sale', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Payment Records / Receipts', 'n' => 'doc_payment_records', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Finance Reconciliation', 'n' => 'doc_finance_reconciliation', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Certificate of Title / Lease', 'n' => 'doc_certificate_of_title', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Survey Diagram', 'n' => 'doc_survey_diagram', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Site Plan', 'n' => 'doc_site_plan', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Legal Due-Diligence / Title Search', 'n' => 'doc_legal_due_diligence', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Legal Opinion (where applicable)', 'n' => 'doc_legal_opinion', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Consent', 'n' => 'doc_consent', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Assignment', 'n' => 'doc_assignment', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Handover Certificate', 'n' => 'doc_handover_certificate', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'PTT Certificate', 'n' => 'doc_ptt_certificate', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Updated Certificate of Title', 'n' => 'doc_updated_certificate', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Other Supporting Documents', 'n' => 'doc_other_supporting', 't' => 'checkbox', 'r' => 0],
        ['s' => '14. Document Checklist', 'l' => 'Upload Supporting Documents', 'n' => 'supporting_documents_upload', 't' => 'file', 'r' => 0],
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
