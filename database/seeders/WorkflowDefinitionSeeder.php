<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkflowDefinitionSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            /**
             * Remove old default workflow for acquisition_request module if it exists
             */
            $existingWorkflow = DB::table('workflow_definitions')
                ->where('module', 'acquisition_request')
                ->where('is_default', true)
                ->first();

            if ($existingWorkflow) {
                DB::table('workflow_steps')
                    ->where('workflow_definition_id', $existingWorkflow->id)
                    ->delete();

                DB::table('workflow_definitions')
                    ->where('id', $existingWorkflow->id)
                    ->delete();
            }

            /**
             * Create workflow definition
             */
            $workflowId = DB::table('workflow_definitions')->insertGetId([
                'name'        => 'Land Acquisition Approval Workflow',
                'module'      => 'acquisition_request',
                'description' => 'Default approval workflow for land acquisition requests.',
                'is_active'   => true,
                'is_default'  => true,
                'created_by'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);

            /**
             * Create workflow steps
             */
            DB::table('workflow_steps')->insert([

                [
                    'workflow_definition_id' => $workflowId,
                    'step_order'             => 1,
                    'step_name'              => 'Department Head Approval',
                    'step_code'              => 'DEPARTMENT_HEAD',
                    'approver_type'          => 'role',
                    'approver_value'         => 'Department Head',
                    'allow_revert'           => true,
                    'allow_approve'          => true,
                    'allow_reject'           => true,
                    'comment_required'       => true,
                    'is_active'              => true,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ],
                [
                    'workflow_definition_id' => $workflowId,
                    'step_order'             => 2,
                    'step_name'              => 'Chief Accountant Approval',
                    'step_code'              => 'CHIEF_ACCOUNTANT',
                    'approver_type'          => 'role',
                    'approver_value'         => 'Chief Accountant',
                    'allow_revert'           => true,
                    'allow_approve'          => true,
                    'allow_reject'           => true,
                    'comment_required'       => true,
                    'is_active'              => true,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ],
                [
                    'workflow_definition_id' => $workflowId,
                    'step_order'             => 3,
                    'step_name'              => 'Procurement Approval',
                    'step_code'              => 'PROCUREMENT_APPROVER',
                    'approver_type'          => 'role',
                    'approver_value'         => 'Procurement Team',
                    'allow_revert'           => true,
                    'allow_approve'          => true,
                    'allow_reject'           => true,
                    'comment_required'       => true,
                    'is_active'              => true,
                    'created_at'             => $now,
                    'updated_at'             => $now,
                ],
            ]);
        });
    }
}
