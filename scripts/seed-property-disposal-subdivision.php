<?php
/**
 * Seed Property Department → Disposal → Disposal of Subdivision (child #244)
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
        ['service_parent_id' => $parent->id, 'service_name' => 'Disposal of Subdivision'],
        ['type' => 'new_acquisition', 'is_active' => 1]
    );

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name' => 'Subdivision & Disposal of Land Form',
            'description' => 'ZESCO Limited — Subdivision and Disposal of Land Form.',
            'is_active' => 1,
        ]
    );

    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $disposalMethods = ['Approved Direct Sale', 'Sitting Tenant', 'Transfer/Exchange', 'Donation', 'Other'];

    $fields = [
        // HEADER
        ['s' => 'Header', 'l' => 'Form No.', 'n' => 'form_no', 't' => 'text', 'r' => 0],
        ['s' => 'Header', 'l' => 'LAMS Reference No.', 'n' => 'lams_reference_no', 't' => 'text', 'r' => 1],
        ['s' => 'Header', 'l' => 'Parent Property / Asset No.', 'n' => 'parent_property_asset_no', 't' => 'text', 'r' => 1],
        ['s' => 'Header', 'l' => 'Date', 'n' => 'form_date', 't' => 'date', 'r' => 1],

        // PART A – PARENT PROPERTY DETAILS
        ['s' => 'Part A - Parent Property Details', 'l' => 'Registered Owner', 'n' => 'registered_owner', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Plot / Stand No.', 'n' => 'plot_stand_no', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Title Deed No.', 'n' => 'title_deed_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Property / Asset No.', 'n' => 'property_asset_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Location', 'n' => 'location', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'District', 'n' => 'district', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Province', 'n' => 'province', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Total Land Extent', 'n' => 'total_land_extent', 't' => 'text', 'r' => 1],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Current Use', 'n' => 'current_use', 't' => 'text', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Existing Development', 'n' => 'existing_development', 't' => 'textarea', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Current Market Value (K)', 'n' => 'current_market_value', 't' => 'number', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Encumbrances', 'n' => 'encumbrances', 't' => 'textarea', 'r' => 0],
        ['s' => 'Part A - Parent Property Details', 'l' => 'Attach Mother Title', 'n' => 'mother_title_attachment', 't' => 'file', 'r' => 0],

        // PART B – SUBDIVISION DETAILS
        ['s' => 'Part B - Subdivision Details', 'l' => 'Proposed Subdivision', 'n' => 'proposed_subdivision_table', 't' => 'table', 'r' => 1,
            'o' => ['columns' => ['Parcel', 'Proposed Plot No.', 'Proposed Area', 'Proposed Use', 'Proposed Disposal'],
                    'rows' => ['1', '2', '3', '4']]],
        ['s' => 'Part B - Subdivision Details', 'l' => 'Area Retained by ZESCO (ha)', 'n' => 'area_retained_zesco', 't' => 'number', 'r' => 0],
        ['s' => 'Part B - Subdivision Details', 'l' => 'Access Road / Wayleave / Utility Corridor Reserved (ha)', 'n' => 'access_road_wayleave_reserved', 't' => 'number', 'r' => 0],
        ['s' => 'Part B - Subdivision Details', 'l' => 'Balance / Residual Parcel', 'n' => 'balance_residual_parcel', 't' => 'text', 'r' => 0],
        ['s' => 'Part B - Subdivision Details', 'l' => 'Attach Proposed Site Plan', 'n' => 'proposed_site_plan_attachment', 't' => 'file', 'r' => 0],

        // PART C – DISPOSAL METHOD
        ['s' => 'Part C - Disposal Method', 'l' => 'Disposal Method (per Parcel)', 'n' => 'disposal_method_table', 't' => 'table', 'r' => 1,
            'o' => ['columns' => ['Parcel', 'Disposal Method', 'Intended Purchaser / Beneficiary', 'Approved Price (K)'],
                    'rows' => ['1', '2', '3', '4']]],
        ['s' => 'Part C - Disposal Method', 'l' => 'Overall Disposal Method', 'n' => 'overall_disposal_method', 't' => 'select', 'r' => 1, 'o' => $disposalMethods],
        ['s' => 'Part C - Disposal Method', 'l' => 'Disposal Method (Other - specify)', 'n' => 'overall_disposal_method_other', 't' => 'text', 'r' => 0],
        ['s' => 'Part C - Disposal Method', 'l' => 'Attach Approval for Disposal', 'n' => 'disposal_approval_attachment', 't' => 'file', 'r' => 0],

        // PART K – DISPOSAL TRANSACTION
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Purchaser / Beneficiary Name', 'n' => 'purchaser_name', 't' => 'text', 'r' => 1],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'NRC / Registration No.', 'n' => 'purchaser_nrc_reg_no', 't' => 'text', 'r' => 1],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Address', 'n' => 'purchaser_address', 't' => 'textarea', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Contact', 'n' => 'purchaser_contact', 't' => 'text', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Parcel Purchased', 'n' => 'parcel_purchased', 't' => 'text', 'r' => 1],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Purchase Price (K)', 'n' => 'purchase_price', 't' => 'number', 'r' => 1],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Agreement Reference', 'n' => 'agreement_reference', 't' => 'text', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Agreement Date', 'n' => 'agreement_date', 't' => 'date', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Date Lodged', 'n' => 'date_lodged', 't' => 'date', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Date Registered', 'n' => 'date_registered', 't' => 'date', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'New Owner', 'n' => 'new_owner', 't' => 'text', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'New Title No.', 'n' => 'new_title_no', 't' => 'text', 'r' => 0],
        ['s' => 'Part K - Disposal Transaction', 'l' => 'Attach Proposed Site Plan (Final)', 'n' => 'final_site_plan_attachment', 't' => 'file', 'r' => 0],
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
