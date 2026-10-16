<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gis_lands', function (Blueprint $table) {
            if (!Schema::hasColumn('gis_lands', 'service_request_service_id')) {
                $table->foreignId('service_request_service_id')
                    ->nullable()
                    ->unique()
                    ->after('created_by')
                    ->constrained('service_request_services')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('gis_lands', function (Blueprint $table) {
            if (Schema::hasColumn('gis_lands', 'service_request_service_id')) {
                $table->dropConstrainedForeignId('service_request_service_id');
            }
        });
    }
};
