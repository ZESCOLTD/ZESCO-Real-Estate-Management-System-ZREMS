<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            // =============================
            // SECURITY SECTION (Due Diligence)
            // =============================

            // Property information
            $table->string('security_property_reference_number')->nullable();
            $table->string('security_acquisition_request_number')->nullable();
            $table->string('security_province')->nullable();
            $table->string('security_district')->nullable();
            $table->string('security_gps_coordinates')->nullable();
            $table->string('security_property_type')->nullable();
            $table->string('security_intended_use')->nullable();
            $table->json('security_property_documents')->nullable();

            // Occupancy & land status
            $table->string('security_property_occupied')->nullable();
            $table->string('security_occupied_by')->nullable();
            $table->json('security_activities')->nullable();
            $table->text('security_occupancy_notes')->nullable();
            $table->json('security_occupancy_evidence')->nullable();

            // Security assessment
            $table->json('security_issues')->nullable();
            $table->string('security_rating')->nullable();
            $table->json('security_evidence')->nullable();

            // Community & traditional leadership
            $table->string('security_traditional_authority')->nullable();
            $table->string('security_chiefdom_name')->nullable();
            $table->text('security_community_concerns')->nullable();
            $table->json('security_community_documents')->nullable();

            // Access & operational feasibility
            $table->string('security_road_access')->nullable();
            $table->string('security_boundary_identifiable')->nullable();
            $table->text('security_terrain_notes')->nullable();
            $table->json('security_access_route_images')->nullable();

            // Future risk assessment
            $table->string('security_encroachment_risk')->nullable();
            $table->string('security_operational_interference')->nullable();
            $table->string('security_community_conflict_risk')->nullable();
            $table->string('security_vandalism_risk')->nullable();
            $table->json('security_risk_assessment_files')->nullable();

            // Ownership verification
            $table->string('security_current_owner')->nullable();
            $table->string('security_previous_owner')->nullable();
            $table->text('security_ownership_disputes')->nullable();
            $table->json('security_ownership_documents')->nullable();

            // Security officer observations
            $table->text('security_observations')->nullable();
            $table->string('security_final_recommendation')->nullable();
            $table->json('security_final_assessment_files')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            $table->dropColumn([
                'security_property_reference_number',
                'security_acquisition_request_number',
                'security_province',
                'security_district',
                'security_gps_coordinates',
                'security_property_type',
                'security_intended_use',
                'security_property_documents',

                'security_property_occupied',
                'security_occupied_by',
                'security_activities',
                'security_occupancy_notes',
                'security_occupancy_evidence',

                'security_issues',
                'security_rating',
                'security_evidence',

                'security_traditional_authority',
                'security_chiefdom_name',
                'security_community_concerns',
                'security_community_documents',

                'security_road_access',
                'security_boundary_identifiable',
                'security_terrain_notes',
                'security_access_route_images',

                'security_encroachment_risk',
                'security_operational_interference',
                'security_community_conflict_risk',
                'security_vandalism_risk',
                'security_risk_assessment_files',

                'security_current_owner',
                'security_previous_owner',
                'security_ownership_disputes',
                'security_ownership_documents',

                'security_observations',
                'security_final_recommendation',
                'security_final_assessment_files',
            ]);
        });
    }
};
