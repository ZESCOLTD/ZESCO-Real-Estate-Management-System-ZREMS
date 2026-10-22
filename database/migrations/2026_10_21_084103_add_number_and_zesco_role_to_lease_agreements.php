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
            $table->enum('zesco_role', ['tenant', 'landlord'])
                ->default('tenant')
                ->after('id');

            $table->string('lease_number', 30)
                ->nullable()
                ->unique()
                ->after('zesco_role');
        });

        // All leases currently in this register have ZESCO as tenant.
        DB::table('lease_agreements')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($leases) {
                foreach ($leases as $lease) {
                    DB::table('lease_agreements')
                        ->where('id', $lease->id)
                        ->update([
                            'lease_number' => 'ZT-' . str_pad(
                                (string) $lease->id,
                                6,
                                '0',
                                STR_PAD_LEFT
                            ),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('lease_agreements', function (Blueprint $table) {
            $table->dropUnique(['lease_number']);
            $table->dropColumn(['lease_number', 'zesco_role']);
        });
    }
};