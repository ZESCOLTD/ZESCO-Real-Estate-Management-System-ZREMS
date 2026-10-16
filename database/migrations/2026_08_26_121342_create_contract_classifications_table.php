<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_classifications', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Contract Request
            |--------------------------------------------------------------------------
            */

            $table->foreignId('contract_request_id')
                ->unique()
                ->constrained('contract_requests')
                ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | Classification Inputs
            |--------------------------------------------------------------------------
            */

            $table->string('contract_type')->nullable();

            $table->decimal('contract_value', 18, 2)->nullable();

            $table->string('risk_rating')->nullable();

            $table->string('procurement_category')->nullable();

            $table->boolean('is_strategic')
                ->default(false);

            $table->boolean('is_international')
                ->default(false);

            $table->string('counterparty_type')->nullable();

            $table->boolean('is_land_property_related')
                ->default(false);

            $table->boolean('is_financing_related')
                ->default(false);

            $table->boolean('is_long_term_commitment')
                ->default(false);

            $table->boolean('has_regulatory_implications')
                ->default(false);

            $table->boolean('attorney_general_review_required')
                ->default(false);


            /*
            |--------------------------------------------------------------------------
            | System-Determined Routing
            |--------------------------------------------------------------------------
            |
            | These fields represent the output of the classification process.
            |
            */

            $table->string('legal_review_level')->nullable();

            $table->string('approval_authority')->nullable();

            $table->json('required_reviewers')->nullable();

            $table->json('required_checks')->nullable();

            $table->json('execution_requirements')->nullable();

            $table->json('mandatory_documents')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Classification Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [
                'draft',
                'classified',
                'reclassified',
                'approved',
            ])->default('draft');


            /*
            |--------------------------------------------------------------------------
            | Classification Audit
            |--------------------------------------------------------------------------
            */

            $table->foreignId('classified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('classified_at')->nullable();

            $table->text('classification_notes')->nullable();


            $table->timestamps();

            $table->index('status');
            $table->index('risk_rating');
            $table->index('procurement_category');
            $table->index('attorney_general_review_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_classifications');
    }
};
