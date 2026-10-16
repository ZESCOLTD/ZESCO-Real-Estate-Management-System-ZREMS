<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_land_boundaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gis_land_id')->constrained('gis_lands')->cascadeOnDelete();
            $table->enum('projection', [
                'arc1950_utm35s',
                'arc1950_utm36s',
                'wgs84_utm35s',
                'wgs84_utm36s',
                'wgs84',
            ])->default('arc1950_utm35s');
            $table->string('datum', 50)->nullable();
            $table->string('zone', 10)->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('gis_land_id');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_land_boundaries');
    }
};
