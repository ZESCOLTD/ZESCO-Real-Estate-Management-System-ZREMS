<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkflowStatusSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['code' => 'DRAFT', 'name' => 'Draft'],
            ['code' => 'SUBMITTED', 'name' => 'Submitted'],
//            ['code' => 'PENDING_LINE_MANAGER', 'name' => 'Pending Line Manager Approval'],
            ['code' => 'PENDING_DEPARTMENT_HEAD', 'name' => 'Pending Department Head Approval'],
            ['code' => 'PENDING_CHIEF_ACCOUNTANT', 'name' => 'Pending Chief Accountant Approval'],
            ['code' => 'PENDING_PROCUREMENT_APPROVER', 'name' => 'Pending Procurement Approval'],
            ['code' => 'REVERTED', 'name' => 'Reverted'],
            ['code' => 'REJECTED', 'name' => 'Rejected'],
            ['code' => 'APPROVED', 'name' => 'Approved'],
        ];

        foreach ($statuses as $status) {
            DB::table('workflow_statuses')->updateOrInsert(
                ['code' => $status['code']],
                [
                    'name'       => $status['name'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
