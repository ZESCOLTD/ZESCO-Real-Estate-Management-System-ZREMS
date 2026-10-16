<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
//            RoleSeeder::class,
//            WorkflowSeeder::class,
////            WorkflowStatusSeeder::class,
//            ServiceFormTemplatesSeeder::class,
//            SecurityServiceFormTemplatesSeeder::class,
//            SheqServiceFormTemplatesSeeder::class,
//            CurrencySeeder::class,

            RolePermissionSeeder::class,
            SuperAdministratorSeeder::class,
        ]);
    }
}
