<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;

class WorkflowSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // =========================
            // 1. ACQUISITION REQUEST WORKFLOW
            // =========================
            $acquisitionRequest = WorkflowDefinition::updateOrCreate(
                ['module' => 'acquisition_request'],
                [
                    'name' => 'Acquisition Request Approval',
                    'description' => 'Workflow for acquisition request approvals',
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1, // adjust if needed
                ]
            );

            $this->createSteps($acquisitionRequest->id, [
                [
                    'step_order' => 1,
                    'step_name' => 'HOD Approval',
                    'step_code' => 'hod',
                    'approver_type' => 'role',
                    'approver_value' => 'hod',
                ],
                [
                    'step_order' => 2,
                    'step_name' => 'Chief Accountant Approval',
                    'step_code' => 'chief_accountant',
                    'approver_type' => 'role',
                    'approver_value' => 'chief_accountant',
                ],
                [
                    'step_order' => 3,
                    'step_name' => 'Property Manager Approval',
                    'step_code' => 'property',
                    'approver_type' => 'role',
                    'approver_value' => 'property',
                ],
            ]);

            // =========================
            // 2. DUE DILIGENCE WORKFLOW
            // =========================
            $dueDiligence = WorkflowDefinition::updateOrCreate(
                ['module' => 'acquisition_due_diligence'],
                [
                    'name' => 'Due Diligence Signoff',
                    'description' => 'Role-based due diligence approvals',
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                ]
            );

            $this->createSteps($dueDiligence->id, [
                [
                    'step_order' => 1,
                    'step_name' => 'Property Manager Approval',
                    'step_code' => 'property',
                    'approver_type' => 'role',
                    'approver_value' => 'property',
                ],
                [
                    'step_order' => 2,
                    'step_name' => 'HOD Approval',
                    'step_code' => 'hod',
                    'approver_type' => 'role',
                    'approver_value' => 'hod',
                ],
                [
                    'step_order' => 3,
                    'step_name' => 'Chief Accountant Approval',
                    'step_code' => 'chief_accountant',
                    'approver_type' => 'role',
                    'approver_value' => 'chief_accountant',
                ],
                [
                    'step_order' => 4,
                    'step_name' => 'Project Accountant Approval',
                    'step_code' => 'project_accountant',
                    'approver_type' => 'role',
                    'approver_value' => 'project_accountant',
                ],
                [
                    'step_order' => 5,
                    'step_name' => 'Procurement Approval',
                    'step_code' => 'procurement',
                    'approver_type' => 'role',
                    'approver_value' => 'procurement',
                ],
            ]);
            // =========================
            // 2. DUE DILIGENCE WORKFLOW
            // =========================
            $dueDiligence = WorkflowDefinition::updateOrCreate(
                ['module' => 'geomatic_request'],
                [
                    'name' => 'Geomatic request',
                    'description' => 'Geomatic Applicant Request',
                    'is_active' => true,
                    'is_default' => true,
                    'created_by' => 1,
                ]
            );

            $this->createSteps($dueDiligence->id, [
                [
                    'step_order' => 1,
                    'step_name' => 'Head ESD',
                    'step_code' => 'head_esd',
                    'approver_type' => 'role',
                    'approver_value' => 'head_esd',
                ],
                [
                    'step_order' => 2,
                    'step_name' => 'HOD Approval',
                    'step_code' => 'hod',
                    'approver_type' => 'role',
                    'approver_value' => 'hod',
                ],
                [
                    'step_order' => 3,
                    'step_name' => 'Chief Accountant Approval',
                    'step_code' => 'chief_accountant',
                    'approver_type' => 'role',
                    'approver_value' => 'chief_accountant',
                ]
            ]);
        });
    }

    /**
     * Create workflow steps safely
     */
    protected function createSteps($definitionId, array $steps)
    {
        foreach ($steps as $step) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_definition_id' => $definitionId,
                    'step_order' => $step['step_order'],
                ],
                [
                    'step_name' => $step['step_name'],
                    'step_code' => $step['step_code'],
                    'approver_type' => $step['approver_type'],
                    'approver_value' => $step['approver_value'],
                    'allow_revert' => true,
                    'allow_reject' => true,
                    'allow_approve' => true,
                    'comment_required' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}
