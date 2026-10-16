<?php

namespace Database\Seeders;

use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Database\Seeder;

class ServiceFormTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        // Get Conveyance Services parent (case-insensitive, trim-safe)
        $parent = \DB::table('service_parents')
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(service_name)) = ?', ['conveyance services'])
                    ->orWhereRaw('LOWER(TRIM(department_name)) = ?', ['property department'])
                    ->orWhereRaw('LOWER(TRIM(department_code)) IN (?, ?)', ['property_services', 'property department']);
            })
            ->orderBy('id')
            ->first();

        if (!$parent) {
            $parentId = \DB::table('service_parents')->insertGetId([
                'service_name' => 'Conveyance Services',
                'department_name' => 'Property Department',
                'department_code' => 'property_services',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->warn('Conveyance Services parent was missing and has been auto-created.');
        } else {
            $parentId = (int) $parent->id;

            // Normalize fields so Services page grouping (by department_code)
            // can always display this department.
            \DB::table('service_parents')
                ->where('id', $parentId)
                ->update([
                    'service_name' => 'Conveyance Services',
                    'department_name' => 'Property Department',
                    'department_code' => 'property_services',
                    'is_active' => 1,
                    'deleted_at' => null,
                    'updated_at' => now(),
                ]);
        }

        // Map service names to their configs
        $services = [
            [
                'name' => 'Scoping - Verification and Due Deligence',
                'code' => 'scoping_verification',
                'fields' => $this->getScopingVerificationFields(),
            ],
            [
                'name' => 'Chiefs Consent Application',
                'code' => 'chiefs_consent',
                'fields' => $this->getChiefsConsentFields(),
            ],
            [
                'name' => 'Application For Conversion - Council',
                'code' => 'app_conversion_council',
                'fields' => $this->getConversionCouncilFields(),
            ],
            [
                'name' => 'Land Lease And Title Registration',
                'code' => 'itt_application',
                'fields' => $this->getIttApplicationFields(),
            ],
            [
                'name' => 'Tittle Invitation',
                'code' => 'title_invitation',
                'fields' => $this->getTitleInvitationFields(),
            ],
            [
                'name' => 'Uplifting Of Title',
                'code' => 'uplifting_title',
                'fields' => $this->getUpliftingTitleFields(),
            ],
            [
                'name' => 'Regularisation Of Land',
                'code' => 'regularisation_of_land',
                'fields' => $this->getRegularisationOfLandFields(),
            ],
            [
                'name' => 'Assignment',
                'code' => 'assignment',
                'fields' => $this->getAssignmentFields(),
            ],
            [
                'name' => 'Registration Of Title',
                'code' => 'registration_of_title',
                'fields' => $this->getRegistrationOfTitleFields(),
            ],
            [
                'name' => 'Regularisation / Normalisation / Legalisation',
                'code' => 'regularisation_normalisation_legalisation',
                'fields' => $this->getRegularisationNormalisationLegalisationFields(),
            ],
        ];

        foreach ($services as $svc) {
            // Find or create service child
            $serviceChild = \DB::table('service_childrens')
                ->where('service_parent_id', $parentId)
                ->where('service_name', $svc['name'])
                ->first();

            if (!$serviceChild) {
                $childId = \DB::table('service_childrens')->insertGetId([
                    'service_parent_id' => $parentId,
                    'service_name' => $svc['name'],
                    'service_code' => $svc['code'],
                    'model' => $svc['name'],
                    'is_active' => 1,
                    'type' => 'new_acquisition',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $childId = $serviceChild->id;
            }

            // Create form template
            $template = ServiceFormTemplate::firstOrCreate(
                ['service_child_id' => $childId],
                [
                    'form_name' => $svc['name'],
                    'description' => 'Form for ' . $svc['name'],
                    'is_active' => true,
                ]
            );

            // Delete existing fields if updating
            ServiceFormField::where('service_form_template_id', $template->id)->delete();

            // Create fields
            foreach ($svc['fields'] as $fieldConfig) {
                ServiceFormField::create(array_merge(
                    $fieldConfig,
                    ['service_form_template_id' => $template->id]
                ));
            }

            $this->command->info("Created form template for: {$svc['name']}");
        }
    }

    private function getScopingVerificationFields(): array
    {
        return [
            [
                'field_name' => 'verification_owner',
                'field_label' => 'Verification of Actual Owner',
                'field_type' => 'checkbox',
                'options' => [
                    'dept_national_parks' => 'Department of National Parks (Game Park or GMA)',
                    'indigenous_pap' => 'Indigenous / PAP (NRC, COT, Proof of ownership)',
                    'institution_pap' => 'Institution / PAP (Certificate of incorporation, COT, Proof of ownership)',
                    'forest_reserve' => 'Forest Reserve',
                    'national_heritage' => 'National Heritage',
                    'military_installation' => 'Military Installation',
                    'print_register_lands' => 'Print out register (From Ministry of lands)',
                    'print_register_council' => 'Print out register (From the Council)',
                    'land_search_council' => 'Land Search (From the Council)',
                    'land_search_lands' => 'Land Search (From ministry of lands)',
                    'rda_compliance' => 'RDA Compliance (Road Reserve)',
                    'zambia_railway' => 'Zambia Railway Reserve',
                    'land_disputes' => 'Land Disputes',
                    'remote_sensing' => 'Remote Sensing report where necessary',
                ],
                'is_required' => true,
                'sort_order' => 1,
                'section' => 'Site Verification',
                'allow_comments' => true,
                'allow_attachment' => true,
                'allow_multiple_attachments' => true,
                'attachment_label' => 'Verification Documents',
            ],
            [
                'field_name' => 'encumbrance_type',
                'field_label' => 'Check Encumbrance (Select all that apply)',
                'field_type' => 'checkbox',
                'options' => [
                    'mortgage' => 'Mortgage',
                    'easement' => 'Easement',
                    'caveat' => 'Caveat',
                    'other' => 'Other (specify)',
                ],
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Site Verification',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'general_comments',
                'field_label' => 'Additional Comments',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Site Verification',
            ],
            [
                'field_name' => 'final_attachment',
                'field_label' => 'Final Attachments',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Site Verification',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }

    private function getChiefsConsentFields(): array
    {
        return [
            [
                'field_name' => 'area_ha',
                'field_label' => 'Area (Ha)',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Land Details',
            ],
            [
                'field_name' => 'province',
                'field_label' => 'Province',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 2,
                'section' => 'Location',
            ],
            [
                'field_name' => 'district',
                'field_label' => 'District',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 3,
                'section' => 'Location',
            ],
            [
                'field_name' => 'township',
                'field_label' => 'Township',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 4,
                'section' => 'Location',
            ],
            [
                'field_name' => 'village',
                'field_label' => 'Village',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Location',
            ],
            [
                'field_name' => 'chiefdom',
                'field_label' => 'Chiefdom',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 6,
                'section' => 'Location',
            ],
            [
                'field_name' => 'chief_name',
                'field_label' => 'King / Chiefs Name',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 7,
                'section' => 'Location',
            ],
            [
                'field_name' => 'coordinates',
                'field_label' => 'Coordinates',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Location',
            ],
            [
                'field_name' => 'road_landmark',
                'field_label' => 'Road / Land mark',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 9,
                'section' => 'Location',
            ],
            [
                'field_name' => 'application_letter',
                'field_label' => 'Application Letter',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 10,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'land_conversion_form_ii',
                'field_label' => 'Land Conversion Form II',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 11,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'endorsement_site_plans',
                'field_label' => 'Endorsement of Site Plans by the Chief',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 12,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'consent_offer_letter',
                'field_label' => 'Consent Letter / Offer letter',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 13,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'other_documents',
                'field_label' => 'Other Documents (Invoice, Certificate etc)',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 14,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'token_appreciation',
                'field_label' => 'Token of Appreciation',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 15,
                'section' => 'Cost',
                'allow_comments' => true,
                'attachment_label' => 'Request for Payment',
            ],
            [
                'field_name' => 'payment_attachment',
                'field_label' => 'Payment Attachment / Invoice',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 16,
                'section' => 'Cost',
                'allow_comments' => true,
            ],
        ];
    }

    private function getConversionCouncilFields(): array
    {
        $fields = [];
        $sortOrder = 1;

        // Section A: Required Documents
        $sectionADocs = [
            'application_letter' => 'Application Letter',
            'land_conversion_form_i' => 'Duly executed Land Conversion Form I',
            'land_conversion_form_ii' => 'Duly executed Land Conversion Form II',
            'endorsed_site_plans' => 'Endorsed Site Plans by the Chief',
            'consent_offer_letter' => 'Consent Letter / Offer letter by the Chief',
            'cert_incorporation' => 'Certificate of Incorporation',
            'duly_executed_annexure' => 'Duly executed Annexure (Form A or Form C)',
            'other_documents_a' => 'Other Documents (Invoice, Receipt, Certificate etc)',
            'mandatory_consents' => 'Other Mandatory Consents (Depts/Parks/Heritage)',
        ];

        foreach ($sectionADocs as $name => $label) {
            $fields[] = [
                'field_name' => $name,
                'field_label' => $label,
                'field_type' => 'file',
                'is_required' => !in_array($name, ['other_documents_a', 'consent_offer_letter'], true),
                'sort_order' => $sortOrder++,
                'section' => 'Section A - Required Documents',
                'allow_comments' => true,
            ];
        }

        // Cost section
        $fields[] = [
            'field_name' => 'land_fees_invoice',
            'field_label' => 'Land Fees Invoice',
            'field_type' => 'file',
            'is_required' => true,
            'sort_order' => $sortOrder++,
            'section' => 'Cost',
            'allow_comments' => true,
        ];

        // Section B: Uplifting Documents
        $sectionBDocs = [
            'recommendation_letter' => 'Recommendation Letter',
            'pwd_minutes' => 'PWD Minutes',
            'full_council_minutes' => 'Full Council Minutes',
            'approved_site_plan' => 'Approved Site Plan for Numbering',
            'duly_executed_annexure_b' => 'Duly executed Annexure (Form A or Form C)',
            'land_conversion_form_i_b' => 'Duly executed Land Conversion Form I',
            'land_conversion_form_ii_b' => 'Duly executed Land Conversion Form II',
            'land_conversion_form_iii' => 'Duly executed Land Conversion Form III',
            'cert_incorporation_b' => 'Certificate of Incorporation',
            'nrc_e_filer' => 'NRC – E-filer',
            'nrc_managing_director' => 'NRC – Managing Director',
            'nrc_company_secretary' => 'NRC – Company Secretary',
            'mandatory_consents_b' => 'Other Mandatory Consents',
            'remote_sensing_report' => 'Remote Sensing Report (if necessary)',
            'other_documents_b' => 'Other Documents (not mandatory)',
        ];

        foreach ($sectionBDocs as $name => $label) {
            $fields[] = [
                'field_name' => $name,
                'field_label' => $label,
                'field_type' => 'file',
                'is_required' => $name !== 'remote_sensing_report' && $name !== 'other_documents_b',
                'sort_order' => $sortOrder++,
                'section' => 'Section B - Uplifting Documents',
                'allow_comments' => true,
            ];
        }

        return $fields;
    }

    private function getIttApplicationFields(): array
    {
        $fields = [];
        $sortOrder = 1;

        // Section A: Required Documents
        $sectionADocs = [
            'application_letter'      => 'Application Letter',
            'land_conversion_form_i'  => 'Duly executed Land Conversion Form I',
            'land_conversion_form_ii' => 'Duly executed Land Conversion Form II',
            'endorsed_site_plans'     => 'Endorsed Site Plans by the Chief',
            'consent_offer_letter'    => 'Consent Letter / Offer letter by the Chief',
            'cert_incorporation'      => 'Certificate of Incorporation',
            'duly_executed_annexure'  => 'Duly executed Annexure (Form A or Form C)',
            'other_documents_a'       => 'Other Documents (Invoice, Receipt, Certificate etc)',
            'mandatory_consents'      => 'Other Mandatory Consents (Depts/Parks/Heritage)',
        ];

        foreach ($sectionADocs as $name => $label) {
            $fields[] = [
                'field_name'     => $name,
                'field_label'    => $label,
                'field_type'     => 'file',
                'is_required'    => !in_array($name, ['other_documents_a', 'consent_offer_letter'], true),
                'sort_order'     => $sortOrder++,
                'section'        => 'Section A - Required Documents',
                'allow_comments' => true,
            ];
        }

        // Cost section
        $fields[] = [
            'field_name'     => 'land_fees_invoice',
            'field_label'    => 'Land Fees Invoice',
            'field_type'     => 'file',
            'is_required'    => true,
            'sort_order'     => $sortOrder++,
            'section'        => 'Cost',
            'allow_comments' => true,
        ];

        // Section B: Uplifting Documents
        $sectionBDocs = [
            'recommendation_letter'   => 'Recommendation Letter',
            'pwd_minutes'             => 'PWD Minutes',
            'full_council_minutes'    => 'Full Council Minutes',
            'approved_site_plan'      => 'Approved Site Plan for Numbering',
            'duly_executed_annexure_b'=> 'Duly executed Annexure (Form A or Form C)',
            'land_conversion_form_i_b'=> 'Duly executed Land Conversion Form I',
            'land_conversion_form_ii_b'=> 'Duly executed Land Conversion Form II',
            'land_conversion_form_iii'=> 'Duly executed Land Conversion Form III',
            'cert_incorporation_b'   => 'Certificate of Incorporation',
            'nrc_e_filer'            => 'NRC – E-filer',
            'nrc_managing_director'  => 'NRC – Managing Director',
            'nrc_company_secretary'  => 'NRC – Company Secretary',
            'mandatory_consents_b'   => 'Other Mandatory Consents',
            'remote_sensing_report'  => 'Remote Sensing Report (if necessary)',
            'other_documents_b'      => 'Other Documents (not mandatory)',
        ];

        foreach ($sectionBDocs as $name => $label) {
            $fields[] = [
                'field_name'     => $name,
                'field_label'    => $label,
                'field_type'     => 'file',
                'is_required'    => !in_array($name, ['remote_sensing_report', 'other_documents_b'], true),
                'sort_order'     => $sortOrder++,
                'section'        => 'Section B - Uplifting Documents',
                'allow_comments' => true,
            ];
        }

        // Lodge a Numbered Site Plan with plot number input
        $fields[] = [
            'field_name'     => 'plot_number',
            'field_label'    => 'Plot Number',
            'field_type'     => 'text',
            'is_required'    => true,
            'sort_order'     => $sortOrder++,
            'section'        => 'Lodge Numbered Site Plan',
        ];

        $fields[] = [
            'field_name'     => 'numbered_site_plan',
            'field_label'    => 'Lodge a Numbered Site Plan',
            'field_type'     => 'file',
            'is_required'    => true,
            'sort_order'     => $sortOrder++,
            'section'        => 'Lodge Numbered Site Plan',
            'allow_comments' => true,
        ];

        // Lease Execution Documents

        // Lease Execution Documents (merged from standalone service)
        $leaseExecutionDocs = [
            'md_nrc'         => 'Managing Director NRC',
            'cs_nrc'         => 'Company Secretary NRC',
            'supervisor_nrc' => 'Supervisor NRC',
        ];

        foreach ($leaseExecutionDocs as $name => $label) {
            $fields[] = [
                'field_name'     => $name,
                'field_label'    => $label,
                'field_type'     => 'file',
                'is_required'    => true,
                'sort_order'     => $sortOrder++,
                'section'        => 'Lease Execution',
                'allow_comments' => true,
            ];
        }

        return $fields;
    }

    private function getTitleInvitationFields(): array
    {
        $docs = [
            'nrc_md' => 'Copy of National Registration Card (NRC) (Managing Director)',
            'nrc_cs' => 'Copy of National Registration Card (NRC) (Company Secretary)',
            'cert_incorporation' => 'Certificate of Incorporation',
            'offer_letter_copy' => 'Copy of the Offer Letter',
            'itt_payment_proof' => 'Proof of ITT Payment',
            'survey_diagram' => 'Approved Survey Diagram',
        ];

        $fields = [];
        $sortOrder = 1;

        foreach ($docs as $name => $label) {
            $fields[] = [
                'field_name' => $name,
                'field_label' => $label,
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => $sortOrder++,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ];
        }

        return $fields;
    }

    private function getUpliftingTitleFields(): array
    {
        return [
            [
                'field_name' => 'cert_title_cover',
                'field_label' => 'Certificate of Title - Front Page (Cover Page)',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 1,
                'section' => 'Certificate Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'cert_title_memorials',
                'field_label' => 'Certificate of Title - Memorials Page',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Certificate Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'cadaster_survey_diagram',
                'field_label' => 'Cadaster Survey Diagram',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 3,
                'section' => 'Certificate Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'lease_agreement',
                'field_label' => 'Lease Agreement',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 4,
                'section' => 'Certificate Documents',
                'allow_comments' => true,
            ],
        ];
    }

    private function getRegularisationOfLandFields(): array
    {
        return [
            [
                'field_name' => 'spatial_planning_note',
                'field_label' => 'Important: Before proceeding with regularisation, you must first request for spatial planning (creation of a site plan). Please ensure this is completed before submitting this application.',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Instructions',
            ],
            [
                'field_name' => 'spatial_unit_type',
                'field_label' => 'Spatial Unit Type',
                'field_type' => 'select',
                'options' => [
                    'council' => 'Council',
                    'private_owner' => 'Private Owner',
                    'ministry_lands' => 'Ministry of Lands',
                ],
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Spatial Unit Details',
            ],
            [
                'field_name' => 'normalization_application_council',
                'field_label' => 'Normalization Application to the Council',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 3,
                'section' => 'Applications',
                'allow_comments' => true,
                'attachment_label' => 'Attach site plan',
            ],
            [
                'field_name' => 'ministry_lands_application',
                'field_label' => 'Ministry of Lands Application to Commissioner of Lands',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Applications',
                'allow_comments' => true,
                'attachment_label' => 'Attach site plan',
            ],
            [
                'field_name' => 'application_letter',
                'field_label' => 'Application Letter',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 5,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'cert_incorporation',
                'field_label' => 'Certificate of Incorporation',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 6,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'duly_executed_annexure',
                'field_label' => 'Duly Executed Annexure (Form A or Form C)',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 7,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'other_documents',
                'field_label' => 'Other Documents (Invoice, Receipt, Certificate etc)',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Required Documents',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }

    private function getAssignmentFields(): array
    {
        return [
            [
                'field_name' => 'vendor_address',
                'field_label' => 'Vendor Address',
                'field_type' => 'textarea',
                'is_required' => true,
                'sort_order' => 1,
                'section' => 'Vendor Details',
            ],
            [
                'field_name' => 'vendor_phone',
                'field_label' => 'Vendor Phone Number',
                'field_type' => 'text',
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Vendor Details',
            ],
            [
                'field_name' => 'assignment_drafting',
                'field_label' => 'Assignment Drafting',
                'field_type' => 'select',
                'options' => [
                    'vendor' => 'Vendor',
                    'purchaser' => 'Purchaser',
                ],
                'is_required' => true,
                'sort_order' => 3,
                'section' => 'Assignment Process',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'assignment_drafting_attachment',
                'field_label' => 'Assignment Drafting Attachment',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Assignment Process',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'review_of_assignment',
                'field_label' => 'Review of Assignment',
                'field_type' => 'select',
                'options' => [
                    'vendor' => 'Vendor',
                    'purchaser' => 'Purchaser',
                ],
                'is_required' => true,
                'sort_order' => 5,
                'section' => 'Assignment Process',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'review_of_assignment_attachment',
                'field_label' => 'Review of Assignment Attachment',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 6,
                'section' => 'Assignment Process',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'md_nrc',
                'field_label' => 'Managing Director NRC',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 7,
                'section' => 'Execution by ZESCO Signatories',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'cs_nrc',
                'field_label' => 'Company Secretary NRC',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 8,
                'section' => 'Execution by ZESCO Signatories',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'executed_assignment',
                'field_label' => 'Executed Assignment (Vendor - Purchaser)',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 9,
                'section' => 'Execution & Payment',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'request_final_payment',
                'field_label' => 'Request Final Payment (to Finance)',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 10,
                'section' => 'Execution & Payment',
                'allow_comments' => true,
                'attachment_label' => 'Attach memo and executed contract',
            ],
            [
                'field_name' => 'expected_completion_date',
                'field_label' => 'Expected Date of Completion',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 11,
                'section' => 'Execution & Payment',
            ],
            [
                'field_name' => 'payment_status',
                'field_label' => 'Payment Status',
                'field_type' => 'select',
                'options' => [
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                ],
                'is_required' => true,
                'sort_order' => 12,
                'section' => 'Execution & Payment',
            ],
            [
                'field_name' => 'payment_mode',
                'field_label' => 'Mode of Payment',
                'field_type' => 'select',
                'options' => [
                    'stage_1' => 'Stage 1',
                    'stage_2' => 'Stage 2',
                    'stage_3' => 'Stage 3',
                ],
                'is_required' => false,
                'sort_order' => 13,
                'section' => 'Execution & Payment',
            ],
            [
                'field_name' => 'consent_to_assign',
                'field_label' => 'Consent Application',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 14,
                'section' => 'Consents & Approvals',
                'allow_comments' => true,
                'attachment_label' => 'Attach Consent to Assign',
            ],
            [
                'field_name' => 'ptt_application',
                'field_label' => 'Property Transfer Tax Application',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 15,
                'section' => 'Consents & Approvals',
                'allow_comments' => true,
                'attachment_label' => 'Attach PTT clearance',
            ],
            [
                'field_name' => 'ptt_completion_date',
                'field_label' => 'PTT Expected Date of Completion',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 16,
                'section' => 'Consents & Approvals',
            ],
            [
                'field_name' => 'ptt_payment_status',
                'field_label' => 'PTT Payment Status',
                'field_type' => 'select',
                'options' => [
                    'paid' => 'Paid',
                    'pending' => 'Pending',
                ],
                'is_required' => false,
                'sort_order' => 17,
                'section' => 'Consents & Approvals',
            ],
            [
                'field_name' => 'deduce_title',
                'field_label' => 'Deduce Title',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 18,
                'section' => 'Title Documents',
                'allow_comments' => true,
                'attachment_label' => 'Attach Vendor\'s Title',
            ],
            [
                'field_name' => 'assignment_preparation',
                'field_label' => 'Assignment Preparation',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 19,
                'section' => 'Title Documents',
                'allow_comments' => true,
                'attachment_label' => 'Attach Assignment',
            ],
            [
                'field_name' => 'completion_at_lawyers',
                'field_label' => 'Completion @ Lawyers Chambers (Exchange of duly executed Assignment, PTT receipt & Tax clearance certificate, balance payment, Vendor COT)',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 20,
                'section' => 'Final Completion',
            ],
            [
                'field_name' => 'marking_off',
                'field_label' => 'Marking Off',
                'field_type' => 'checkbox',
                'options' => [
                    'marking_off' => 'Mark as completed',
                ],
                'is_required' => false,
                'sort_order' => 21,
                'section' => 'Final Completion',
            ],
            [
                'field_name' => 'final_completion_date',
                'field_label' => 'Expected Date of Final Completion',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 22,
                'section' => 'Final Completion',
            ],
        ];
    }

    private function getRegistrationOfTitleFields(): array
    {
        return [
            [
                'field_name' => 'cert_incorporation',
                'field_label' => 'Certificate of Incorporation',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 1,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'vendor_title_deed',
                'field_label' => 'Vendor\'s Title Deed',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'deed_of_assignment',
                'field_label' => 'Deed of Assignment',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 3,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'ptt_clearance_cert',
                'field_label' => 'PTT Clearance Certificate',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 4,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'ptt_tax_receipt',
                'field_label' => 'PTT Tax Receipt',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 5,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'consent_to_assign',
                'field_label' => 'Consent to Assign',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 6,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'vendor_nrc_or_cert',
                'field_label' => 'Vendor\'s Certified National Registration Card / Certificate of Incorporation',
                'field_type' => 'select',
                'options' => [
                    'nrc' => 'National Registration Card',
                    'cert_incorporation' => 'Certificate of Incorporation',
                ],
                'is_required' => true,
                'sort_order' => 7,
                'section' => 'Verification',
            ],
            [
                'field_name' => 'company_documents_resolutions',
                'field_label' => 'Company Documents and Resolutions',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Company Details',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
            [
                'field_name' => 'ground_rent_clearance',
                'field_label' => 'Ground Rent Clearance Receipt',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 9,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'title_registration_fees',
                'field_label' => 'Payment of the 1% Title Registration Fees (Maximum ZMW 15,000)',
                'field_type' => 'number',
                'is_required' => true,
                'sort_order' => 10,
                'section' => 'Payment',
            ],
            [
                'field_name' => 'payment_proof',
                'field_label' => 'Payment Status (Comment)',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 11,
                'section' => 'Payment',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'expected_completion_date',
                'field_label' => 'Expected Date of Completion',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 12,
                'section' => 'Completion',
            ],
        ];
    }

    private function getRegularisationNormalisationLegalisationFields(): array
    {
        return [
            [
                'field_name' => 'spatial_planning_note',
                'field_label' => 'Important: Before proceeding with this service, please request for spatial planning services first.',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Instructions',
            ],
            [
                'field_name' => 'application_letter',
                'field_label' => 'Application Letter',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 2,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'cert_incorporation',
                'field_label' => 'Certificate of Incorporation',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 3,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'duly_executed_annexure',
                'field_label' => 'Duly Executed Annexure (Form A or Form C)',
                'field_type' => 'file',
                'is_required' => true,
                'sort_order' => 4,
                'section' => 'Required Documents',
                'allow_comments' => true,
            ],
            [
                'field_name' => 'other_documents',
                'field_label' => 'Other Documents (Invoice, Receipt, Certificate etc)',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Required Documents',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }
}
