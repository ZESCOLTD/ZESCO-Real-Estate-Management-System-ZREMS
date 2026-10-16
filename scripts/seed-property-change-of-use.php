<?php
/**
 * Seed the Property Department child service "Change of Land Use"
 * + its full LAMS Change of Land Use form template.
 *
 * Run:  php scripts/seed-property-change-of-use.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceParent;
use App\Models\ServiceChild;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Support\Facades\DB;

/* -------------------------------------------------------------------
 |  Option lists
 |  ------------------------------------------------------------------- */
$YES_NO = ['Yes', 'No'];

$APPLICATION_TYPE = [
    'Change of Use', 'New Construction', 'Extension/Alteration',
    'Renovation', 'Demolition', 'Mixed Development', 'Other',
];

$APPLICATION_STATUS = ['Draft', 'Submitted', 'Under Review', 'Approved', 'Rejected', 'Withdrawn'];

$DEVELOPMENT_TYPE = [
    'Residential', 'Commercial', 'Industrial', 'Institutional',
    'Agricultural', 'Mixed Use', 'Warehouse', 'Office', 'Other',
];

$TECHNICAL_DOCS = [
    'Title/Lease Documents', 'Site Plan', 'Location Plan', 'Survey Diagram',
    'Architectural Drawings', 'Structural Drawings', 'Drainage Plan',
    'Engineering Drawings', 'Parking/Access Plan', 'Environmental Documentation',
    'Fire-Safety Documentation', 'Services/Infrastructure Plan',
    'Previous Planning Approval', 'Existing Building Plans', 'Other',
];

$DECISION = ['Approved', 'Approved with Conditions', 'Refused'];
$COMPLIANCE = ['Complies', 'Does Not Comply', 'N/A'];

/* -------------------------------------------------------------------
 |  Development parameters table
 |  ------------------------------------------------------------------- */
