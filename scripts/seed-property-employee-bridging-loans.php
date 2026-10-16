<?php
/**
 * Seed Property Department → Mortgages & Securities Management → Employee Securities (Bridging Loans) (#280)
 * Source spec: "EMPLOYEE BRIDGING LOAN – MANAGEMENT FORM"
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ServiceChild;
use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use App\Models\ServiceParent;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    $propertyParentIds = ServiceParent::where('department_name', 'Property Department')->pluck('id');
    if ($propertyParentIds->isEmpty()) {
        throw new RuntimeException('No Property Department parents found');
    }

    $child = ServiceChild::whereIn('service_parent_id', $propertyParentIds)
        ->where('service_name', 'Employee Securities (Bridging Loans)')
        ->first();
    if (! $child) {
        throw new RuntimeException('Child "Employee Securities (Bridging Loans)" not found under Property Department');
    }

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Employee Bridging Loan - Management Form',
            'description' => 'Employee Securities (Bridging Loans) — Management and Title Deed release form.',
            'is_active'   => 1,
        ]
    );

    $countBefore = ServiceFormField::where('service_form_template_id', $template->id)->count();
    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];

    $overallStatus     = ['Active', 'Paid Off', 'Released', 'Defaulted', 'Under Recovery', 'Closed'];
    $employmentStatus  = ['Permanent', 'Contract', 'Retired', 'Out of Service'];
    $loanTypes         = ['Employee Bridging Loan', 'Housing-Related Bridging Loan', 'Material Loan', 'Other'];
    $propertyTypes     = ['Residential House', 'Flat/Apartment', 'Commercial Property', 'Other'];
    $interestTypes     = ['Registered Owner', 'Joint Owner', 'Beneficial Interest', 'Other'];
    $loanStatus        = [
        'Performing', 'Paid Up', 'In Arrears', 'Restructured', 'Defaulted',
        'Referred for Recovery', 'Employee Separated from Corporation', 'Legal Action', 'Other',
    ];
    $supportingDocs    = [
        'Loan Agreement', 'Deed of Assignment', 'Letter of Undertaking',
        'Consent', 'Power of Attorney', 'Other',
    ];
    $releaseConditions = [
        'Loan fully repaid', 'All interest/charges settled', 'No outstanding liability',
        'Legal clearance obtained', 'Finance clearance obtained', 'Management approval obtained',
        'Mortgage/charge discharged, where applicable', 'Release documentation prepared',
    ];
    $handoverDocs      = [
        'Original Title Deed', 'Mortgage/Charge Discharge', 'Release Letter',
        'Loan Clearance Certificate', 'Payment Statement', 'Other relevant documents',
    ];

    $fields = [
        // 0. HEADER
        ['s' => 'Section 0 - Header', 'l' => 'LAMS Reference No.', 'n' => 'lams_reference_no', 't' => 'text',   'r' => 1],
        ['s' => 'Section 0 - Header', 'l' => 'Date Created',       'n' => 'date_created',      't' => 'date',   'r' => 1],
        ['s' => 'Section 0 - Header', 'l' => 'Status',             'n' => 'overall_status',    't' => 'select', 'r' => 1, 'o' => $overallStatus],

        // 1. EMPLOYEE DETAILS
        ['s' => 'Section 1 - Employee Details', 'l' => 'Employee Name',       'n' => 'employee_name',       't' => 'text',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Employee No.',        'n' => 'employee_no',         't' => 'text',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'NRC / ID No.',        'n' => 'nrc_id_no',           't' => 'text',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Department / Unit',   'n' => 'department_unit',     't' => 'text',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Job Title',           'n' => 'job_title',           't' => 'text',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Grade',               'n' => 'grade',               't' => 'text',  'r' => 0],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Employment Status',   'n' => 'employment_status',   't' => 'select','r' => 1, 'o' => $employmentStatus],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Date of Employment',  'n' => 'date_of_employment',  't' => 'date',  'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Telephone',           'n' => 'telephone',           't' => 'tel',   'r' => 1],
        ['s' => 'Section 1 - Employee Details', 'l' => 'Email',               'n' => 'email',               't' => 'email', 'r' => 1],

        // 2. BRIDGING LOAN DETAILS
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Loan / Facility Reference No.', 'n' => 'loan_facility_reference_no', 't' => 'text',     'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Loan Type',                     'n' => 'loan_type',                  't' => 'select',   'r' => 1, 'o' => $loanTypes],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Loan Type (Other - specify)',   'n' => 'loan_type_other',            't' => 'text',     'r' => 0],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Purpose of Loan',               'n' => 'purpose_of_loan',            't' => 'textarea', 'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Approved Loan Amount (K)',      'n' => 'approved_loan_amount',       't' => 'number',   'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Disbursed Amount (K)',          'n' => 'disbursed_amount',           't' => 'number',   'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Approval Date',                 'n' => 'approval_date',              't' => 'date',     'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Disbursement Date',             'n' => 'disbursement_date',          't' => 'date',     'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Loan Term',                     'n' => 'loan_term',                  't' => 'text',     'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Repayment Commencement Date',   'n' => 'repayment_commencement_date','t' => 'date',     'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Expected Final Repayment Date', 'n' => 'expected_final_repayment_date','t' => 'date',   'r' => 1],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Interest / Charges (K)',        'n' => 'interest_charges',           't' => 'number',   'r' => 0],
        ['s' => 'Section 2 - Bridging Loan Details', 'l' => 'Total Amount Payable (K)',      'n' => 'total_amount_payable',       't' => 'number',   'r' => 1],

        // 3. PROPERTY DETAILS
        ['s' => 'Section 3 - Property Details', 'l' => 'Property / House No.',                'n' => 'property_house_no',         't' => 'text',     'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => 'Plot No.',                            'n' => 'plot_no',                   't' => 'text',     'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => 'Property Address',                    'n' => 'property_address',          't' => 'textarea', 'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => 'District',                            'n' => 'district',                  't' => 'text',     'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => 'Province',                            'n' => 'province',                  't' => 'text',     'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => 'Property Type',                       'n' => 'property_type',             't' => 'select',   'r' => 1, 'o' => $propertyTypes],
        ['s' => 'Section 3 - Property Details', 'l' => 'Property Type (Other - specify)',     'n' => 'property_type_other',       't' => 'text',     'r' => 0],
        ['s' => 'Section 3 - Property Details', 'l' => 'Property Owner According to Title',   'n' => 'property_owner_per_title',  't' => 'text',     'r' => 1],
        ['s' => 'Section 3 - Property Details', 'l' => "Employee's Interest in Property",     'n' => 'employee_interest_in_property', 't' => 'select', 'r' => 1, 'o' => $interestTypes],
        ['s' => 'Section 3 - Property Details', 'l' => "Employee's Interest (Other - specify)",'n' => 'employee_interest_other',  't' => 'text',     'r' => 0],

        // 4. TITLE DEED DETAILS
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Title Deed No.',            'n' => 'title_deed_no',           't' => 'text',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Plot / Property No.',       'n' => 'title_plot_property_no',  't' => 'text',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Title Type',                'n' => 'title_type',              't' => 'text',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Date of Issue',             'n' => 'title_date_of_issue',     't' => 'date',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Registered Proprietor',     'n' => 'registered_proprietor',   't' => 'text',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Land Registry',             'n' => 'land_registry',           't' => 'text',   'r' => 1],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Original Title Surrendered','n' => 'original_title_surrendered','t' => 'select','r' => 1, 'o' => $yesNo],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Date Surrendered',          'n' => 'date_surrendered',        't' => 'date',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Surrendered By',            'n' => 'surrendered_by',          't' => 'text',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Valuation Date',            'n' => 'valuation_date',          't' => 'date',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Valuer',                    'n' => 'valuer',                  't' => 'text',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Valuation Report No.',      'n' => 'valuation_report_no',     't' => 'text',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'LAMS Document ID',          'n' => 'lams_document_id',        't' => 'text',   'r' => 0],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Scanned Copy Available',    'n' => 'scanned_copy_available',  't' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Certified Copy Available',  'n' => 'certified_copy_available','t' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Original Verified',         'n' => 'original_verified',       't' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Supporting Documents',      'n' => 'supporting_documents',    't' => 'checkbox','r' => 0, 'o' => $supportingDocs],
        ['s' => 'Section 4 - Title Deed Details', 'l' => 'Supporting Documents (Other - specify)', 'n' => 'supporting_documents_other', 't' => 'text', 'r' => 0],

        // 5. EMPLOYEE LOAN STATUS
        ['s' => 'Section 5 - Employee Loan Status', 'l' => 'Loan Status',               'n' => 'loan_status',        't' => 'select',   'r' => 1, 'o' => $loanStatus],
        ['s' => 'Section 5 - Employee Loan Status', 'l' => 'Loan Status (Other - specify)', 'n' => 'loan_status_other', 't' => 'text', 'r' => 0],
        ['s' => 'Section 5 - Employee Loan Status', 'l' => 'Remarks',                   'n' => 'loan_status_remarks','t' => 'textarea', 'r' => 0],

        // 6. TITLE DEED RELEASE APPLICATION
        ['s' => 'Section 6 - Title Deed Release Application', 'l' => 'Loan Fully Settled',      'n' => 'loan_fully_settled',        't' => 'select',   'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 6 - Title Deed Release Application', 'l' => 'Outstanding Balance (K)', 'n' => 'outstanding_balance',       't' => 'number',   'r' => 0],
        ['s' => 'Section 6 - Title Deed Release Application', 'l' => 'Release Requested By',    'n' => 'release_requested_by',      't' => 'text',     'r' => 0],
        ['s' => 'Section 6 - Title Deed Release Application', 'l' => 'Date Requested',          'n' => 'release_date_requested',    't' => 'date',     'r' => 0],
        ['s' => 'Section 6 - Title Deed Release Application', 'l' => 'Release Conditions',      'n' => 'release_conditions',        't' => 'checkbox', 'r' => 0, 'o' => $releaseConditions],

        // 7. DOCUMENT HANDOVER CHECKLIST
        ['s' => 'Section 7 - Document Handover Checklist', 'l' => 'Handover Checklist',      'n' => 'handover_checklist',    't' => 'checkbox', 'r' => 0, 'o' => $handoverDocs],
        ['s' => 'Section 7 - Document Handover Checklist', 'l' => 'Documents Released',      'n' => 'documents_released',    't' => 'textarea', 'r' => 0],
        ['s' => 'Section 7 - Document Handover Checklist', 'l' => 'Documents Upload',        'n' => 'documents_upload',      't' => 'file',     'r' => 0],
    ];

    $order = 1;
    foreach ($fields as $f) {
        ServiceFormField::create([
            'service_form_template_id' => $template->id,
            'section'      => $f['s'],
            'field_label'  => $f['l'],
            'field_name'   => $f['n'],
            'field_type'   => $f['t'],
            'is_required'  => $f['r'],
            'options'      => isset($f['o']) ? json_encode($f['o']) : null,
            'sort_order'   => $order++,
        ]);
    }

    $countAfter = $order - 1;
    $parent = ServiceParent::find($child->service_parent_id);
    echo "OK  parent=#{$parent->id} ({$parent->service_name})  child=#{$child->id}  template=#{$template->id}  fields_before={$countBefore}  fields_after={$countAfter}" . PHP_EOL;
});
