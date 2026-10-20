<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LeaseStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'lease_to_be_executed', 'name' => 'Lease to be executed'],
            [
                'code' => 'awaiting_landlord_execution',
                'name' => 'Duly executed by ZESCO awaiting execution by the landlord',
            ],
            ['code' => 'executed', 'name' => 'Executed'],
            ['code' => 'ongoing', 'name' => 'On-going'],
            ['code' => 'lease_to_be_renewed', 'name' => 'Lease to be renewed'],
            ['code' => 'expired', 'name' => 'Expired'],
        ];

        foreach ($statuses as $status) {
            $exists = DB::table('lease_statuses')
                ->where('code', $status['code'])
                ->exists();

            if (! $exists) {
                DB::table('lease_statuses')->insert([
                    'code' => $status['code'],
                    'name' => $status['name'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}