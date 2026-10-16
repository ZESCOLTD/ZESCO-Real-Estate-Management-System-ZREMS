<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('management_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('request_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('applicant_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('directorate')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('reporting_manager')->nullable();

            $table->string('project_name')->nullable();
            $table->string('project_code')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('project_type')->nullable();
            $table->text('description')->nullable();

            $table->string('location')->nullable();
            $table->date('expected_date')->nullable();

            $table->string('purpose')->nullable();
            $table->string('acquisition_type')->nullable();
            $table->string('acquisition_type_other')->nullable();
            $table->string('ownership_status')->nullable();
            $table->string('ownership_status_other')->nullable();
            $table->decimal('extent', 14, 4)->nullable();
            $table->string('extent_unit')->nullable()->default('Ha');
            $table->text('coordinates')->nullable();

            $table->boolean('is_budget_approved')->default(false);
            $table->string('budget_line')->nullable();
            $table->decimal('estimated_cost', 18, 2)->nullable();
            $table->boolean('cet_approval')->default(false);
            $table->string('cet_ref')->nullable();
            $table->boolean('board_approval')->default(false);
            $table->string('board_resolution')->nullable();

            $table->string('status')->default('Draft');
            $table->string('workflow_stage')->nullable()->default('Initiator');
            $table->text('remarks')->nullable();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->index(['status']);
            $table->index(['workflow_stage']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('management_requests');
    }
};