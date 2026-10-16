<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acquisition_due_diligence_properties', function (Blueprint $table) {
            $table->id();
            $table->integer('acquisition_due_diligence_id');

            $table->string('property_label')->nullable(); // e.g. Property 1 / Plot A

            // SECTION C
            $table->string('parcel_plot')->nullable();
            $table->string('coordinates')->nullable();
            $table->decimal('extent', 15, 2)->nullable();
            $table->string('extent_unit')->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('chiefdom')->nullable();

            // SECTION D
            $table->string('owner_names')->nullable();
            $table->string('tenure_type')->nullable();
            $table->string('title_type')->nullable();
            $table->string('title_type_other')->nullable();
            $table->string('encumbrances')->nullable();
            $table->string('disputes')->nullable();
            $table->text('disputes_notes')->nullable();

            // SECTION E
            $table->string('planning_status')->nullable();
            $table->string('zoning')->nullable();
            $table->json('servitudes')->nullable();
            $table->string('environmental_category')->nullable();
            $table->string('settlement_impact')->nullable();

            // SECTION F
            $table->string('recommendation')->nullable();
            $table->text('mitigation_notes')->nullable();
            $table->string('officer')->nullable();
            $table->date('officer_date')->nullable();

            // Draft save tracking
            $table->timestamp('last_saved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acquisition_due_diligence_properties');
    }
};
