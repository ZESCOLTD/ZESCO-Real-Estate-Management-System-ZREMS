<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('project_progress_reports')) {
            Schema::create('project_progress_reports', function (Blueprint $table) {
                $table->id();
                $table->foreignId('real_estate_project_id')->constrained('real_estate_projects')->cascadeOnDelete();
                $table->date('period_from')->nullable();
                $table->date('period_to')->nullable();
                $table->date('report_date')->nullable();
                $table->string('current_status')->nullable();
                $table->unsignedTinyInteger('overall_progress')->nullable();
                $table->json('form_data');
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (! Schema::hasIndex('project_progress_reports', 'project_progress_project_date_idx')) {
            Schema::table('project_progress_reports', function (Blueprint $table) {
                $table->index(['real_estate_project_id', 'report_date'], 'project_progress_project_date_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('project_progress_reports');
    }
};
