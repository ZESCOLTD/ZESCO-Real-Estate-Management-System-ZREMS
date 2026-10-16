<?php

namespace Database\Seeders;

use App\Models\ServiceFormField;
use App\Models\ServiceFormTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SheqServiceFormTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $parent = DB::table('service_parents')
            ->where(function ($query) {
                $query->whereRaw('LOWER(TRIM(department_name)) IN (?, ?)', ['sheq department', 'sheq checklist'])
                    ->orWhereRaw('LOWER(TRIM(department_code)) IN (?, ?)', ['sheq services', 'sheq_services'])
                    ->orWhereRaw('LOWER(TRIM(service_name)) = ?', ['sheq checklist']);
            })
            ->first();

        if (! $parent) {
            $parentId = DB::table('service_parents')->insertGetId([
                'service_name' => 'SHEQ Checklist',
                'department_name' => 'SHEQ Department',
                'department_code' => 'sheq_services',
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->command->warn('SHEQ parent was missing and has been auto-created.');
        } else {
            $parentId = $parent->id;
        }

        DB::table('service_parents')
            ->where('id', $parentId)
            ->update([
                'service_name' => 'SHEQ Checklist',
                'updated_at' => now(),
            ]);

        $service = [
            'name' => 'SHEQ Checklist for Land and Building Acquisition and Management',
            'code' => 'sheq_land_building_acquisition_management',
            'fields' => $this->getSheqChecklistFields(),
        ];

        $serviceChild = DB::table('service_childrens')
            ->where('service_parent_id', $parentId)
            ->where('service_name', $service['name'])
            ->first();

        if (! $serviceChild) {
            $childId = DB::table('service_childrens')->insertGetId([
                'service_parent_id' => $parentId,
                'service_name' => $service['name'],
                'service_code' => $service['code'],
                'model' => $service['name'],
                'is_active' => 1,
                'type' => 'new_acquisition',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $childId = $serviceChild->id;

            DB::table('service_childrens')
                ->where('id', $childId)
                ->update([
                    'service_code' => $service['code'],
                    'model' => $service['name'],
                    'is_active' => 1,
                    'type' => 'new_acquisition',
                    'updated_at' => now(),
                ]);
        }

        $template = ServiceFormTemplate::firstOrCreate(
            ['service_child_id' => $childId],
            [
                'form_name' => $service['name'],
                'description' => 'Form for '.$service['name'],
                'is_active' => true,
            ]
        );

        ServiceFormField::where('service_form_template_id', $template->id)->delete();

        foreach ($service['fields'] as $fieldConfig) {
            ServiceFormField::create(array_merge(
                $fieldConfig,
                ['service_form_template_id' => $template->id]
            ));
        }

        $this->command->info("Created form template for: {$service['name']}");
    }

    private function getSheqChecklistFields(): array
    {
        return [
            [
                'field_name' => 'seller_details',
                'field_label' => 'Seller Details',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 1,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'property_address',
                'field_label' => 'Property Address',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 2,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'intended_use',
                'field_label' => 'Intended Use',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 3,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'property_type',
                'field_label' => 'Property Type',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 4,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'existing_structures',
                'field_label' => 'Existing Structures',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 5,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'date_of_inspection',
                'field_label' => 'Date of Inspection',
                'field_type' => 'date',
                'is_required' => false,
                'sort_order' => 6,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'due_diligence_team',
                'field_label' => 'Due Diligence Team',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 7,
                'section' => '1. Property Details',
            ],
            [
                'field_name' => 'site_location_verified',
                'field_label' => 'Site location verified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 8,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'land_ownership_information_received',
                'field_label' => 'Land ownership information received',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 9,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'existing_developments_identified',
                'field_label' => 'Existing developments identified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 10,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'site_access_available',
                'field_label' => 'Site access available',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 11,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'flooding_or_drainage_concerns_observed',
                'field_label' => 'Flooding or drainage concerns observed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 12,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'protected_or_sensitive_environmental_areas_identified',
                'field_label' => 'Protected or sensitive environmental areas identified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 13,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'evidence_of_contamination_or_illegal_dumping_observed',
                'field_label' => 'Evidence of contamination or illegal dumping observed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 14,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'adjacent_land_uses_reviewed',
                'field_label' => 'Adjacent land uses reviewed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 15,
                'section' => '2. Site Screening',
            ],
            [
                'field_name' => 'environmental_site_assessment_completed',
                'field_label' => 'Environmental Site Assessment completed (where required)',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 16,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'historical_land_use_reviewed',
                'field_label' => 'Historical land use reviewed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 17,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'hazardous_materials_identified',
                'field_label' => 'Hazardous materials identified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 18,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'soil_or_groundwater_contamination_assessed',
                'field_label' => 'Soil or groundwater contamination assessed (if required)',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 19,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'water_source_and_permits_verified',
                'field_label' => 'Water source and permits verified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 20,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'environmental_permits_reviewed',
                'field_label' => 'Environmental permits reviewed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 21,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'waste_management_practices_assessed',
                'field_label' => 'Waste management practices assessed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 22,
                'section' => '3. Environmental',
            ],
            [
                'field_name' => 'fire_safety_systems_inspected',
                'field_label' => 'Fire safety systems inspected',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 23,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'electrical_safety_assessed',
                'field_label' => 'Electrical safety assessed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 24,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'asbestos_lead_paint_assessment_completed',
                'field_label' => 'Asbestos/lead paint assessment completed (where applicable)',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 25,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'indoor_air_quality_and_mould_assessed',
                'field_label' => 'Indoor air quality and mould assessed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 26,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'slip_trip_and_fall_hazards_identified',
                'field_label' => 'Slip, trip and fall hazards identified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 27,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'emergency_lighting_and_exits_verified',
                'field_label' => 'Emergency lighting and exits verified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 28,
                'section' => '4. Health & Safety Checklist',
            ],
            [
                'field_name' => 'maintenance_records_reviewed',
                'field_label' => 'Maintenance records reviewed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 29,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'site_security_assessed',
                'field_label' => 'Site security assessed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 30,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'accessibility_reviewed',
                'field_label' => 'Accessibility reviewed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 31,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'parking_and_traffic_flow_assessed',
                'field_label' => 'Parking and traffic flow assessed',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 32,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'third_party_rights_easements_identified',
                'field_label' => 'Third-party rights/easements identified',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 33,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'photographic_evidence_collected',
                'field_label' => 'Photographic evidence collected',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 34,
                'section' => '5. Operational',
            ],
            [
                'field_name' => 'environmental_risk',
                'field_label' => 'Environmental',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 35,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'health_risk',
                'field_label' => 'Health',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 36,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'safety_risk',
                'field_label' => 'Safety',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 37,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'operational_risk',
                'field_label' => 'Operational',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 38,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'legal_compliance_risk',
                'field_label' => 'Legal/Compliance',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 39,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'security_risk',
                'field_label' => 'Security',
                'field_type' => 'select',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High'],
                'is_required' => false,
                'sort_order' => 40,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'risk_decision',
                'field_label' => '6.1 Risk Decision',
                'field_type' => 'select',
                'options' => [
                    'acceptable' => 'Acceptable',
                    'acceptable_with_conditions' => 'Acceptable with Conditions',
                    'unacceptable' => 'Unacceptable',
                ],
                'is_required' => false,
                'sort_order' => 41,
                'section' => '6. Risk Assessment',
            ],
            [
                'field_name' => 'environmental_issues_addressed',
                'field_label' => 'Environmental issues addressed',
                'field_type' => 'select',
                'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'],
                'is_required' => false,
                'sort_order' => 42,
                'section' => '6.2 Corrective Actions',
            ],
            [
                'field_name' => 'safety_issues_corrected',
                'field_label' => 'Safety issues corrected',
                'field_type' => 'select',
                'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'],
                'is_required' => false,
                'sort_order' => 43,
                'section' => '6.2 Corrective Actions',
            ],
            [
                'field_name' => 'statutory_documents_verified',
                'field_label' => 'Statutory documents verified',
                'field_type' => 'select',
                'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'],
                'is_required' => false,
                'sort_order' => 44,
                'section' => '6.2 Corrective Actions',
            ],
            [
                'field_name' => 'required_inspections_completed',
                'field_label' => 'Required inspections completed',
                'field_type' => 'select',
                'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'],
                'is_required' => false,
                'sort_order' => 45,
                'section' => '6.2 Corrective Actions',
            ],
            [
                'field_name' => 'outstanding_risks_mitigated',
                'field_label' => 'Outstanding risks mitigated',
                'field_type' => 'select',
                'options' => ['open' => 'Open', 'in_progress' => 'In Progress', 'completed' => 'Completed'],
                'is_required' => false,
                'sort_order' => 46,
                'section' => '6.2 Corrective Actions',
            ],
            [
                'field_name' => 'recommendation',
                'field_label' => 'Recommendation',
                'field_type' => 'select',
                'options' => [
                    'approve' => 'Approve – No significant SHEQ risks identified.',
                    'conditional_approve' => 'Conditionally Approve – Acquisition may proceed after specified actions are completed.',
                    'reject' => 'Reject – SHEQ risks are unacceptable or cannot be reasonably mitigated.',
                ],
                'is_required' => false,
                'sort_order' => 47,
                'section' => '7. Recommendation',
            ],
            [
                'field_name' => 'remarks',
                'field_label' => 'Remarks',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 48,
                'section' => '7. Recommendation',
            ],
        ];
    }
}
