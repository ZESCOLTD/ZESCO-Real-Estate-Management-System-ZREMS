<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fms_asset_register', function (Blueprint $table) {
            $table->id();

            $table->string('asset_number')->nullable()->unique();
            $table->string('lams_asset_number')->nullable()->unique();
            $table->string('business_unit')->nullable()->index();
            $table->string('cost_centre')->nullable()->index();

            $table->string('tag_number')->nullable()->index();
            $table->string('serial_number')->nullable();

            $table->string('asset_natural_account')->nullable();
            $table->string('deprn_reserve_account')->nullable();
            $table->string('deprn_charge_account')->nullable();

            $table->text('description')->nullable();

            $table->string('link_id')->nullable()->index();

            $table->string('major_category')->nullable()->index();
            $table->string('sub_major_category')->nullable();
            $table->string('minor_category')->nullable();
            $table->string('sub_minor_category')->nullable();

            $table->integer('units')->nullable();

            $table->integer('life_in_months')->nullable();

            $table->string('date_placed_in_service')->nullable();

            $table->string('town')->nullable()->index();
            $table->string('location_2')->nullable();
            $table->string('dept')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fms_asset_register');
    }
};
