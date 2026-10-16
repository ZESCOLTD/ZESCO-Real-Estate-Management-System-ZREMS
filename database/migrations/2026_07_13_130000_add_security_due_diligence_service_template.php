<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('service_parents') || !Schema::hasTable('service_childrens') || !Schema::hasTable('service_form_templates') || !Schema::hasTable('service_form_fields')) {
            return;
        }

        $now = now();

        $parent = DB::table('service_parents')
            ->whereRaw('LOWER(TRIM(department_code)) = ?', ['security_services'])
            ->orderBy('id')
            ->first();

        if (!$parent) {
            $parentId = DB::table('service_parents')->insertGetId([
                'department_name' => 'Security Department',
                'department_code' => 'security_services',
                'service_name' => 'Security Due Diligence Services',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $parentId = (int) $parent->id;

            if (strtolower(trim((string) ($parent->department_name ?? ''))) === 'secuty department') {
                DB::table('service_parents')
                    ->where('id', $parentId)
                    ->update([
                        'department_name' => 'Security Department',
                        'updated_at' => $now,
                    ]);
            }
        }

        $serviceName = 'Security Due Diligence';

        $child = DB::table('service_childrens')
            ->where('service_parent_id', $parentId)
            ->whereRaw('LOWER(TRIM(service_name)) = ?', [strtolower($serviceName)])
            ->first();

        if (!$child) {
            $childId = DB::table('service_childrens')->insertGetId([
                'service_parent_id' => $parentId,
                'service_name' => $serviceName,
                'service_code' => 'security_due_diligence',
                'model' => 'AcquisitionDueDiligence',
                'type' => 'new_acquisition',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $childId = (int) $child->id;

            DB::table('service_childrens')
                ->where('id', $childId)
                ->update([
                    'service_code' => 'security_due_diligence',
                    'model' => 'AcquisitionDueDiligence',
                    'type' => 'new_acquisition',
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);
        }

        $template = DB::table('service_form_templates')
            ->where('service_child_id', $childId)
            ->first();

        if (!$template) {
            $templateId = DB::table('service_form_templates')->insertGetId([
                'service_child_id' => $childId,
                'form_name' => $serviceName,
                'description' => 'Security due diligence form for scoping and risk assessment.',
                'sections' => json_encode([
                    'Property Information',
                    'Occupancy & Land Status',
                    'Security Assessment',
                    'Protective Security Infrastructure Requirements',
                    'Community & Traditional Leadership',
                    'Access & Operational Feasibility',
                    'Future Risk Assessment',
                    'Ownership Verification',
                    'Security Officer Observations',
                ]),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            $templateId = (int) $template->id;

            DB::table('service_form_templates')
                ->where('id', $templateId)
                ->update([
                    'form_name' => $serviceName,
                    'description' => 'Security due diligence form for scoping and risk assessment.',
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);
        }

        DB::table('service_form_fields')
            ->where('service_form_template_id', $templateId)
            ->delete();

        $fields = [
            // Property Information
            ['field_name' => 'security_property_reference_number', 'field_label' => 'Property Reference Number', 'field_type' => 'text', 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_acquisition_request_number', 'field_label' => 'Acquisition Request Number', 'field_type' => 'text', 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_province', 'field_label' => 'Province', 'field_type' => 'text', 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_district', 'field_label' => 'District', 'field_type' => 'text', 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_gps_coordinates', 'field_label' => 'GPS Coordinates', 'field_type' => 'text', 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_property_type', 'field_label' => 'Property Type', 'field_type' => 'select', 'options' => ['land' => 'Land', 'building' => 'Building', 'substation_site' => 'Substation Site', 'wayleave' => 'Wayleave'], 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_intended_use', 'field_label' => 'Intended ZESCO Use', 'field_type' => 'select', 'options' => ['substation' => 'Substation', 'office' => 'Office', 'storage_facility' => 'Storage Facility', 'transmission_line' => 'Transmission Line'], 'section' => 'Property Information', 'is_required' => false],
            ['field_name' => 'security_property_documents', 'field_label' => 'Upload Property Documents', 'field_type' => 'file', 'section' => 'Property Information', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Occupancy
            ['field_name' => 'security_property_occupied', 'field_label' => 'Is Property Occupied?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Occupancy & Land Status', 'is_required' => false],
            ['field_name' => 'security_occupied_by', 'field_label' => 'Occupied By', 'field_type' => 'text', 'section' => 'Occupancy & Land Status', 'is_required' => false],
            ['field_name' => 'security_activities', 'field_label' => 'Visible Signs / Activities', 'field_type' => 'checkbox', 'options' => ['encroachment' => 'Encroachment', 'illegal_settlement' => 'Illegal Settlement', 'mining_activity' => 'Mining Activity', 'unauthorized_structures' => 'Unauthorized Structures', 'illegal_business_activity' => 'Illegal Business Activity', 'community_occupation' => 'Community Occupation'], 'section' => 'Occupancy & Land Status', 'is_required' => false],
            ['field_name' => 'security_occupancy_notes', 'field_label' => 'Additional Notes', 'field_type' => 'textarea', 'section' => 'Occupancy & Land Status', 'is_required' => false],
            ['field_name' => 'security_occupancy_evidence', 'field_label' => 'Upload Occupancy Evidence', 'field_type' => 'file', 'section' => 'Occupancy & Land Status', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Security Assessment
            ['field_name' => 'security_issues', 'field_label' => 'Area Known For', 'field_type' => 'checkbox', 'options' => ['vandalism' => 'Vandalism', 'theft' => 'Theft', 'illegal_mining' => 'Illegal Mining', 'political_tension' => 'Political Tension', 'community_disputes' => 'Community Disputes', 'criminal_activity' => 'Criminal Activity'], 'section' => 'Security Assessment', 'is_required' => false],
            ['field_name' => 'security_rating', 'field_label' => 'General Security Rating', 'field_type' => 'select', 'options' => ['low' => 'Low Risk', 'moderate' => 'Moderate Risk', 'high' => 'High Risk', 'critical' => 'Critical Risk'], 'section' => 'Security Assessment', 'is_required' => false],
            ['field_name' => 'security_evidence', 'field_label' => 'Upload Security Evidence', 'field_type' => 'file', 'section' => 'Security Assessment', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Protective Infrastructure
            ['field_name' => 'security_perimeter_fencing_required', 'field_label' => 'Perimeter Security Fencing Required?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_fencing_types', 'field_label' => 'Recommended Fencing Type', 'field_type' => 'checkbox', 'options' => ['chain_link_fence' => 'Chain Link Fence', 'electric_fence' => 'Electric Fence', 'concrete_wall_fence' => 'Concrete Wall Fence', 'razor_wire_reinforcement' => 'Razor Wire Reinforcement', 'other' => 'Other (Specify)'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_fencing_other', 'field_label' => 'Other Fencing Type', 'field_type' => 'text', 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_guard_post_required', 'field_label' => 'Security Guard Post Required?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_guard_post_types', 'field_label' => 'Security Guard Post Type', 'field_type' => 'checkbox', 'options' => ['permanent_guard_post' => 'Permanent Guard Post', 'temporary_construction_guard_shelter' => 'Temporary Construction Guard Shelter', 'security_control_room' => 'Security Control Room', 'vehicle_access_checkpoint' => 'Vehicle Access Checkpoint', 'visitor_screening_point' => 'Visitor Screening Point'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_infrastructure_stages', 'field_label' => 'Infrastructure Implementation Stage', 'field_type' => 'checkbox', 'options' => ['before_construction' => 'Before construction works commence', 'during_infrastructure_development' => 'During infrastructure development', 'after_project_completion' => 'After project completion', 'immediate_deployment_required' => 'Immediate deployment required'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],
            ['field_name' => 'security_protective_measures', 'field_label' => 'Additional Protective Measures', 'field_type' => 'checkbox', 'options' => ['cctv_surveillance' => 'CCTV Surveillance', 'flood_security_lighting' => 'Flood/Security Lighting', 'access_control_gates' => 'Access Control Gates', 'intruder_alarm_systems' => 'Intruder Alarm Systems', 'anti_vandalism_reinforcement' => 'Anti-vandalism reinforcement', 'security_patrol_route_planning' => 'Security patrol route planning', 'security_signage_warning_notices' => 'Security signage and warning notices', 'drone_surveillance_suitability' => 'Drone surveillance suitability', 'dedicated_security_personnel_deployment' => 'Dedicated security personnel deployment'], 'section' => 'Protective Security Infrastructure Requirements', 'is_required' => false],

            // Community
            ['field_name' => 'security_traditional_authority', 'field_label' => 'Traditional Authority?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Community & Traditional Leadership', 'is_required' => false],
            ['field_name' => 'security_chiefdom_name', 'field_label' => 'Name of Chiefdom', 'field_type' => 'text', 'section' => 'Community & Traditional Leadership', 'is_required' => false],
            ['field_name' => 'security_community_concerns', 'field_label' => 'Known Disputes or Community Concerns', 'field_type' => 'textarea', 'section' => 'Community & Traditional Leadership', 'is_required' => false],
            ['field_name' => 'security_community_documents', 'field_label' => 'Upload Community Documents', 'field_type' => 'file', 'section' => 'Community & Traditional Leadership', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Access
            ['field_name' => 'security_road_access', 'field_label' => 'Road Access Available?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Access & Operational Feasibility', 'is_required' => false],
            ['field_name' => 'security_boundary_identifiable', 'field_label' => 'Boundary Clearly Identifiable?', 'field_type' => 'select', 'options' => ['yes' => 'Yes', 'no' => 'No'], 'section' => 'Access & Operational Feasibility', 'is_required' => false],
            ['field_name' => 'security_terrain_notes', 'field_label' => 'Access & Terrain Notes', 'field_type' => 'textarea', 'section' => 'Access & Operational Feasibility', 'is_required' => false],
            ['field_name' => 'security_access_route_images', 'field_label' => 'Upload Access Route Images', 'field_type' => 'file', 'section' => 'Access & Operational Feasibility', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Future Risks
            ['field_name' => 'security_encroachment_risk', 'field_label' => 'Future Encroachment Risk', 'field_type' => 'select', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'], 'section' => 'Future Risk Assessment', 'is_required' => false],
            ['field_name' => 'security_operational_interference', 'field_label' => 'Operational Interference', 'field_type' => 'select', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'], 'section' => 'Future Risk Assessment', 'is_required' => false],
            ['field_name' => 'security_community_conflict_risk', 'field_label' => 'Community Conflict Risk', 'field_type' => 'select', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'], 'section' => 'Future Risk Assessment', 'is_required' => false],
            ['field_name' => 'security_vandalism_risk', 'field_label' => 'Infrastructure Vandalism Risk', 'field_type' => 'select', 'options' => ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'], 'section' => 'Future Risk Assessment', 'is_required' => false],
            ['field_name' => 'security_risk_assessment_files', 'field_label' => 'Upload Risk Assessment Files', 'field_type' => 'file', 'section' => 'Future Risk Assessment', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Ownership
            ['field_name' => 'security_current_owner', 'field_label' => 'Current Owner', 'field_type' => 'text', 'section' => 'Ownership Verification', 'is_required' => false],
            ['field_name' => 'security_previous_owner', 'field_label' => 'Previous Owner', 'field_type' => 'text', 'section' => 'Ownership Verification', 'is_required' => false],
            ['field_name' => 'security_ownership_disputes', 'field_label' => 'Ownership Disputes', 'field_type' => 'textarea', 'section' => 'Ownership Verification', 'is_required' => false],
            ['field_name' => 'security_ownership_documents', 'field_label' => 'Upload Ownership Verification Documents', 'field_type' => 'file', 'section' => 'Ownership Verification', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],

            // Final
            ['field_name' => 'security_observations', 'field_label' => 'Observations & Recommendations', 'field_type' => 'textarea', 'section' => 'Security Officer Observations', 'is_required' => false],
            ['field_name' => 'security_final_recommendation', 'field_label' => 'Final Recommendation', 'field_type' => 'select', 'options' => ['approved' => 'Approved - No Major Risk', 'approved_conditions' => 'Approved with Conditions', 'further_investigation' => 'Further Investigation Required', 'escalate_management' => 'Escalate to Management', 'not_recommended' => 'Not Recommended'], 'section' => 'Security Officer Observations', 'is_required' => false],
            ['field_name' => 'security_final_assessment_files', 'field_label' => 'Upload Final Assessment Files', 'field_type' => 'file', 'section' => 'Security Officer Observations', 'is_required' => false, 'allow_comments' => true, 'allow_multiple_attachments' => true],
        ];

        $sortOrder = 1;
        $rows = [];

        foreach ($fields as $field) {
            $rows[] = [
                'service_form_template_id' => $templateId,
                'field_name' => $field['field_name'],
                'field_label' => $field['field_label'],
                'field_type' => $field['field_type'],
                'options' => isset($field['options']) ? json_encode($field['options']) : null,
                'is_required' => (bool) ($field['is_required'] ?? false),
                'sort_order' => $sortOrder++,
                'section' => $field['section'] ?? null,
                'allow_comments' => (bool) ($field['allow_comments'] ?? false),
                'allow_attachment' => $field['field_type'] === 'file',
                'allow_multiple_attachments' => (bool) ($field['allow_multiple_attachments'] ?? false),
                'attachment_label' => $field['field_type'] === 'file' ? 'Attach supporting evidence' : null,
                'validation_rules' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('service_form_fields')->insert($rows);
    }

    public function down(): void
    {
        if (!Schema::hasTable('service_form_templates') || !Schema::hasTable('service_childrens') || !Schema::hasTable('service_form_fields')) {
            return;
        }

        $child = DB::table('service_childrens')
            ->whereRaw('LOWER(TRIM(service_name)) = ?', [strtolower('Security Due Diligence')])
            ->orderByDesc('id')
            ->first();

        if (!$child) {
            return;
        }

        $template = DB::table('service_form_templates')
            ->where('service_child_id', $child->id)
            ->first();

        if ($template) {
            DB::table('service_form_fields')
                ->where('service_form_template_id', $template->id)
                ->delete();

            DB::table('service_form_templates')
                ->where('id', $template->id)
                ->delete();
        }

        DB::table('service_childrens')
            ->where('id', $child->id)
            ->delete();
    }
};
