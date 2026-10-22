<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_registers', function (Blueprint $table) {
            $table->id();
            $table->string('unit_key', 64)->unique();
            $table->string('serial_no')->nullable();
            $table->string('station_location')->index();
            $table->string('category')->index();
            $table->string('current_tenant')->nullable()->index();
            $table->string('man_no')->nullable()->index();
            $table->string('employer')->nullable()->index();
            $table->string('location')->nullable()->index();
            $table->string('physical_address')->nullable();
            $table->decimal('area_m2', 14, 2)->nullable();
            $table->integer('bedrooms')->nullable();
            $table->decimal('open_closed_verandah_m2', 14, 2)->nullable();
            $table->decimal('garage_m2', 14, 2)->nullable();
            $table->decimal('servants_quarter_m2', 14, 2)->nullable();
            $table->string('condition')->nullable()->index();
            $table->decimal('current_rental_charge', 14, 2)->nullable();
            $table->decimal('market_rental_charge_gvd', 14, 2)->nullable();
            $table->decimal('proposed_rental_charge_50_percent', 14, 2)->nullable();
            $table->decimal('proposed_rentals_after_verification', 14, 2)->nullable();
            $table->decimal('proposed_percentage_increment', 14, 2)->nullable();
            $table->decimal('water_charges', 14, 2)->nullable();
            $table->decimal('sewer_charges', 14, 2)->nullable();
            $table->decimal('proposed_total_amount', 14, 2)->nullable();
            $table->longText('source_row')->nullable();
            $table->unsignedBigInteger('last_import_job_id')->nullable()->index();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_registers');
    }
};