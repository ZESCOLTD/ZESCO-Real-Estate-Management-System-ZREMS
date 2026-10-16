<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisition_requests', function (Blueprint $table) {
            $table->id();

            // =========================
            // REQUEST METADATA
            // =========================
            $table->string('reference_no')->unique();
            $table->date('request_date')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // =========================
            // APPLICANT SNAPSHOT
            // =========================
            $table->string('applicant_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('directorate')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('reporting_manager')->nullable();

            // =========================
            // PROJECT DETAILS
            // =========================
            $table->string('project_name')->nullable();
            $table->string('project_code')->nullable();
            $table->string('business_unit')->nullable();
            $table->string('cost_center')->nullable();
            $table->string('project_type')->nullable();
            $table->text('description')->nullable();

            // =========================
            // LOCATION / TIMELINE
            // =========================
            $table->string('location')->nullable();
            $table->date('expected_date')->nullable();

            // =========================
            // LAND DETAILS
            // =========================
            $table->string('purpose')->nullable();
            $table->string('acquisition_type')->nullable(); // Purchase / Lease / Right Of Use / Other
            $table->string('acquisition_type_other')->nullable();
            $table->string('ownership_status')->nullable(); // Leasehold / Customary / Other
            $table->string('ownership_status_other')->nullable();
            $table->string('extent')->nullable();
            $table->string('extent_unit')->nullable()->default('Ha'); // Ha / Acres
            $table->text('coordinates')->nullable();

            // =========================
            // BUDGET & APPROVAL
            // =========================
            $table->boolean('is_budget_approved')->default(false);
            $table->string('budget_line')->nullable();
            $table->decimal('estimated_cost', 18, 2)->nullable();

            $table->boolean('cet_approval')->default(false);
            $table->string('cet_ref')->nullable();
            $table->boolean('board_approval')->default(false);
            $table->string('board_resolution')->nullable();

            // =========================
            // WORKFLOW / STATUS
            // =========================
            $table->string('status')->default('Draft');
            // Draft, Pending Approval, Reverted, Rejected, Approved, Cancelled

            $table->string('workflow_stage')->nullable()->default('Initiator');
            // Initiator, Line Manager, Procurement, Director, CEO, etc.

            $table->text('remarks')->nullable();

            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();

            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('rejected_at')->nullable();

            $table->foreignId('reverted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reverted_at')->nullable();

            // =========================
            // SYSTEM
            // =========================
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status']);
            $table->index(['workflow_stage']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisition_requests');
    }
};
