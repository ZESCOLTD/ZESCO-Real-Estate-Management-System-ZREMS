<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lease_import_batches', 'date_order')) {
            Schema::table('lease_import_batches', function (Blueprint $table) {
                $table->string('date_order', 3)->nullable();
            });
        }
    }

    public function down(): void
    {
        // The earlier migration owns this column.
    }
};