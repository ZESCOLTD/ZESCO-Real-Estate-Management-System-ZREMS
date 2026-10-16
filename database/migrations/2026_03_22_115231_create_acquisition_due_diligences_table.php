<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('acquisition_due_diligences', function (Blueprint $table) {
            $table->id();

            // Link to acquisition request using its reference_no
            $table->integer('acquisition_request_id');
            $table->string('acquisition_request_reference_no');

            // General info
            $table->string('ref_no')->nullable(); // Due diligence internal ref no
            $table->date('request_date')->nullable();

            // Section A
            $table->text('team_creation_action')->nullable();

            // Section B
            $table->text('potential_properties')->nullable();
            $table->decimal('offer_price', 18, 2)->nullable();
            $table->string('findings_report_path')->nullable();
            $table->text('findings_report_comment')->nullable();

            // Section C - Screening / Property details
            $table->text('filter_viable_option')->nullable();
            $table->text('shortlisted_properties')->nullable();

            $table->string('property_no')->nullable();
            $table->string('property_no_file_path')->nullable();
            $table->text('property_no_comment')->nullable();

            $table->string('coordinates')->nullable();
            $table->string('coordinates_file_path')->nullable();
            $table->text('coordinates_comment')->nullable();

            $table->string('land_extent')->nullable();
            $table->string('land_extent_file_path')->nullable();
            $table->text('land_extent_comment')->nullable();

            // Location
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('township')->nullable();
            $table->string('chiefdom')->nullable();

            // Ownership
            $table->string('owner_name')->nullable();
            $table->enum('land_tenure', ['Leasehold', 'Customary'])->nullable();

            // Registration
            $table->string('registration_status')->nullable(); // Certificate of Title / Other
            $table->string('registration_file_path')->nullable();
            $table->text('registration_comment')->nullable();

            // Encumbrances
            $table->string('encumbrances_file_path')->nullable();
            $table->text('encumbrances_comment')->nullable();

            // Suitability
            $table->enum('suitability', [
                'Suitable',
                'Suitable W/Mitigation',
                'Not Suitable'
            ])->nullable();

            $table->text('mitigation_notes')->nullable();

            // Valuation stage
            $table->decimal('valuation_amount', 18, 2)->nullable();
            $table->string('valuation_report_path')->nullable();
            $table->text('valuation_report_comment')->nullable();

            // Purchase negotiations
            $table->decimal('final_offer', 18, 2)->nullable();
            $table->string('contract_execution_path')->nullable();
            $table->string('invoice_path')->nullable();
            $table->decimal('payment_amount', 18, 2)->nullable();

            $table->text('purchase_outcome')->nullable();

            // Optional signoff / workflow support
            $table->boolean('all_team_signed_off')->default(false);
            $table->timestamp('signoff_completed_at')->nullable();
            $table->string('status')->nullable();



            $table->timestamps();
            $table->softDeletes();

            $table->index('ref_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquisition_due_diligences');
    }
};
