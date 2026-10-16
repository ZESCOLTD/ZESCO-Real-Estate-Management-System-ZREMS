<?php

namespace Database\Seeders;

use App\Models\ServiceFormTemplate;
use App\Models\ServiceFormField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecurityServiceFormTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        $parent = DB::table('service_parents')
            ->where('department_code', 'security_services')
            ->first();

        if (!$parent) {
            $this->command->warn('Security Department parent not found.');
            return;
        }

        $parentId = $parent->id;

        // Remove old Security Due Diligence service
        $oldChild = DB::table('service_childrens')
            ->where('service_parent_id', $parentId)
            ->where('service_name', 'Security Due Diligence')
            ->first();

        if ($oldChild) {
            $oldTemplate = DB::table('service_form_templates')
                ->where('service_child_id', $oldChild->id)
                ->first();

            if ($oldTemplate) {
                DB::table('service_form_fields')
                    ->where('service_form_template_id', $oldTemplate->id)
                    ->delete();
                DB::table('service_form_templates')->where('id', $oldTemplate->id)->delete();
            }

            DB::table('service_childrens')->where('id', $oldChild->id)->delete();
            $this->command->info('Removed old: Security Due Diligence');
        }

        $services = [
            [
                'name' => 'Scoping – Verification / Due Diligence',
                'code' => 'security_scoping_verification',
                'fields' => $this->getScopingVerificationFields(),
            ],
            [
                'name' => 'Security Risk Assessment',
                'code' => 'security_risk_assessment',
                'fields' => $this->getSecurityRiskAssessmentFields(),
            ],
            [
                'name' => 'Mitigation Plan',
                'code' => 'security_mitigation_plan',
                'fields' => $this->getMitigationPlanFields(),
            ],
        ];

        foreach ($services as $svc) {
            $serviceChild = DB::table('service_childrens')
                ->where('service_parent_id', $parentId)
                ->where('service_name', $svc['name'])
                ->first();

            if (!$serviceChild) {
                $childId = DB::table('service_childrens')->insertGetId([
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

            $template = ServiceFormTemplate::firstOrCreate(
                ['service_child_id' => $childId],
                [
                    'form_name' => $svc['name'],
                    'description' => 'Form for ' . $svc['name'],
                    'is_active' => true,
                ]
            );

            ServiceFormField::where('service_form_template_id', $template->id)->delete();

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
            // a) Property / Request Identification
            [
                'field_name' => 'property_reference_number',
                'field_label' => 'Property Reference Number',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'acquisition_request_number',
                'field_label' => 'Acquisition Request Number',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 2,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'province',
                'field_label' => 'Province',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 3,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'district',
                'field_label' => 'District',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'gps_coordinates',
                'field_label' => 'GPS Coordinates',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'property_type',
                'field_label' => 'Property Type',
                'field_type' => 'checkbox',
                'options' => [
                    'land' => 'Land',
                    'building' => 'Building',
                    'substation_site' => 'Substation Site',
                    'wayleave' => 'Wayleave',
                ],
                'is_required' => false,
                'sort_order' => 6,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'intended_use',
                'field_label' => 'Intended Use',
                'field_type' => 'checkbox',
                'options' => [
                    'substation' => 'Substation',
                    'office' => 'Office',
                    'storage_facility' => 'Storage Facility',
                    'transmission_line' => 'Transmission Line',
                ],
                'is_required' => false,
                'sort_order' => 7,
                'section' => 'Property / Request Identification',
            ],
            [
                'field_name' => 'property_documents',
                'field_label' => 'Property Documents',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Property / Request Identification',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],

            // b) Occupancy & Activities
            [
                'field_name' => 'property_occupied',
                'field_label' => 'Property Occupied?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 9,
                'section' => 'Occupancy & Activities',
            ],
            [
                'field_name' => 'occupied_by',
                'field_label' => 'Occupied By',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 10,
                'section' => 'Occupancy & Activities',
            ],
            [
                'field_name' => 'activities_observed',
                'field_label' => 'Activities Observed',
                'field_type' => 'checkbox',
                'options' => [
                    'farming' => 'Farming',
                    'residential' => 'Residential',
                    'commercial' => 'Commercial',
                    'grazing' => 'Grazing',
                    'industrial' => 'Industrial',
                    'other' => 'Other',
                ],
                'is_required' => false,
                'sort_order' => 11,
                'section' => 'Occupancy & Activities',
            ],
            [
                'field_name' => 'occupancy_notes',
                'field_label' => 'Occupancy Notes',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 12,
                'section' => 'Occupancy & Activities',
            ],
            [
                'field_name' => 'occupancy_evidence',
                'field_label' => 'Occupancy Evidence',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 13,
                'section' => 'Occupancy & Activities',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],

            // c) Traditional Authority / Community
            [
                'field_name' => 'traditional_authority_involved',
                'field_label' => 'Traditional Authority Involved?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 14,
                'section' => 'Traditional Authority / Community',
            ],
            [
                'field_name' => 'chiefdom_name',
                'field_label' => 'Chiefdom Name',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 15,
                'section' => 'Traditional Authority / Community',
            ],
            [
                'field_name' => 'community_concerns',
                'field_label' => 'Community Concerns',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 16,
                'section' => 'Traditional Authority / Community',
            ],
            [
                'field_name' => 'community_documents',
                'field_label' => 'Community Documents',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 17,
                'section' => 'Traditional Authority / Community',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],

            // d) Access / Terrain
            [
                'field_name' => 'road_access',
                'field_label' => 'Road Access Available?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 18,
                'section' => 'Access / Terrain',
            ],
            [
                'field_name' => 'boundary_identifiable',
                'field_label' => 'Boundary Clearly Identifiable?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 19,
                'section' => 'Access / Terrain',
            ],
            [
                'field_name' => 'terrain_notes',
                'field_label' => 'Terrain Notes',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 20,
                'section' => 'Access / Terrain',
            ],
            [
                'field_name' => 'access_route_images',
                'field_label' => 'Access Route Images',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 21,
                'section' => 'Access / Terrain',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }

    private function getSecurityRiskAssessmentFields(): array
    {
        return [
            // a) Risk Identification
            [
                'field_name' => 'security_issues',
                'field_label' => 'Security Issues Identified',
                'field_type' => 'checkbox',
                'options' => [
                    'encroachment' => 'Encroachment',
                    'theft' => 'Theft',
                    'vandalism' => 'Vandalism',
                    'illegal_settlements' => 'Illegal Settlements',
                    'conflicts' => 'Conflicts',
                    'other' => 'Other',
                ],
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Risk Identification',
            ],
            [
                'field_name' => 'security_rating',
                'field_label' => 'Security Rating',
                'field_type' => 'checkbox',
                'options' => [
                    'low' => 'Low',
                    'moderate' => 'Moderate',
                    'high' => 'High',
                    'critical' => 'Critical',
                ],
                'is_required' => false,
                'sort_order' => 2,
                'section' => 'Risk Identification',
            ],
            [
                'field_name' => 'security_evidence',
                'field_label' => 'Security Evidence',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 3,
                'section' => 'Risk Identification',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],

            // b) Risk Rating
            [
                'field_name' => 'encroachment_risk',
                'field_label' => 'Encroachment Risk',
                'field_type' => 'checkbox',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Risk Rating',
            ],
            [
                'field_name' => 'operational_interference_risk',
                'field_label' => 'Operational Interference Risk',
                'field_type' => 'checkbox',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Risk Rating',
            ],
            [
                'field_name' => 'community_conflict_risk',
                'field_label' => 'Community Conflict Risk',
                'field_type' => 'checkbox',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
                'is_required' => false,
                'sort_order' => 6,
                'section' => 'Risk Rating',
            ],
            [
                'field_name' => 'vandalism_risk',
                'field_label' => 'Vandalism Risk',
                'field_type' => 'checkbox',
                'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'],
                'is_required' => false,
                'sort_order' => 7,
                'section' => 'Risk Rating',
            ],
            [
                'field_name' => 'risk_assessment_files',
                'field_label' => 'Risk Assessment Files',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Risk Rating',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],

            // c) Conflicts / Ownership
            [
                'field_name' => 'current_owner',
                'field_label' => 'Current Owner',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 9,
                'section' => 'Conflicts',
            ],
            [
                'field_name' => 'previous_owner',
                'field_label' => 'Previous Owner',
                'field_type' => 'text',
                'is_required' => false,
                'sort_order' => 10,
                'section' => 'Conflicts',
            ],
            [
                'field_name' => 'ownership_disputes',
                'field_label' => 'Ownership Disputes',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 11,
                'section' => 'Conflicts',
            ],
            [
                'field_name' => 'ownership_documents',
                'field_label' => 'Ownership Documents',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 12,
                'section' => 'Conflicts',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }

    private function getMitigationPlanFields(): array
    {
        return [
            // a) Protective Security Infrastructure Requirements
            [
                'field_name' => 'perimeter_fencing_required',
                'field_label' => 'Perimeter Fencing Required?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 1,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'fencing_types',
                'field_label' => 'Fencing Types',
                'field_type' => 'checkbox',
                'options' => [
                    'electric_fence' => 'Electric Fence',
                    'chain_link' => 'Chain Link',
                    'razor_wire' => 'Razor Wire',
                    'concrete_wall' => 'Concrete Wall',
                    'other' => 'Other',
                ],
                'is_required' => false,
                'sort_order' => 2,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'guard_post_required',
                'field_label' => 'Guard Post Required?',
                'field_type' => 'select',
                'options' => ['yes' => 'Yes', 'no' => 'No'],
                'is_required' => false,
                'sort_order' => 3,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'guard_post_types',
                'field_label' => 'Guard Post Types',
                'field_type' => 'checkbox',
                'options' => [
                    'temporary' => 'Temporary',
                    'permanent' => 'Permanent',
                    'mobile' => 'Mobile',
                ],
                'is_required' => false,
                'sort_order' => 4,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'infrastructure_stages',
                'field_label' => 'Infrastructure Stages',
                'field_type' => 'checkbox',
                'options' => [
                    'planning' => 'Planning',
                    'construction' => 'Construction',
                    'operational' => 'Operational',
                ],
                'is_required' => false,
                'sort_order' => 5,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'protective_measures',
                'field_label' => 'Protective Measures Required',
                'field_type' => 'checkbox',
                'options' => [
                    'cctv' => 'CCTV',
                    'security_lighting' => 'Security Lighting',
                    'alarm_systems' => 'Alarm Systems',
                    'armed_response' => 'Armed Response',
                    'patrols' => 'Patrols',
                    'access_control' => 'Access Control',
                    'static_guard' => 'Static Guard',
                    'other' => 'Other',
                ],
                'is_required' => false,
                'sort_order' => 6,
                'section' => 'Protective Security Infrastructure Requirements',
            ],
            [
                'field_name' => 'observations',
                'field_label' => 'Observations',
                'field_type' => 'textarea',
                'is_required' => false,
                'sort_order' => 7,
                'section' => 'Final Recommendation',
            ],
            [
                'field_name' => 'final_recommendation',
                'field_label' => 'Final Recommendation',
                'field_type' => 'checkbox',
                'options' => [
                    'approved' => 'Approved',
                    'approved_with_conditions' => 'Approved with Conditions',
                    'further_investigation' => 'Further Investigation Required',
                    'escalate_management' => 'Escalate to Management',
                    'not_recommended' => 'Not Recommended',
                ],
                'is_required' => false,
                'sort_order' => 8,
                'section' => 'Final Recommendation',
            ],
            [
                'field_name' => 'final_assessment_files',
                'field_label' => 'Final Assessment Files',
                'field_type' => 'file',
                'is_required' => false,
                'sort_order' => 9,
                'section' => 'Final Recommendation',
                'allow_comments' => true,
                'allow_multiple_attachments' => true,
            ],
        ];
    }
}
