<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_requests', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Request Details
            |--------------------------------------------------------------------------
            */
            $table->string('request_no')->unique();

            $table->unsignedBigInteger('requesting_department_id')->nullable();
            $table->unsignedBigInteger('requesting_officer_id')->nullable();

            $table->dateTime('date_requested')->nullable();

            $table->string('contract_type');
            $table->string('proposed_contract_title');
            $table->string('land_asset_number')->nullable();
            $table->string('project_programme')->nullable();
            $table->string('business_unit')->nullable();

            $table->enum('priority', [
                'normal',
                'urgent',
                'critical',
            ])->default('normal');


            /*
            |--------------------------------------------------------------------------
            | Business Justification
            |--------------------------------------------------------------------------
            */
            $table->text('purpose')->nullable();
            $table->text('business_need')->nullable();
            $table->text('expected_outcome')->nullable();
            $table->text('strategic_objective')->nullable();
            $table->text('consequences_if_not_entered')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Proposed Counterparty
            |--------------------------------------------------------------------------
            */
            $table->string('counterparty_legal_name')->nullable();
            $table->string('counterparty_registration_number')->nullable();
            $table->text('counterparty_address')->nullable();
            $table->string('counterparty_contact_person')->nullable();
            $table->string('counterparty_email')->nullable();
            $table->string('counterparty_telephone')->nullable();
            $table->string('counterparty_supplier_no')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Commercial Information
            |--------------------------------------------------------------------------
            */
            $table->decimal('estimated_contract_value', 18, 2)->nullable();
            $table->string('money_currency', 10)->nullable();
            $table->string('contract_duration', 10)->nullable();
            $table->string('funding_source')->nullable();
            $table->string('budget_code')->nullable();
            $table->string('cost_centre')->nullable();
            $table->string('procurement_reference')->nullable();
            $table->text('proposed_payment_structure')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Preliminary Risk
            |--------------------------------------------------------------------------
            |
            | Stored as JSON because the document identifies multiple risk
            | categories rather than a single overall risk field.
            |
            */
            $table->json('preliminary_risk')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */
            $table->enum('status', [
                'draft',
                'submitted',
                'supervisor_review',
                'returned',
                'classification',
                'legal_review',
                'procurement_review',
                'contract_drafting',
                'approval',
                'approved',
                'rejected',
                'executed',
                'active',
                'expired',
                'terminated',
                'closed'
            ])->default('draft');

            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->dateTime('submitted_at')->nullable();

            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();

            $table->text('review_comments')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Timestamps
            |--------------------------------------------------------------------------
            */
            $table->timestamps();
            $table->softDeletes();


            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index('status');
            $table->index('contract_type');
            $table->index('priority');
            $table->index('requesting_department_id');
            $table->index('requesting_officer_id');
            $table->index('date_requested');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_requests');
    }
};
