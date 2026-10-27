<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('real_estate_project_id')->unique()->constrained('real_estate_projects')->cascadeOnDelete();
            $table->json('form_data');
            $table->unsignedSmallInteger('risk_count')->default(0);
            $table->string('overall_position')->nullable();
            $table->date('next_review_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_risk_assessments');
    }
};
