<?php
/**
 * Replace fields on: Property Department → Application for Numbering
 * Locates the child by name (currently under Conveyance Services after reorg).
 * Keeps child + template records intact; only wipes and re-seeds fields.
 *
 * Source spec: "APPLICATION FOR NUMBERING OF CUSTOMARY LAND" — MoLNR.
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
    // Resolve child by name, scoped to Property Department (parents).
    $propertyParentIds = ServiceParent::where('department_name', 'Property Department')->pluck('id');
    if ($propertyParentIds->isEmpty()) {
        throw new RuntimeException('No Property Department parents found');
    }

    $child = ServiceChild::whereIn('service_parent_id', $propertyParentIds)
        ->where('service_name', 'Application for Numbering')
        ->first();
    if (! $child) {
        throw new RuntimeException('Child "Application for Numbering" not found under Property Department');
    }

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Application for Numbering of Customary Land',
            'description' => 'Ministry of Lands and Natural Resources — Application for Numbering of Customary Land.',
            'is_active'   => 1,
        ]
    );

    $countBefore = ServiceFormField::where('service_form_template_id', $template->id)->count();
    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo         = ['Yes', 'No'];
    $yesNoNa       = ['Yes', 'No', 'N/A'];
    $intendedUse   = ['Residential', 'Commercial', 'Industrial', 'Other'];
    $currentUse    = ['Undeveloped', 'Residential', 'Commercial', 'Industrial', 'Other'];
    $planningStatus= ['Approved', 'Pending', 'Not Applicable', 'Other'];
    $applicationType= ['New Numbering', 'Other'];
    $currentStatus = ['Submitted', 'Under Examination', 'Query Raised', 'Approved', 'Number Assigned', 'Rejected', 'Withdrawn', 'Completed'];

    $fields = [
        // 1. HEADER / REFERENCE
        ['s' => 'Section 1 - Reference', 'l' => 'LAMS Reference No.',       'n' => 'lams_reference_no', 't' => 'text', 'r' => 1],
        ['s' => 'Section 1 - Reference', 'l' => 'Application No.',          'n' => 'application_no',    't' => 'text', 'r' => 0],
        ['s' => 'Section 1 - Reference', 'l' => 'Date',                     'n' => 'form_date',         't' => 'date', 'r' => 1],

        // 2. LAND IDENTIFICATION
        ['s' => 'Section 2 - Land Identification', 'l' => 'Province',                      'n' => 'province',                    't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'District',                      'n' => 'district',                    't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Chiefdom',                      'n' => 'chiefdom',                    't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Traditional Authority',         'n' => 'traditional_authority',       't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Area / Locality / Village',     'n' => 'area_locality_village',       't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Approximate Extent (hectares)', 'n' => 'approximate_extent_hectares', 't' => 'number', 'r' => 1],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Intended Land Use',             'n' => 'intended_land_use',           't' => 'select', 'r' => 1, 'o' => $intendedUse],
        ['s' => 'Section 2 - Land Identification', 'l' => 'Intended Land Use (Other - specify)', 'n' => 'intended_land_use_other', 't' => 'text', 'r' => 0],

        // 3. CUSTOMARY LAND ALLOCATION DETAILS
        ['s' => 'Section 3 - Customary Land Allocation Details', 'l' => 'Name of Traditional Authority / Chief', 'n' => 'name_of_traditional_authority_chief', 't' => 'text', 'r' => 1],

        // 4. TRADITIONAL AUTHORITY CONFIRMATION
        ['s' => 'Section 4 - Traditional Authority Confirmation', 'l' => 'Chief / Traditional Authority Name', 'n' => 'chief_confirmation_name', 't' => 'text', 'r' => 1],
        ['s' => 'Section 4 - Traditional Authority Confirmation', 'l' => 'Attach Consent',                     'n' => 'consent_attachment',      't' => 'file', 'r' => 1],

        // 5. LAND USE & DEVELOPMENT
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Current Use of Land',                   'n' => 'current_use_of_land',       't' => 'select',   'r' => 1, 'o' => $currentUse],
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Current Use of Land (Other - specify)', 'n' => 'current_use_of_land_other', 't' => 'text',     'r' => 0],
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Existing Developments / Improvements',  'n' => 'existing_developments',     't' => 'textarea', 'r' => 0],
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Proposed Development',                  'n' => 'proposed_development',      't' => 'textarea', 'r' => 1],
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Planning Permission Required',          'n' => 'planning_permission_required', 't' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 5 - Land Use & Development', 'l' => 'Planning Approval Obtained',            'n' => 'planning_approval_obtained', 't' => 'select', 'r' => 1, 'o' => $yesNoNa],

        // 6. LAND USE PLANNING & COUNCIL CONFIRMATION
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Local Authority',                'n' => 'local_authority',             't' => 'text',     'r' => 1],
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Planning Authority Reference No.', 'n' => 'planning_authority_ref_no', 't' => 'text',     'r' => 0],
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Planning Zone',                  'n' => 'planning_zone',               't' => 'text',     'r' => 0],
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Planning Status',                'n' => 'planning_status',             't' => 'select',   'r' => 1, 'o' => $planningStatus],
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Planning Status (Other - specify)', 'n' => 'planning_status_other',   't' => 'text',     'r' => 0],
        ['s' => 'Section 6 - Land Use Planning & Council Confirmation', 'l' => 'Planning Comments',              'n' => 'planning_comments',           't' => 'textarea', 'r' => 0],

        // 7. MINISTRY OF LANDS APPLICATION DETAILS
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Ministry of Lands Office',                'n' => 'ministry_of_lands_office',     't' => 'text',   'r' => 1],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Responsible Office / Department',         'n' => 'responsible_office',           't' => 'text',   'r' => 1],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Application Type',                        'n' => 'application_type',             't' => 'select', 'r' => 1, 'o' => $applicationType],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Application Type (Other - specify)',      'n' => 'application_type_other',       't' => 'text',   'r' => 0],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Assigned Parcel Number',                  'n' => 'assigned_parcel_number',       't' => 'text',   'r' => 0],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Ministry Application Reference',          'n' => 'ministry_application_reference','t' => 'text',  'r' => 0],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Date Submitted',                          'n' => 'date_submitted',               't' => 'date',   'r' => 0],
        ['s' => 'Section 7 - Ministry of Lands Application Details', 'l' => 'Current Status',                          'n' => 'current_status',               't' => 'select', 'r' => 1, 'o' => $currentStatus],

        // 8. FEES & PAYMENT
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Application Fee (K)',        'n' => 'application_fee',        't' => 'number', 'r' => 1],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Other Government Fees (K)',  'n' => 'other_government_fees',  't' => 'number', 'r' => 0],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Total Fees (K)',             'n' => 'total_fees',             't' => 'number', 'r' => 1],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Payment Reference',          'n' => 'payment_reference',      't' => 'text',   'r' => 1],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Date Paid',                  'n' => 'date_paid',              't' => 'date',   'r' => 1],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Proof of Payment Attached',  'n' => 'proof_of_payment_attached','t' => 'select','r' => 1, 'o' => $yesNo],
        ['s' => 'Section 8 - Fees & Payment', 'l' => 'Proof of Payment Upload',    'n' => 'proof_of_payment_upload','t' => 'file',   'r' => 0],
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
