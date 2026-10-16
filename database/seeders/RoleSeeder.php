<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Initiator',
            'Department Head',
            'Chief Accountant',
            'Procurement Approver',
            'Workflow Admin',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate([
                'name' => $role,
                'guard_name' => 'web',
            ]);
        }
//            DB::table('roles')->updateOrInsert(
//                ['name' => $role],
//                [
//                    'created_at' => Carbon::now(),
//                    'updated_at' => Carbon::now(),
//                ]
//            );
//        }
    }
}
