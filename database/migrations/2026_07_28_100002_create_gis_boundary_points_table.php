<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_boundary_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gis_land_boundary_id')
                  ->constrained('gis_land_boundaries')
                  ->cascadeOnDelete();
            $table->unsignedSmallInteger('point_number');
            $table->unsignedSmallInteger('sequence');
            // Original survey coordinates
            $table->decimal('easting', 15, 4)->nullable()->comment('Original easting from survey');
            $table->decimal('northing', 15, 4)->nullable()->comment('Original northing from survey');
            // Converted WGS84 coordinates
            $table->decimal('latitude', 12, 8)->nullable()->comment('WGS84 latitude');
            $table->decimal('longitude', 12, 8)->nullable()->comment('WGS84 longitude');
            $table->timestamps();

            $table->index('gis_land_boundary_id');
            $table->index('sequence');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_boundary_points');
    }
};
