<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('land_parcels', function (Blueprint $table) {
            $table->id();
            $table->string('parcel_no')->unique()->nullable();
            $table->string('fms_asset_number')->unique()->nullable();
            $table->string('plot_no')->nullable();
            $table->string('cadastral_id')->nullable()->index();
            $table->string('title_number')->nullable()->index();
            $table->string('survey_number')->nullable();
            $table->string('tenure_type'); // state, customary, private, leasehold
            $table->string('title_type')->nullable(); // CoT, offer, none, other
            $table->string('ownership_status')->nullable(); // state, customary, private
            $table->decimal('area_hectares', 14, 4)->nullable();
            $table->string('province')->nullable();
            $table->string('district')->nullable();
            $table->string('chiefdom')->nullable();
            $table->string('village')->nullable();
            $table->text('coordinates')->nullable();
            $table->string('zoning')->nullable();
            $table->string('planning_status')->nullable(); // planned/unplanned
            $table->string('permitted_use')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_expiry_date')->nullable();
            $table->integer('lease_term_years')->nullable();
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('land_parcels');
    }
};
