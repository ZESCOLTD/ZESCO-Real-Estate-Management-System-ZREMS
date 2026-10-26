<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->string('site_building_suitability')->nullable();
            $table->string('structural_capacity')->nullable();
            $table->string('space_layout_suitability')->nullable();
            $table->string('site_access')->nullable();
            $table->string('electricity_supply')->nullable();
            $table->string('water_supply')->nullable();
            $table->string('drainage_sewerage')->nullable();
            $table->string('fire_safety_access')->nullable();
            $table->string('environmental_sheq_concerns')->nullable();
            $table->string('permits_statutory_requirements')->nullable();
            $table->text('technical_limitations_measures')->nullable();

            $table->json('options_considered')->nullable();
            $table->unsignedTinyInteger('preferred_option_number')->nullable();
            $table->text('preference_reason')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('project_feasibility_assessments', function (Blueprint $table) {
            $table->dropColumn([
                'site_building_suitability',
                'structural_capacity',
                'space_layout_suitability',
                'site_access',
                'electricity_supply',
                'water_supply',
                'drainage_sewerage',
                'fire_safety_access',
                'environmental_sheq_concerns',
                'permits_statutory_requirements',
                'technical_limitations_measures',
                'options_considered',
                'preferred_option_number',
                'preference_reason',
            ]);
        });
    }
};