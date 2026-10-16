<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wayleave_asset_register', function (Blueprint $table) {
            $table->id();
            $table->string('line_name'); // LINE NAME / DESCRIPTION
            $table->string('voltage_level')->nullable();
            $table->string('line_length')->nullable(); // km or meters
            $table->string('line_width')->nullable();
            $table->string('hectarage')->nullable();
            $table->string('rate')->nullable();
            $table->string('amount')->nullable();
            $table->string('region_name')->nullable();
            $table->string('zone')->nullable();
            $table->string('district_name')->nullable();
            $table->string('proposed_service_provider')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('email_address')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('comments')->nullable();
            $table->string('directorate')->nullable();
            $table->string('schedule_year')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wayleave_asset_register');
    }
};
