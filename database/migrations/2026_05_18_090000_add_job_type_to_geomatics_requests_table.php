<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geomatics_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('geomatics_requests', 'job_type')) {
                $table->string('job_type')->nullable()->index()->after('services');
            }
        });
    }

    public function down(): void
    {
        Schema::table('geomatics_requests', function (Blueprint $table) {
            if (Schema::hasColumn('geomatics_requests', 'job_type')) {
                $table->dropColumn('job_type');
            }
        });
    }
};
