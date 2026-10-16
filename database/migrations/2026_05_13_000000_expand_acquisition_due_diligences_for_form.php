<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acquisition_due_diligences')) {
            return;
        }

        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            // Columns expected by the Due Diligence Livewire form/model but missing in some DBs.

            if (!Schema::hasColumn('acquisition_due_diligences', 'team_creation_action')) {
                $table->text('team_creation_action')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'potential_properties')) {
                $table->text('potential_properties')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'offer_price')) {
                $table->decimal('offer_price', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'findings_report_path')) {
                $table->string('findings_report_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'findings_report_comment')) {
                $table->text('findings_report_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'filter_viable_option')) {
                $table->text('filter_viable_option')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'shortlisted_properties')) {
                $table->text('shortlisted_properties')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'property_no')) {
                $table->string('property_no')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'property_no_file_path')) {
                $table->string('property_no_file_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'property_no_comment')) {
                $table->text('property_no_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'coordinates_file_path')) {
                $table->string('coordinates_file_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'coordinates_comment')) {
                $table->text('coordinates_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'land_extent')) {
                $table->string('land_extent')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'land_extent_file_path')) {
                $table->string('land_extent_file_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'land_extent_comment')) {
                $table->text('land_extent_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'township')) {
                $table->string('township')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'owner_name')) {
                $table->string('owner_name')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'land_tenure')) {
                $table->string('land_tenure')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'registration_status')) {
                $table->string('registration_status')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'registration_file_path')) {
                $table->string('registration_file_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'registration_comment')) {
                $table->text('registration_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'encumbrances_file_path')) {
                $table->string('encumbrances_file_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'encumbrances_comment')) {
                $table->text('encumbrances_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'suitability')) {
                $table->string('suitability')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'valuation_amount')) {
                $table->decimal('valuation_amount', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'valuation_report_path')) {
                $table->string('valuation_report_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'valuation_report_comment')) {
                $table->text('valuation_report_comment')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'final_offer')) {
                $table->decimal('final_offer', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'contract_execution_path')) {
                $table->string('contract_execution_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'invoice_path')) {
                $table->string('invoice_path')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'payment_amount')) {
                $table->decimal('payment_amount', 18, 2)->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'purchase_outcome')) {
                $table->string('purchase_outcome')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'all_team_signed_off')) {
                $table->boolean('all_team_signed_off')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'signoff_completed_at')) {
                $table->timestamp('signoff_completed_at')->nullable();
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'status')) {
                $table->string('status')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Non-destructive: keeping added columns.
    }
};
