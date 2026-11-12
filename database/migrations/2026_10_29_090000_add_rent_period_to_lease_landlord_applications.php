<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lease_landlord_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('lease_landlord_applications', 'proposed_rent_period')) {
                $table->string('proposed_rent_period', 24)->nullable()->after('proposed_monthly_rent');
            }
            if (! Schema::hasColumn('lease_landlord_applications', 'proposed_rent_amount')) {
                $table->decimal('proposed_rent_amount', 18, 2)->nullable()->after('proposed_rent_period');
            }
        });

        DB::table('lease_landlord_applications')
            ->whereNull('proposed_rent_period')
            ->whereNotNull('proposed_monthly_rent')
            ->update([
                'proposed_rent_period' => 'monthly',
                'proposed_rent_amount' => DB::raw('proposed_monthly_rent'),
            ]);
    }

    public function down(): void
    {
        Schema::table('lease_landlord_applications', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'proposed_rent_period',
                'proposed_rent_amount',
            ], fn (string $column) => Schema::hasColumn('lease_landlord_applications', $column)));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};