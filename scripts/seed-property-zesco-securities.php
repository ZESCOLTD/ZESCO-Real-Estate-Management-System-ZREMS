<?php
/**
 * Seed Property Department → Mortgages & Securities Management → ZESCO Securities (child #279)
 * Source spec: "ORGANISATIONAL SECURITIES MANAGEMENT AND REGISTRATION FORM"
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
        ->where('service_name', 'ZESCO Securities')
        ->first();
    if (! $child) {
        throw new RuntimeException('Child "ZESCO Securities" not found under Property Department');
    }

    $template = ServiceFormTemplate::updateOrCreate(
        ['service_child_id' => $child->id],
        [
            'form_name'   => 'Organisational Securities Management and Registration Form',
            'description' => 'ZESCO Securities — Organisational Securities Management and Registration.',
            'is_active'   => 1,
        ]
    );

    $countBefore = ServiceFormField::where('service_form_template_id', $template->id)->count();
    ServiceFormField::where('service_form_template_id', $template->id)->delete();

    $yesNo = ['Yes', 'No'];

    $securityTypes = [
        'Mortgage', 'Charge', 'Debenture', 'Fixed Charge', 'Floating Charge', 'Legal Mortgage',
        'Equitable Mortgage', 'Lien', 'Pledge', 'Assignment of Receivables', 'Bank Guarantee',
        'Performance Bond', 'Bid/Tender Security', 'Retention/Collateral Security',
        'Employee Loan Security', 'Other',
    ];
    $purposes = [
        'Loan Financing', 'Employee Loan/Bridging Loan', 'Project Financing',
        'Contractual Obligation', 'Performance Security', 'Debt Security', 'Other',
    ];
    $securedPartyTypes = [
        'Bank', 'Financial Institution', 'Government Institution', 'Corporate Entity',
        'Individual', 'Insurance Company', 'Other',
    ];
    $providerTypes = [
        'Individual', 'Company', 'Government Entity', 'Employee', 'Contractor', 'Supplier', 'Other',
    ];
    $assetTypes = [
        'Land', 'Building', 'Motor Vehicle', 'Plant & Machinery', 'Shares', 'Receivables',
        'Bank Account', 'Contract Rights', 'Intellectual Property', 'Equipment', 'Other',
    ];
    $registrationTypes = [
        'Lands Registry', 'PACRA', 'Motor Vehicle Registry', 'Court/Registry',
        'Bank/Financial Institution', 'Other',
    ];
    $releaseReasons = [
        'Loan Fully Paid', 'Partial Repayment', 'Replacement Security', 'Refinancing',
        'Settlement', 'Expiry', 'Other',
    ];

    $fields = [
        // 0. HEADER / ORG DETAILS
        ['s' => 'Section 0 - Organisation Details', 'l' => 'Organisation',            'n' => 'organisation',            't' => 'text', 'r' => 1],
        ['s' => 'Section 0 - Organisation Details', 'l' => 'Department / Unit',       'n' => 'department_unit',         't' => 'text', 'r' => 1],
        ['s' => 'Section 0 - Organisation Details', 'l' => 'Security Reference No.', 'n' => 'security_reference_no',    't' => 'text', 'r' => 1],
        ['s' => 'Section 0 - Organisation Details', 'l' => 'LAMS Security ID',       'n' => 'lams_security_id',         't' => 'text', 'r' => 0],
        ['s' => 'Section 0 - Organisation Details', 'l' => 'Date Created',           'n' => 'date_created',             't' => 'date', 'r' => 1],
        ['s' => 'Section 0 - Organisation Details', 'l' => 'Related Property / Asset ID', 'n' => 'related_property_asset_id', 't' => 'text', 'r' => 0],

        // 1. TRANSACTION DETAILS
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Type of Security',                'n' => 'type_of_security',          't' => 'select', 'r' => 1, 'o' => $securityTypes],
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Type of Security (Other - specify)', 'n' => 'type_of_security_other', 't' => 'text',   'r' => 0],
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Purpose of Security',             'n' => 'purpose_of_security',       't' => 'select', 'r' => 1, 'o' => $purposes],
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Purpose of Security (Other - specify)', 'n' => 'purpose_of_security_other', 't' => 'text', 'r' => 0],
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Transaction / Project Name',     'n' => 'transaction_project_name',  't' => 'text',   'r' => 1],
        ['s' => 'Section 1 - Transaction Details', 'l' => 'Transaction Reference No.',      'n' => 'transaction_reference_no',  't' => 'text',   'r' => 1],

        // 2. SECURED PARTY / BENEFICIARY
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Name of Secured Party',       'n' => 'secured_party_name',         't' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Entity Type',                 'n' => 'secured_party_entity_type',  't' => 'select', 'r' => 1, 'o' => $securedPartyTypes],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Entity Type (Other - specify)', 'n' => 'secured_party_entity_type_other', 't' => 'text', 'r' => 0],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Registered Address',          'n' => 'secured_party_address',      't' => 'textarea', 'r' => 1],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Contact Person',              'n' => 'secured_party_contact_person','t' => 'text',   'r' => 1],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Telephone',                   'n' => 'secured_party_telephone',    't' => 'tel',    'r' => 1],
        ['s' => 'Section 2 - Secured Party / Beneficiary', 'l' => 'Email',                       'n' => 'secured_party_email',        't' => 'email',  'r' => 1],

        // 3. SECURITY PROVIDER / DEBTOR
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Name',                        'n' => 'provider_name',              't' => 'text',   'r' => 1],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Entity Type',                 'n' => 'provider_entity_type',       't' => 'select', 'r' => 1, 'o' => $providerTypes],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Entity Type (Other - specify)', 'n' => 'provider_entity_type_other', 't' => 'text',  'r' => 0],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Registration / NRC No.',      'n' => 'provider_registration_nrc',  't' => 'text',   'r' => 1],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'TPIN',                        'n' => 'provider_tpin',              't' => 'text',   'r' => 0],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Address',                     'n' => 'provider_address',           't' => 'textarea', 'r' => 1],
        ['s' => 'Section 3 - Security Provider / Debtor', 'l' => 'Contact Details',             'n' => 'provider_contact_details',   't' => 'text',   'r' => 1],

        // 4. COLLATERAL / SECURITY DESCRIPTION
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Description of Security',       'n' => 'security_description',      't' => 'textarea', 'r' => 1],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Security Asset Type',           'n' => 'security_asset_type',       't' => 'select',   'r' => 1, 'o' => $assetTypes],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Security Asset Type (Other - specify)', 'n' => 'security_asset_type_other', 't' => 'text', 'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Plot / Stand No.',              'n' => 'plot_stand_no',             't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Title Deed No.',                'n' => 'title_deed_no',             't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Lease No.',                     'n' => 'lease_no',                  't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Property Location',             'n' => 'property_location',         't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Tenure',                        'n' => 'tenure',                    't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Area',                          'n' => 'area',                      't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Registered Owner',              'n' => 'registered_owner',          't' => 'text',     'r' => 0],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Property Value (ZMW)',          'n' => 'property_value_zmw',        't' => 'number',   'r' => 1],
        ['s' => 'Section 4 - Collateral / Security Description', 'l' => 'Forced Sale / Liquidation Value (ZMW)', 'n' => 'forced_sale_value_zmw', 't' => 'number', 'r' => 0],

        // 5. REGISTRATION DETAILS
        ['s' => 'Section 5 - Registration Details', 'l' => 'Registration Authority',            'n' => 'registration_authority',     't' => 'text',   'r' => 1],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Type of Registration',              'n' => 'type_of_registration',       't' => 'select', 'r' => 1, 'o' => $registrationTypes],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Type of Registration (Other - specify)', 'n' => 'type_of_registration_other', 't' => 'text', 'r' => 0],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Registration / Application No.',    'n' => 'registration_application_no','t' => 'text',   'r' => 1],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Date Submitted',                    'n' => 'date_submitted',             't' => 'date',   'r' => 1],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Date Registered',                   'n' => 'date_registered',            't' => 'date',   'r' => 0],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Registration Certificate No.',      'n' => 'registration_certificate_no','t' => 'text',   'r' => 0],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Priority / Ranking Confirmed',      'n' => 'priority_ranking_confirmed', 't' => 'select', 'r' => 1, 'o' => $yesNo],
        ['s' => 'Section 5 - Registration Details', 'l' => 'Original Document Location',        'n' => 'original_document_location', 't' => 'text',   'r' => 1],

        // 6. RELEASE / DISCHARGE OF SECURITY
        ['s' => 'Section 6 - Release / Discharge of Security', 'l' => 'Reason for Release',       'n' => 'reason_for_release',        't' => 'select', 'r' => 0, 'o' => $releaseReasons],
        ['s' => 'Section 6 - Release / Discharge of Security', 'l' => 'Reason for Release (Other - specify)', 'n' => 'reason_for_release_other', 't' => 'text', 'r' => 0],
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
