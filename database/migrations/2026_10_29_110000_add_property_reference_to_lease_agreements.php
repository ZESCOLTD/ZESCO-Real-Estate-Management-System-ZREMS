<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lease_agreements', 'property_reference')) {
            Schema::table('lease_agreements', function (Blueprint $table) {
                $table->string('property_reference')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lease_agreements', 'property_reference')) {
            Schema::table('lease_agreements', function (Blueprint $table) {
                $table->dropColumn('property_reference');
            });
        }
    }
};