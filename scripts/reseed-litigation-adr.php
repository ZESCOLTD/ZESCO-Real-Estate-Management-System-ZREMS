<?php
/**
 * Reseed Litigation → Alternative Dispute Resolution (child #54)
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
    // Match Litigation by known name variants (test uses 'Legal Department / Legal Services',
    // local uses 'Litigation Department / Legal Services').
    $parent = ServiceParent::where(function($q) {
            $q->where('department_name', 'Legal Department')
              ->orWhere('department_name', 'Litigation Department');
        })
        ->where('service_name', 'Legal Services')
        ->first();
    if (!$parent) { throw new RuntimeException('Litigation parent (Legal/Litigation Department + Legal Services) not found'); }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Alternative Dispute Resolution'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Alternative Dispute Resolution',
            'description' => 'Matter intake, review, drafting, filing, settlement, workflow tracking and closure.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];
    $priority = ['High', 'Medium', 'Low', 'Urgent'];
    $matterTypes = ['Civil Suit', 'Arbitration', 'Mediation', 'Land Dispute', 'Contractual Dispute', 'Regulatory / Statutory', 'Other'];
    $deadlineTypes = ['Court-imposed', 'Statutory', 'Internal', 'Other'];
    $natureOfClaim = ['Contractual', 'Tort / Negligence', 'Land / Property', 'Employment', 'Commercial', 'Regulatory', 'Other'];
    $reviewActions = ['File Response', 'Negotiate Settlement', 'File Application', 'Request Extension', 'Refer to External Counsel', 'Other'];
    $settleTypes = ['Full Settlement', 'Partial Settlement', 'Consent Order', 'Withdrawal', 'Judgement by Consent', 'Other'];
    $finalOutcomes = ['Judgement in Favour', 'Judgement Against', 'Settled', 'Withdrawn', 'Dismissed', 'Struck Out', 'Other'];

    $fields = [
        // 1. MATTER INTAKE
        ['s' => '1. Matter Intake', 'l' => 'Type of Matter', 'n' => 'type_of_matter', 't' => 'select', 'r' => 1, 'o' => $matterTypes],
        ['s' => '1. Matter Intake', 'l' => 'Type of Matter (Other - specify)', 'n' => 'type_of_matter_other', 't' => 'text', 'r' => 0],
        ['s' => '1. Matter Intake', 'l' => 'Plaintiff / Claimant', 'n' => 'plaintiff_claimant', 't' => 'text', 'r' => 1],
        ['s' => '1. Matter Intake', 'l' => 'Defendant / Respondent', 'n' => 'defendant_respondent', 't' => 'text', 'r' => 1],
        ['s' => '1. Matter Intake', 'l' => 'Court', 'n' => 'court', 't' => 'text', 'r' => 0],
        ['s' => '1. Matter Intake', 'l' => 'Cause Number', 'n' => 'cause_number', 't' => 'text', 'r' => 0],
        ['s' => '1. Matter Intake', 'l' => 'Date Received', 'n' => 'date_received', 't' => 'date', 'r' => 1],
        ['s' => '1. Matter Intake', 'l' => 'Response Deadline', 'n' => 'response_deadline', 't' => 'date', 'r' => 0],
        ['s' => '1. Matter Intake', 'l' => 'Court Date', 'n' => 'court_date', 't' => 'date', 'r' => 0],
        ['s' => '1. Matter Intake', 'l' => 'Amount Claimed (K)', 'n' => 'amount_claimed', 't' => 'number', 'r' => 0],

        // 2. CLASSIFICATION & PRIORITY
        ['s' => '2. Matter Classification & Priority', 'l' => 'Nature of Claim', 'n' => 'nature_of_claim', 't' => 'select', 'r' => 1, 'o' => $natureOfClaim],
        ['s' => '2. Matter Classification & Priority', 'l' => 'Priority', 'n' => 'priority', 't' => 'select', 'r' => 1, 'o' => $priority],
        ['s' => '2. Matter Classification & Priority', 'l' => 'Deadline Type', 'n' => 'deadline_type', 't' => 'select', 'r' => 0, 'o' => $deadlineTypes],
        ['s' => '2. Matter Classification & Priority', 'l' => 'Deadline Type (Other - specify)', 'n' => 'deadline_type_other', 't' => 'text', 'r' => 0],

        // 3. INSTRUCTIONS BY HEAD LEGAL
        ['s' => '3. Instructions by Head Legal', 'l' => 'Date Assigned', 'n' => 'date_assigned', 't' => 'date', 'r' => 1],
        ['s' => '3. Instructions by Head Legal', 'l' => 'Assigned Legal Officer', 'n' => 'assigned_legal_officer', 't' => 'text', 'r' => 1],
        ['s' => '3. Instructions by Head Legal', 'l' => "Head Legal's Instructions", 'n' => 'head_legal_instructions', 't' => 'textarea', 'r' => 1],
        ['s' => '3. Instructions by Head Legal', 'l' => 'Upload Instructions Document', 'n' => 'instructions_document', 't' => 'file', 'r' => 0],
        ['s' => '3. Instructions by Head Legal', 'l' => 'Immediate Action Required', 'n' => 'immediate_action_required', 't' => 'textarea', 'r' => 0],

        // 4. INITIAL LEGAL REVIEW
        ['s' => '4. Initial Legal Review', 'l' => 'Confirmations', 'n' => 'initial_confirmations', 't' => 'textarea', 'r' => 0],
        ['s' => '4. Initial Legal Review', 'l' => 'Preliminary Assessment', 'n' => 'preliminary_assessment', 't' => 'textarea', 'r' => 1],
        ['s' => '4. Initial Legal Review', 'l' => 'Sufficient Information Available to Proceed?', 'n' => 'sufficient_information', 't' => 'select', 'r' => 1, 'o' => ['Yes, sufficient information is available to proceed', 'No, additional information/instructions required']],
        ['s' => '4. Initial Legal Review', 'l' => 'Court Hearing Documents Upload', 'n' => 'court_hearing_documents', 't' => 'file', 'r' => 0],
        ['s' => '4. Initial Legal Review', 'l' => 'Hearing Date', 'n' => 'hearing_date', 't' => 'date', 'r' => 0],
        ['s' => '4. Initial Legal Review', 'l' => 'Court Attendance Log', 'n' => 'court_attendance_log', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Hearing Date', 'Attending Officer', 'Outcome'], 'rows' => ['', '', '']]],

        // 5. REQUEST FOR DEPARTMENTAL INFORMATION
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Relevant Department', 'n' => 'relevant_department', 't' => 'text', 'r' => 0],
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Attach Memorandum', 'n' => 'attach_memorandum', 't' => 'file', 'r' => 0],
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Date Request Sent', 'n' => 'date_request_sent', 't' => 'date', 'r' => 0],
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Detailed Information Required', 'n' => 'detailed_information_required', 't' => 'textarea', 'r' => 0],
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Deadline for Departmental Response (5-day rule)', 'n' => 'deadline_dept_response', 't' => 'date', 'r' => 0],
        ['s' => '5. Request for Departmental Information / Instructions', 'l' => 'Time is of the Essence', 'n' => 'time_is_of_essence', 't' => 'select', 'r' => 0, 'o' => $yesNo],

        // 6. DEPARTMENTAL FOLLOW-UP
        ['s' => '6. Departmental Follow-Up', 'l' => 'Follow-Up Log', 'n' => 'follow_up_log', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Date', 'Officer', 'Method', 'Response Received', 'Remarks'], 'rows' => ['', '', '']]],

        // 7. REVIEW OF DEPARTMENTAL INSTRUCTIONS
        ['s' => '7. Review of Departmental Instructions', 'l' => 'Date Reviewed', 'n' => 'date_reviewed', 't' => 'date', 'r' => 0],
        ['s' => '7. Review of Departmental Instructions', 'l' => "Legal Officer's Assessment", 'n' => 'legal_officer_assessment', 't' => 'textarea', 'r' => 0],
        ['s' => '7. Review of Departmental Instructions', 'l' => 'Legal Action Required', 'n' => 'legal_action_required', 't' => 'select', 'r' => 0, 'o' => $reviewActions],
        ['s' => '7. Review of Departmental Instructions', 'l' => 'Legal Action Required (Other - specify)', 'n' => 'legal_action_required_other', 't' => 'text', 'r' => 0],

        // 8. DRAFTING & APPROVAL
        ['s' => '8. Drafting & Approval', 'l' => 'Document Prepared', 'n' => 'document_prepared', 't' => 'text', 'r' => 0],
        ['s' => '8. Drafting & Approval', 'l' => 'Date Prepared', 'n' => 'date_prepared', 't' => 'date', 'r' => 0],
        ['s' => '8. Drafting & Approval', 'l' => 'Internal Review', 'n' => 'internal_review', 't' => 'textarea', 'r' => 0],
        ['s' => '8. Drafting & Approval', 'l' => 'Date Approved', 'n' => 'date_approved', 't' => 'date', 'r' => 0],
        ['s' => '8. Drafting & Approval', 'l' => 'Draft Document Upload', 'n' => 'draft_document_upload', 't' => 'file', 'r' => 0],

        // 9. FILING / SERVICE OF COURT DOCUMENTS
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Document Filed / Served', 'n' => 'document_filed_served', 't' => 'text', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Court / Recipient', 'n' => 'court_recipient', 't' => 'text', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Date Filed / Served', 'n' => 'date_filed_served', 't' => 'date', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Deadline', 'n' => 'filing_deadline', 't' => 'date', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Proof Attachment', 'n' => 'proof_attachment', 't' => 'file', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Date of Hearing (if received)', 'n' => 'date_of_hearing_received', 't' => 'date', 'r' => 0],
        ['s' => '9. Filing / Service of Court Documents', 'l' => 'Date of Hearing Attachment', 'n' => 'date_of_hearing_attachment', 't' => 'file', 'r' => 0],

        // 10. SETTLEMENT / RESOLUTION
        ['s' => '10. Settlement / Resolution', 'l' => 'Settlement Considered', 'n' => 'settlement_considered', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '10. Settlement / Resolution', 'l' => 'Type', 'n' => 'settlement_type', 't' => 'select', 'r' => 0, 'o' => $settleTypes],
        ['s' => '10. Settlement / Resolution', 'l' => 'Type (Other - specify)', 'n' => 'settlement_type_other', 't' => 'text', 'r' => 0],
        ['s' => '10. Settlement / Resolution', 'l' => 'Settlement Terms / Proposal', 'n' => 'settlement_terms', 't' => 'textarea', 'r' => 0],
        ['s' => '10. Settlement / Resolution', 'l' => 'Approval Obtained', 'n' => 'settlement_approval_obtained', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '10. Settlement / Resolution', 'l' => 'Approval Date', 'n' => 'settlement_approval_date', 't' => 'date', 'r' => 0],
        ['s' => '10. Settlement / Resolution', 'l' => 'Settlement Agreement / Order / Judgement Upload', 'n' => 'settlement_document_upload', 't' => 'file', 'r' => 0],

        // 11. LITIGATION WORKFLOW TRACKER
        ['s' => '11. Litigation Workflow Tracker', 'l' => 'Workflow Tracker', 'n' => 'workflow_tracker', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Stage', 'Owner', 'Start Date', 'Target Date', 'Actual Date', 'Status', 'Remarks'], 'rows' => ['', '', '', '', '']]],

        // 12. DEADLINE & COMPLIANCE MONITOR
        ['s' => '12. Deadline & Compliance Monitor', 'l' => 'Deadline Monitor', 'n' => 'deadline_monitor', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Deadline', 'Description', 'Owner', 'Status'], 'rows' => ['', '', '']]],
        ['s' => '12. Deadline & Compliance Monitor', 'l' => 'All Applicable Deadlines Complied With', 'n' => 'deadlines_complied_with', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '12. Deadline & Compliance Monitor', 'l' => 'If No, explain', 'n' => 'deadlines_not_complied_explanation', 't' => 'textarea', 'r' => 0],

        // 13. MATTER CLOSURE
        ['s' => '13. Matter Closure', 'l' => 'Final Outcome', 'n' => 'final_outcome', 't' => 'select', 'r' => 0, 'o' => $finalOutcomes],
        ['s' => '13. Matter Closure', 'l' => 'Final Outcome (Other - specify)', 'n' => 'final_outcome_other', 't' => 'text', 'r' => 0],
        ['s' => '13. Matter Closure', 'l' => 'Attachment', 'n' => 'closure_attachment', 't' => 'file', 'r' => 0],
        ['s' => '13. Matter Closure', 'l' => 'Comment', 'n' => 'closure_comment', 't' => 'textarea', 'r' => 0],
        ['s' => '13. Matter Closure', 'l' => 'Date Matter Concluded', 'n' => 'date_matter_concluded', 't' => 'date', 'r' => 0],
        ['s' => '13. Matter Closure', 'l' => 'Final Order / Judgement Obtained', 'n' => 'final_order_obtained', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '13. Matter Closure', 'l' => 'Client / Relevant Department Notified', 'n' => 'client_notified', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => '13. Matter Closure', 'l' => 'Date Notified', 'n' => 'date_notified', 't' => 'date', 'r' => 0],

        // 14. FILE CLOSURE CHECKLIST
        ['s' => '14. File Closure Checklist', 'l' => 'File Closure Checklist', 'n' => 'file_closure_checklist', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Item', 'Done (Y/N)', 'Remarks'],
                    'rows' => ['All correspondence filed', 'All court documents filed', 'Final judgement/order filed', 'Deadlines log closed', 'Client notification sent', 'Archive location recorded']]],
        ['s' => '14. File Closure Checklist', 'l' => 'Closed By', 'n' => 'closed_by', 't' => 'text', 'r' => 0],
        ['s' => '14. File Closure Checklist', 'l' => 'Closure Date', 'n' => 'closure_date', 't' => 'date', 'r' => 0],
        ['s' => '14. File Closure Checklist', 'l' => 'Head Legal Services Approval (Name)', 'n' => 'head_legal_approval_name', 't' => 'text', 'r' => 0],
        ['s' => '14. File Closure Checklist', 'l' => 'Head Legal Signature (draw)', 'n' => 'head_legal_signature_draw', 't' => 'signature', 'r' => 0],
        ['s' => '14. File Closure Checklist', 'l' => 'Head Legal Signature (upload)', 'n' => 'head_legal_signature_upload', 't' => 'file', 'r' => 0],
        ['s' => '14. File Closure Checklist', 'l' => 'Signature Date', 'n' => 'head_legal_signature_date', 't' => 'date', 'r' => 0],
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
