<?php
/**
 * Reseed Commercial Contracts → Attorney General Review (child #43)
 * Form: SUBMISSION FOR EXECUTION BY MANAGING DIRECTOR (AG workflow)
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
    $parent = ServiceParent::where('department_code', '78')
        ->orWhere('department_name', 'Commercial Contracts')
        ->first();
    if (!$parent) { throw new RuntimeException('Commercial Contracts parent not found'); }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Attorney General Review'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Submission for Execution by Managing Director (AG Review)',
            'description' => 'Contract intake, AG approval workflow, AG review, post-consent legal review, final execution, and post-execution implementation.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $contractTypes = ['Lease agreement', 'Contract of Sale', 'Assignment', 'Deed of Agreement', 'Other'];
    $yesNo = ['Yes', 'No'];
    $yesNoNA = ['Yes', 'No', 'N/A'];

    $fields = [
        // 1. CONTRACT INTAKE
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Reference No.', 'n' => 'reference_no', 't' => 'text', 'r' => 1],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Date Received', 'n' => 'date_received', 't' => 'date', 'r' => 1],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Requesting Department', 'n' => 'requesting_department', 't' => 'text', 'r' => 1],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'End User / Business Unit', 'n' => 'end_user_business_unit', 't' => 'text', 'r' => 1],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Subject / Matter', 'n' => 'subject_matter', 't' => 'textarea', 'r' => 1],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Contract Type', 'n' => 'contract_type', 't' => 'select', 'r' => 1, 'o' => $contractTypes],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Contract Type (Other - specify)', 'n' => 'contract_type_other', 't' => 'text', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Contract Value (K)', 'n' => 'contract_value', 't' => 'number', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Annual Rental / Consideration (K)', 'n' => 'annual_rental', 't' => 'number', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Contract Term', 'n' => 'contract_term', 't' => 'text', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Commencement Date', 'n' => 'commencement_date', 't' => 'date', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Expiry Date', 'n' => 'expiry_date', 't' => 'date', 'r' => 0],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Renewal Option', 'n' => 'renewal_option', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '1. Contract Intake & Instructions', 'l' => 'Contract Amendment / Renewal', 'n' => 'contract_amendment_renewal', 't' => 'select', 'r' => 0, 'o' => $yesNo],

        // 2. INSTRUCTIONS TO LEGAL
        ['s' => '2. Instructions to Legal Department', 'l' => 'Nature of Instructions', 'n' => 'nature_of_instructions', 't' => 'select', 'r' => 1, 'o' => ['Review Draft Contract', 'Other']],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Nature of Instructions (Other - specify)', 'n' => 'nature_of_instructions_other', 't' => 'text', 'r' => 0],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Detailed Instructions', 'n' => 'detailed_instructions', 't' => 'textarea', 'r' => 1],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Correspondence / Instructions Attached', 'n' => 'correspondence_attached', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Attachment Upload', 'n' => 'instructions_attachment', 't' => 'file', 'r' => 0],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Date of Instructions', 'n' => 'date_of_instructions', 't' => 'date', 'r' => 0],

        // 3. INTERNAL ROUTING
        ['s' => '3. Internal Routing', 'l' => 'Routing Officers', 'n' => 'internal_routing_table', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Officer', 'Name', 'Date', 'Signature'],
                    'rows' => ['Senior Manager – Commercial Contracts', 'Dealing Officer']]],
        ['s' => '3. Internal Routing', 'l' => 'Assigned Legal Officer', 'n' => 'assigned_legal_officer', 't' => 'text', 'r' => 1],
        ['s' => '3. Internal Routing', 'l' => 'Date Assigned', 'n' => 'date_assigned', 't' => 'date', 'r' => 1],
        ['s' => '3. Internal Routing', 'l' => 'Required Completion Date', 'n' => 'required_completion_date', 't' => 'date', 'r' => 0],
        ['s' => '3. Internal Routing', 'l' => 'Standard Review Period (working days)', 'n' => 'standard_review_period_days', 't' => 'number', 'r' => 0],

        // 4. CONTRACT TYPES (review)
        ['s' => '4. Contract Types', 'l' => 'Type of Agreement (Reviewed)', 'n' => 'reviewed_agreement_type', 't' => 'select', 'r' => 1, 'o' => $contractTypes],
        ['s' => '4. Contract Types', 'l' => 'Type of Agreement (Other - specify)', 'n' => 'reviewed_agreement_type_other', 't' => 'text', 'r' => 0],
        ['s' => '4. Contract Types', 'l' => 'Review Findings', 'n' => 'review_findings', 't' => 'textarea', 'r' => 1],

        // 5. AG APPROVAL WORKFLOW
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 1 (SM Commercial Contracts → Head Legal Services) — Prepared', 'n' => 'ag_memo1_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 1 — Submitted', 'n' => 'ag_memo1_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 1 — Date', 'n' => 'ag_memo1_date', 't' => 'date', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 2 (Head Legal Services → Company Secretary) — Prepared', 'n' => 'ag_memo2_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 2 — Submitted', 'n' => 'ag_memo2_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 2 — Date', 'n' => 'ag_memo2_date', 't' => 'date', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 3 (Company Secretary → Managing Director) — Prepared', 'n' => 'ag_memo3_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 3 — Submitted', 'n' => 'ag_memo3_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 3 — Letter Signed', 'n' => 'ag_memo3_letter_signed', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Memo 3 — Date', 'n' => 'ag_memo3_date', 't' => 'date', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Contract Submitted to Attorney General', 'n' => 'ag_contract_submitted', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Submission Date', 'n' => 'ag_submission_date', 't' => 'date', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Expected Review Period (weeks)', 'n' => 'ag_expected_review_weeks', 't' => 'number', 'r' => 0],
        ['s' => '5. Attorney General Approval Workflow', 'l' => 'Covering Letter Upload', 'n' => 'ag_covering_letter_upload', 't' => 'file', 'r' => 0],

        // 6. AG REVIEW
        ['s' => '6. Attorney General Review', 'l' => 'Status', 'n' => 'ag_status', 't' => 'select', 'r' => 1,
            'o' => ['Awaiting Review', 'Approved', 'Approved with Amendments', 'Returned for Further Information', 'Not Approved']],
        ['s' => '6. Attorney General Review', 'l' => 'Consent / Approval Received', 'n' => 'ag_consent_received', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Attorney General Review', 'l' => 'Date Received', 'n' => 'ag_date_received', 't' => 'date', 'r' => 0],
        ['s' => '6. Attorney General Review', 'l' => 'Consent Attached', 'n' => 'ag_consent_attached', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Attorney General Review', 'l' => 'Consent Document Upload', 'n' => 'ag_consent_upload', 't' => 'file', 'r' => 0],
        ['s' => '6. Attorney General Review', 'l' => 'Attorney General Comments', 'n' => 'ag_comments', 't' => 'textarea', 'r' => 0],

        // 7. POST-CONSENT LEGAL REVIEW
        ['s' => '7. Post-Consent Legal Review', 'l' => 'Instructions Received From', 'n' => 'pc_instructions_from', 't' => 'select', 'r' => 0, 'o' => ['Senior Manager Commercial Contracts']],
        ['s' => '7. Post-Consent Legal Review', 'l' => 'Date of Instructions', 'n' => 'pc_date_of_instructions', 't' => 'date', 'r' => 0],
        ['s' => '7. Post-Consent Legal Review', 'l' => 'Assigned Officer', 'n' => 'pc_assigned_officer', 't' => 'text', 'r' => 0],
        ['s' => '7. Post-Consent Legal Review', 'l' => 'Changes Incorporated', 'n' => 'pc_changes_incorporated', 't' => 'select', 'r' => 0, 'o' => $yesNoNA],
        ['s' => '7. Post-Consent Legal Review', 'l' => 'Final Contract Prepared', 'n' => 'pc_final_contract_prepared', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '7. Post-Consent Legal Review', 'l' => 'End User Requested to Print / Resubmit for Final Execution', 'n' => 'pc_end_user_requested_resubmit', 't' => 'select', 'r' => 0, 'o' => $yesNo],

        // 8. FINAL EXECUTION
        ['s' => '8. Final Execution', 'l' => 'Memo 1 (SM Commercial Contracts → Head Legal Services) — Prepared', 'n' => 'fe_memo1_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 1 — Submitted', 'n' => 'fe_memo1_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 1 — Recommendation Made', 'n' => 'fe_memo1_recommendation', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 1 — Date', 'n' => 'fe_memo1_date', 't' => 'date', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 2 (Head Legal Services → Company Secretary) — Prepared', 'n' => 'fe_memo2_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 2 — Submitted', 'n' => 'fe_memo2_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 2 — Legal Clearance Confirmed', 'n' => 'fe_memo2_legal_clearance', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 2 — Date', 'n' => 'fe_memo2_date', 't' => 'date', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 3 (Company Secretary → Managing Director) — Prepared', 'n' => 'fe_memo3_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 3 — Submitted', 'n' => 'fe_memo3_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 3 — Execution Requested', 'n' => 'fe_memo3_execution_requested', 't' => 'checkbox', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Memo 3 — Date', 'n' => 'fe_memo3_date', 't' => 'date', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Executed by Managing Director', 'n' => 'fe_executed_by_md', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '8. Final Execution', 'l' => 'Date Executed', 'n' => 'fe_date_executed', 't' => 'date', 'r' => 0],
        ['s' => '8. Final Execution', 'l' => 'Managing Director Signature', 'n' => 'fe_md_signature', 't' => 'signature', 'r' => 0],

        // 9. POST-EXECUTION IMPLEMENTATION
        ['s' => '9. Post-Execution Implementation', 'l' => 'Contract forwarded to Company Secretary', 'n' => 'post_forward_company_sec', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Company Secretary → Head Legal Services', 'n' => 'post_cs_to_hls', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Head Legal Services → Senior Manager Commercial Contracts', 'n' => 'post_hls_to_smcc', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Assigned Officer', 'n' => 'post_assigned_officer', 't' => 'text', 'r' => 0],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Memorandum to End User Prepared', 'n' => 'post_memo_end_user_prepared', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Executed Contract Upload', 'n' => 'executed_contract_upload', 't' => 'file', 'r' => 0],
        ['s' => '9. Post-Execution Implementation', 'l' => 'Remarks', 'n' => 'post_remarks', 't' => 'textarea', 'r' => 0],
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
