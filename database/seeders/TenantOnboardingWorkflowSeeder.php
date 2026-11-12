<?php

namespace Database\Seeders;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;

class TenantOnboardingWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $workflow = WorkflowDefinition::updateOrCreate(
            ['module' => 'tenant_onboarding'],
            [
                'name' => 'Tenant Onboarding Verification',
                'description' => 'Review submitted tenant onboarding applications and verify or return them for correction.',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        WorkflowStep::updateOrCreate(
            [
                'workflow_definition_id' => $workflow->id,
                'step_order' => 1,
            ],
            [
                'step_name' => 'Tenant Verification',
                'step_code' => 'tenant_verification',
                'approver_type' => 'role',
                'approver_value' => 'Tenant Verification Officer',
                'allow_approve' => true,
                'allow_reject' => false,
                'allow_revert' => true,
                'comment_required' => false,
                'is_active' => true,
            ]
        );
    }
}