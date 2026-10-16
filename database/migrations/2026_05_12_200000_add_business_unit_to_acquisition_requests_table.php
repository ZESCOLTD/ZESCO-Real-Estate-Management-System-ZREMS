<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('acquisition_requests')) {
            return;
        }

        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_requests', 'business_unit')) {
                return;
            }

            $table->string('business_unit')->nullable()->after('project_code');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('acquisition_requests')) {
            return;
        }

        Schema::table('acquisition_requests', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_requests', 'business_unit')) {
                $table->dropColumn('business_unit');
            }
        });
    }
};
