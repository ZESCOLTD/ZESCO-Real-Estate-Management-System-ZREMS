<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('acquisition_due_diligences')) {
            return;
        }

        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            if (!Schema::hasColumn('acquisition_due_diligences', 'acquisition_request_reference_no')) {
                $table->string('acquisition_request_reference_no')->nullable()->after('acquisition_request_id');
            }

            if (!Schema::hasColumn('acquisition_due_diligences', 'request_date')) {
                $table->date('request_date')->nullable()->after('ref_no');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('acquisition_due_diligences')) {
            return;
        }

        Schema::table('acquisition_due_diligences', function (Blueprint $table) {
            if (Schema::hasColumn('acquisition_due_diligences', 'request_date')) {
                $table->dropColumn('request_date');
            }

            if (Schema::hasColumn('acquisition_due_diligences', 'acquisition_request_reference_no')) {
                $table->dropColumn('acquisition_request_reference_no');
            }
        });
    }
};