$DEV_PARAMS_TABLE = [
    ['name' => 'parameter',        'label' => 'Parameter',           'type' => 'text'],
    ['name' => 'approved_required','label' => 'Approved/Required',   'type' => 'text'],
    ['name' => 'proposed',         'label' => 'Proposed',            'type' => 'text'],
    ['name' => 'compliance',       'label' => 'Compliance',          'type' => 'select', 'options' => $COMPLIANCE],
    'default_rows' => [
        ['parameter' => 'Plot size',         'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Site coverage',     'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Plot ratio',        'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Building height',   'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Setbacks',          'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Parking provision', 'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
        ['parameter' => 'Landscaping',       'approved_required' => '', 'proposed' => '', 'compliance' => 'N/A'],
    ],
];

/* -------------------------------------------------------------------
 |  Sections
 |  ------------------------------------------------------------------- */
$S1 = '1. Application Details';
$S2 = '2. Property Details';
$S3 = '3. Change of Use';
$S4 = '4. Construction / Development Details';
$S5 = '5. Development Parameters';
$S6 = '6. Technical Documents';
$S7 = '7. Site Inspection';
$S8 = '8. Public / Stakeholder Consultation';
$S9 = '9. Approval';

$fields = [
    // ---- 1. Application ----
    ['section' => $S1, 'name' => 'application_reference_no', 'label' => 'Application Reference No.', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'date_received',            'label' => 'Date Received', 'type' => 'date', 'required' => true],
    ['section' => $S1, 'name' => 'lams_property_id',         'label' => 'LAMS Property ID', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'applicant_developer',      'label' => 'Applicant/Developer', 'type' => 'text', 'required' => true],
    ['section' => $S1, 'name' => 'applicant_contact',        'label' => 'Contact', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'application_type',         'label' => 'Application Type', 'type' => 'checkbox', 'options' => $APPLICATION_TYPE, 'required' => true],
    ['section' => $S1, 'name' => 'application_type_other',   'label' => 'If Other application type, specify', 'type' => 'text', 'required' => false],
    ['section' => $S1, 'name' => 'application_status',       'label' => 'Application Status', 'type' => 'select', 'options' => $APPLICATION_STATUS, 'required' => true],

    // ---- 2. Property ----
    ['section' => $S2, 'name' => 'property_no',          'label' => 'Property No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'title_lease_no',       'label' => 'Title/Lease No.', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'street',               'label' => 'Street', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'district',             'label' => 'District', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'local_authority',      'label' => 'Local Authority', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'land_size',            'label' => 'Land Size', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'current_use',          'label' => 'Current Use', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'proposed_use',         'label' => 'Proposed Use', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'zoning',               'label' => 'Zoning', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'cadastral_reference',  'label' => 'Cadastral Reference', 'type' => 'text', 'required' => false],
    ['section' => $S2, 'name' => 'coordinates',          'label' => 'Coordinates', 'type' => 'text', 'required' => false],

    // ---- 3. Change of Use ----
    ['section' => $S3, 'name' => 'current_approved_use', 'label' => 'Current Approved Use', 'type' => 'text', 'required' => false],
    ['section' => $S3, 'name' => 'current_development',  'label' => 'Current Development', 'type' => 'textarea', 'required' => false],
    ['section' => $S3, 'name' => 'proposed_land_use',    'label' => 'Proposed Land/Property Use', 'type' => 'textarea', 'required' => true],
    ['section' => $S3, 'name' => 'change_from',          'label' => 'Change From', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'change_to',            'label' => 'Change To', 'type' => 'text', 'required' => true],
    ['section' => $S3, 'name' => 'reason_for_change',    'label' => 'Reason for Change of Use', 'type' => 'textarea', 'required' => true],
    ['section' => $S3, 'name' => 'planning_justification','label' => 'Planning Justification', 'type' => 'textarea', 'required' => false],
    ['section' => $S3, 'name' => 'justification_file',   'label' => 'Upload Planning Justification', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 4. Construction / Development ----
    ['section' => $S4, 'name' => 'development_description', 'label' => 'Development Description', 'type' => 'textarea', 'required' => false],
    ['section' => $S4, 'name' => 'development_type',        'label' => 'Type of Development', 'type' => 'checkbox', 'options' => $DEVELOPMENT_TYPE, 'required' => false],
    ['section' => $S4, 'name' => 'development_type_other',  'label' => 'If Other development type, specify', 'type' => 'text', 'required' => false],

    // ---- 5. Development Parameters ----
    ['section' => $S5, 'name' => 'dev_parameters_table', 'label' => 'Development Parameters', 'type' => 'table', 'options' => $DEV_PARAMS_TABLE, 'required' => false],

    // ---- 6. Technical Documents ----
    ['section' => $S6, 'name' => 'technical_docs_checklist', 'label' => 'Required Attachments', 'type' => 'checkbox', 'options' => $TECHNICAL_DOCS, 'required' => false],
    ['section' => $S6, 'name' => 'technical_docs_other',     'label' => 'If Other attachment, specify', 'type' => 'text', 'required' => false],
    ['section' => $S6, 'name' => 'technical_docs_files',     'label' => 'Upload Technical Documents', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 7. Site Inspection ----
    ['section' => $S7, 'name' => 'inspection_date',       'label' => 'Inspection Date', 'type' => 'date', 'required' => false],
    ['section' => $S7, 'name' => 'inspection_team',       'label' => 'Inspection Team', 'type' => 'text', 'required' => false],
    ['section' => $S7, 'name' => 'existing_development',  'label' => 'Existing Development', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'current_occupation',    'label' => 'Current Occupation', 'type' => 'textarea', 'required' => false],
    ['section' => $S7, 'name' => 'inspection_report',     'label' => 'Attach Inspection Report / Photos', 'type' => 'file', 'required' => false, 'allow_attachment' => true, 'allow_multiple_attachments' => true],

    // ---- 8. Public / Stakeholder Consultation ----
    ['section' => $S8, 'name' => 'consultation_required',  'label' => 'Consultation Required', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S8, 'name' => 'consultation_date',      'label' => 'Consultation Date', 'type' => 'date', 'required' => false],
    ['section' => $S8, 'name' => 'stakeholders_notified',  'label' => 'Stakeholders Notified', 'type' => 'textarea', 'required' => false],
    ['section' => $S8, 'name' => 'objections_received',    'label' => 'Objections Received', 'type' => 'select', 'options' => $YES_NO, 'required' => false],
    ['section' => $S8, 'name' => 'number_of_objections',   'label' => 'Number of Objections', 'type' => 'number', 'required' => false],
    ['section' => $S8, 'name' => 'objections_resolution',  'label' => 'Comments / Resolution', 'type' => 'textarea', 'required' => false],

    // ---- 9. Approval ----
    ['section' => $S9, 'name' => 'approving_authority',   'label' => 'Approving Authority', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'decision',              'label' => 'Decision', 'type' => 'select', 'options' => $DECISION, 'required' => false],
    ['section' => $S9, 'name' => 'approval_conditions',   'label' => 'Conditions (if Approved with Conditions)', 'type' => 'textarea', 'required' => false],
    ['section' => $S9, 'name' => 'approval_reference',    'label' => 'Approval Reference', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'approval_date',         'label' => 'Approval Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'validity_expiry_date',  'label' => 'Validity/Expiry Date', 'type' => 'date', 'required' => false],
    ['section' => $S9, 'name' => 'approval_certificate',  'label' => 'Upload Approval Certificate', 'type' => 'file', 'required' => false, 'allow_attachment' => true],
    ['section' => $S9, 'name' => 'approving_officer',     'label' => 'Approving Officer', 'type' => 'text', 'required' => false],
    ['section' => $S9, 'name' => 'approving_signature',   'label' => 'Approving Officer Signature', 'type' => 'signature', 'required' => false],
];

/* -------------------------------------------------------------------
 |  Persist
 |  ------------------------------------------------------------------- */
DB::transaction(function () use ($fields) {

    $parent = ServiceParent::where('department_code', 'property_department')
        ->orWhere('department_name', 'Property Department')
        ->first();

    if (!$parent) {
        echo "  ERROR: Property Department parent not found.\n";
        return;
    }
    echo "  Parent : #{$parent->id}  {$parent->department_name} / {$parent->service_name}\n";

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Change of Land Use'],
        ['model' => 'Change of Land Use', 'type' => 'asset_management', 'is_active' => 1]
    );
    echo "  Child  : #{$child->id}  {$child->service_name}\n";

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'LAMS — Planning: Change of Use Form',
            'description' => 'Property Department — Application, property details, change-of-use justification, development parameters, inspection, consultation and planning approval.',
            'is_active'   => true,
        ]
    );
    echo "  Template: #{$template->id}  {$template->form_name}\n";

    $existing = ServiceFormField::where('service_form_template_id', $template->id)->count();
    if ($existing > 0) {
        echo "     ↳ {$existing} fields already exist, skipping insert.\n";
        return;
    }

    $order = 10;
    foreach ($fields as $f) {
        ServiceFormField::create([
            'service_form_template_id'   => $template->id,
            'field_name'                 => $f['name'],
            'field_label'                => $f['label'],
            'field_type'                 => $f['type'],
            'options'                    => $f['options'] ?? null,
            'is_required'                => (bool) ($f['required'] ?? false),
            'sort_order'                 => $order,
            'section'                    => $f['section'],
            'allow_comments'             => false,
            'allow_attachment'           => (bool) ($f['allow_attachment'] ?? false),
            'allow_multiple_attachments' => (bool) ($f['allow_multiple_attachments'] ?? false),
            'attachment_label'           => $f['attachment_label'] ?? null,
            'validation_rules'           => null,
        ]);
        $order += 10;
    }
    echo "     ↳ Inserted " . count($fields) . " fields across "
        . count(array_unique(array_column($fields, 'section'))) . " sections.\n";
});

echo "\nDone.\n";
