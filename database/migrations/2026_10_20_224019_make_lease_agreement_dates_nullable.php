<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (
            DB::table('lease_agreements')
                ->whereNull('start_date')
                ->orWhereNull('end_date')
                ->exists()
        ) {
            throw new RuntimeException(
                'Cannot require dates while lease agreements with missing dates exist.'
            );
        }

        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->date('start_date')->nullable(false)->change();
            $table->date('end_date')->nullable(false)->change();
        });
    }
};