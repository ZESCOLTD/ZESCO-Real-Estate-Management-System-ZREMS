<?php
/**
 * Seed Property Department → Land Conversion → Application for Numbering (child #321)
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
        ->where('service_name', 'Land Conversion')
        ->first();
    if (!$parent) { throw new RuntimeException('Parent Property Department / Land Conversion not found'); }

    $child = ServiceChild::updateOrCreate(
        ['service_parent_id' => $parent->id, 'service_name' => 'Application for Numbering'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Ministry of Lands - Application for Land/Plot Number Form',
            'description' => 'ZESCO Limited — LAMS Application for Number.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];
    $purposes = ['New Land Acquisition', 'Subdivision', 'Consolidation', 'Re-planning', 'Property Regularisation', 'Disposal/Transfer', 'Other'];
    $landTenure = ['Customary Land', 'Leasehold', 'Other'];
    $submissionMethods = ['Physical Submission', 'Online / e-Government System', 'Through Surveyor', 'Through Legal Practitioner', 'Other'];
    $nextActions = ['Survey', 'Invitation to Treat', 'Application for Consent', 'Title Registration', 'Deed of Transfer', 'Lease Preparation', 'Subdivision Registration', 'Disposal', 'Valuation', 'Other'];

    $fields = [
        // HEADER
        ['s' => 'Header', 'l' => 'Form No.', 'n' => 'form_no', 't' => 'text', 'r' => 0],
        ['s' => 'Header', 'l' => 'LAMS Reference No.', 'n' => 'lams_reference_no', 't' => 'text', 'r' => 1],
        ['s' => 'Header', 'l' => 'Date', 'n' => 'form_date', 't' => 'date', 'r' => 1],

        // PART A – APPLICATION DETAILS
        ['s' => 'Part A - Application Details', 'l' => 'Requesting Department / Directorate', 'n' => 'requesting_department', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Application Details', 'l' => 'Province', 'n' => 'province', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Application Details', 'l' => 'District', 'n' => 'district', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Application Details', 'l' => 'Town / Locality', 'n' => 'town_locality', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Application Details', 'l' => 'Ward', 'n' => 'ward', 't' => 'text', 'r' => 0],
        ['s' => 'Part A - Application Details', 'l' => 'Chiefdom (where applicable)', 'n' => 'chiefdom', 't' => 'text', 'r' => 0],
        ['s' => 'Part A - Application Details', 'l' => 'Purpose of Application', 'n' => 'purpose_of_application', 't' => 'select', 'r' => 1, 'o' => $purposes],
        ['s' => 'Part A - Application Details', 'l' => 'Purpose (Other - specify)', 'n' => 'purpose_of_application_other', 't' => 'text', 'r' => 0],

        // PART B – PROPERTY / PARENT LAND DETAILS
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Current Plot / Stand / Parcel No.', 'n' => 'current_plot_stand_parcel_no', 't' => 'text', 'r' => 1],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Title Deed No.', 'n' => 'title_deed_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Certificate of Title Available', 'n' => 'certificate_of_title_available', 't' => 'select', 'r' => 0, 'o' => $yesNo],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Registered Proprietor', 'n' => 'registered_proprietor', 't' => 'text', 'r' => 1],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Property / Asset Register No.', 'n' => 'property_asset_register_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'LAMS Asset No.', 'n' => 'lams_asset_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Location', 'n' => 'property_location', 't' => 'text', 'r' => 1],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'District', 'n' => 'property_district', 't' => 'text', 'r' => 1],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Province', 'n' => 'property_province', 't' => 'text', 'r' => 1],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Current Land Use', 'n' => 'current_land_use', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Land Tenure', 'n' => 'land_tenure', 't' => 'select', 'r' => 1, 'o' => $landTenure],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Land Tenure (Other - specify)', 'n' => 'land_tenure_other', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Property / Parent Land Details', 'l' => 'Total Land Extent (hectares)', 'n' => 'total_land_extent_ha', 't' => 'number', 'r' => 1],

        // PART C – MINISTRY OF LANDS SUBMISSION
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Application Prepared By', 'n' => 'application_prepared_by', 't' => 'text', 'r' => 1],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Designation', 'n' => 'preparer_designation', 't' => 'text', 'r' => 1],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Date Prepared', 'n' => 'date_prepared', 't' => 'date', 'r' => 1],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Date Submitted', 'n' => 'date_submitted', 't' => 'date', 'r' => 0],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Submission Method', 'n' => 'submission_method', 't' => 'select', 'r' => 0, 'o' => $submissionMethods],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Submission Method (Other - specify)', 'n' => 'submission_method_other', 't' => 'text', 'r' => 0],
        ['s' => 'Part C - Ministry of Lands Submission', 'l' => 'Ministry of Lands Case No.', 'n' => 'ministry_case_no', 't' => 'text', 'r' => 0],

        // PART D – DOCUMENTS SUBMITTED
        ['s' => 'Part D - Documents Submitted', 'l' => 'Documents Checklist', 'n' => 'documents_checklist', 't' => 'table', 'r' => 0,
            'o' => ['columns' => ['Document', 'Attached', 'Certified', 'Uploaded to LAMS'],
                    'rows' => [
                        'Application letter / form',
                        'NRC / Company documents',
                        'Title deed',
                        'Lands Registry search',
                        'Survey plan',
                        'Site plan',
                        'Planning approval',
                        'Consent to subdivide',
                        'Board / Management approval',
                        'Valuation report',
                        'Property photographs',
                        'Other supporting document',
                    ]]],
        ['s' => 'Part D - Documents Submitted', 'l' => 'Supporting Documents Upload', 'n' => 'supporting_documents_upload', 't' => 'file', 'r' => 0],

        // PART E – NUMBER ALLOCATION
        ['s' => 'Part E - Number Allocation', 'l' => 'Property No. (Allocated by Ministry of Lands)', 'n' => 'allocated_property_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part E - Number Allocation', 'l' => 'Date Allocated', 'n' => 'date_allocated', 't' => 'date', 'r' => 0],
        ['s' => 'Part E - Number Allocation', 'l' => 'Allocation Letter / Evidence Upload', 'n' => 'allocation_evidence_upload', 't' => 'file', 'r' => 0],

        // PART F – FOLLOW-UP ACTION
        ['s' => 'Part F - Follow-up Action', 'l' => 'Next Required Action', 'n' => 'next_required_action', 't' => 'select', 'r' => 0, 'o' => $nextActions],
        ['s' => 'Part F - Follow-up Action', 'l' => 'Next Required Action (Other - specify)', 'n' => 'next_required_action_other', 't' => 'text', 'r' => 0],
        ['s' => 'Part F - Follow-up Action', 'l' => 'Responsible Department', 'n' => 'responsible_department', 't' => 'text', 'r' => 0],
        ['s' => 'Part F - Follow-up Action', 'l' => 'Responsible Officer', 'n' => 'responsible_officer', 't' => 'text', 'r' => 0],
        ['s' => 'Part F - Follow-up Action', 'l' => 'Target Date', 'n' => 'target_date', 't' => 'date', 'r' => 0],
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
