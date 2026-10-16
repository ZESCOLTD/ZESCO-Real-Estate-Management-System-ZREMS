<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gis_survey_imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gis_land_id')->constrained('gis_lands')->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_filename')->nullable();
            $table->enum('projection', [
                'arc1950_utm35s',
                'arc1950_utm36s',
                'wgs84_utm35s',
                'wgs84_utm36s',
                'wgs84',
            ])->nullable();
            $table->string('zone', 10)->nullable();
            $table->unsignedInteger('point_count')->default(0);
            $table->enum('status', ['pending', 'processed', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->index('gis_land_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gis_survey_imports');
    }
};
