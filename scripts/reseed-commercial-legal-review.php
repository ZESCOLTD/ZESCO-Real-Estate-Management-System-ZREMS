<?php
/**
 * Reseed Commercial Contracts → Legal Review (child #42)
 * Form: LEGAL REVIEW AND SUBMISSION FOR EXECUTION BY MANAGING DIRECTOR
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Legal Review'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Legal Review and Submission for Execution by Managing Director',
            'description' => 'Contract intake, legal review, internal execution workflow and post-execution implementation.',
            'is_active' => 1,
        ]
    );

    // Wipe existing fields for a clean reseed
    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $contractTypes = ['Lease agreement', 'Contract of Sale', 'Assignment', 'Deed of Agreement', 'Other'];
    $yesNo = ['Yes', 'No'];

    $fields = [
        // 1. CONTRACT INTAKE & INSTRUCTIONS
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

        // 2. INSTRUCTIONS TO LEGAL DEPARTMENT
        ['s' => '2. Instructions to Legal Department', 'l' => 'Nature of Instructions', 'n' => 'nature_of_instructions', 't' => 'select', 'r' => 1, 'o' => ['Review Draft Contract', 'Other']],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Nature of Instructions (Other - specify)', 'n' => 'nature_of_instructions_other', 't' => 'text', 'r' => 0],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Detailed Instructions', 'n' => 'detailed_instructions', 't' => 'textarea', 'r' => 1],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Correspondence / Instructions Attached', 'n' => 'correspondence_attached', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Attachment Upload', 'n' => 'instructions_attachment', 't' => 'file', 'r' => 0],
        ['s' => '2. Instructions to Legal Department', 'l' => 'Date of Instructions', 'n' => 'date_of_instructions', 't' => 'date', 'r' => 0],

        // 3. INTERNAL ROUTING
        ['s' => '3. Internal Routing', 'l' => 'Routing Officers', 'n' => 'internal_routing_table', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Officer', 'Name', 'Date', 'Signature'],
                    'rows' => ['Company Secretary', 'Head Legal Services', 'Senior Manager – Commercial Contracts', 'Dealing Officer']]],
        ['s' => '3. Internal Routing', 'l' => 'Assigned Legal Officer', 'n' => 'assigned_legal_officer', 't' => 'text', 'r' => 1],
        ['s' => '3. Internal Routing', 'l' => 'Date Assigned', 'n' => 'date_assigned', 't' => 'date', 'r' => 1],
        ['s' => '3. Internal Routing', 'l' => 'Required Completion Date', 'n' => 'required_completion_date', 't' => 'date', 'r' => 0],
        ['s' => '3. Internal Routing', 'l' => 'Standard Review Period (working days)', 'n' => 'standard_review_period_days', 't' => 'number', 'r' => 0],

        // 4. CONTRACT TYPES (review findings)
        ['s' => '4. Contract Types', 'l' => 'Type of Agreement (Reviewed)', 'n' => 'reviewed_agreement_type', 't' => 'select', 'r' => 1, 'o' => $contractTypes],
        ['s' => '4. Contract Types', 'l' => 'Type of Agreement (Other - specify)', 'n' => 'reviewed_agreement_type_other', 't' => 'text', 'r' => 0],
        ['s' => '4. Contract Types', 'l' => 'Review Findings', 'n' => 'review_findings', 't' => 'textarea', 'r' => 1],

        // 5. INTERNAL EXECUTION WORKFLOW
        // Memo 1
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 1 (SM Commercial Contracts → Head Legal Services) — Prepared', 'n' => 'memo1_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 1 — Submitted', 'n' => 'memo1_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 1 — Approved', 'n' => 'memo1_approved', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 1 — Date', 'n' => 'memo1_date', 't' => 'date', 'r' => 0],
        // Memo 2
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 2 (Head Legal Services → Company Secretary) — Prepared', 'n' => 'memo2_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 2 — Submitted', 'n' => 'memo2_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 2 — Legal Clearance Confirmed', 'n' => 'memo2_legal_clearance', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 2 — Date', 'n' => 'memo2_date', 't' => 'date', 'r' => 0],
        // Memo 3
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 3 (Company Secretary → Managing Director) — Prepared', 'n' => 'memo3_prepared', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 3 — Submitted', 'n' => 'memo3_submitted', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 3 — Execution Requested', 'n' => 'memo3_execution_requested', 't' => 'checkbox', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Memo 3 — Date', 'n' => 'memo3_date', 't' => 'date', 'r' => 0],
        // MD Execution
        ['s' => '5. Internal Execution Workflow', 'l' => 'Managing Director — Contract Executed', 'n' => 'md_contract_executed', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Date Executed', 'n' => 'md_date_executed', 't' => 'date', 'r' => 0],
        ['s' => '5. Internal Execution Workflow', 'l' => 'Managing Director Signature', 'n' => 'md_signature', 't' => 'signature', 'r' => 0],

        // 6. POST-EXECUTION IMPLEMENTATION
        ['s' => '6. Post-Execution Implementation', 'l' => 'Contract forwarded to Company Secretary', 'n' => 'post_forward_company_sec', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Company Secretary → Head Legal Services', 'n' => 'post_cs_to_hls', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Head Legal Services → Senior Manager Commercial Contracts', 'n' => 'post_hls_to_smcc', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Assigned Officer', 'n' => 'post_assigned_officer', 't' => 'text', 'r' => 0],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Memorandum to End User Prepared', 'n' => 'post_memo_end_user_prepared', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Executed Contract Upload', 'n' => 'executed_contract_upload', 't' => 'file', 'r' => 0],
        ['s' => '6. Post-Execution Implementation', 'l' => 'Remarks', 'n' => 'post_remarks', 't' => 'textarea', 'r' => 0],
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
