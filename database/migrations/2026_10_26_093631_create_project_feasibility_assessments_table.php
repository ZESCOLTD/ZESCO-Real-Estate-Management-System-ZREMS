<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_feasibility_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('real_estate_project_id')
                ->constrained('real_estate_projects')
                ->cascadeOnDelete();

            $table->string('project_title')->nullable();
            $table->string('proposal_reference')->nullable();
            $table->string('property_site_name')->nullable();
            $table->string('building_structure_name')->nullable();
            $table->string('town_location')->nullable();

            $table->date('assessment_date')->nullable();
            $table->string('lead_assessing_department')->nullable();
            $table->string('prepared_by')->nullable();
            $table->text('other_departments_consulted')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_feasibility_assessments');
    }
};