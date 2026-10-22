<?php

namespace Database\Seeders;

use App\Models\WorkflowDefinition;
use App\Models\WorkflowStep;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RealEstateProjectProposalWorkflowSeeder extends Seeder
{
    public function run(): void
    {
        $projectPermissions = collect(['view', 'create', 'update', 'submit', 'approve', 'export'])
            ->map(fn (string $action) => Permission::firstOrCreate([
                'name' => "real_estate_projects.{$action}",
                'guard_name' => 'web',
            ]))
            ->keyBy('name');

        $approverNames = [
            'Senior Real Estate Officer',
            'Principal Real Estate Officer',
            'Manager Real Estate',
            'Senior Manager Real Estate',
            'Head Real Estate',
            'Director Real Estate',
        ];

        foreach ($approverNames as $approverName) {
            Role::firstOrCreate(['name' => $approverName, 'guard_name' => 'web'])
                ->givePermissionTo($projectPermissions->only(['real_estate_projects.view', 'real_estate_projects.approve'])->values()->all());
        }

        foreach (['Administrator', 'Asset Officer'] as $initiatorRole) {
            Role::firstOrCreate(['name' => $initiatorRole, 'guard_name' => 'web'])
                ->givePermissionTo($projectPermissions->only([
                    'real_estate_projects.view',
                    'real_estate_projects.create',
                    'real_estate_projects.update',
                    'real_estate_projects.submit',
                    'real_estate_projects.export',
                ])->values()->all());
        }

        Role::firstOrCreate(['name' => 'Super Administrator', 'guard_name' => 'web'])
            ->givePermissionTo($projectPermissions->values()->all());

        $workflow = WorkflowDefinition::updateOrCreate(
            ['module' => 'real_estate_project_proposal'],
            [
                'name' => 'Real Estate Project Proposal',
                'description' => 'Sequential review of a real estate project proposal. Final approval allows progression for further consideration; it is not expenditure authority.',
                'is_active' => true,
                'is_default' => true,
            ]
        );

        $stages = [
            'Senior Real Estate Officer',
            'Principal Real Estate Officer',
            'Manager Real Estate',
            'Senior Manager Real Estate',
            'Head Real Estate',
            'Director Real Estate',
        ];

        foreach ($stages as $index => $stageName) {
            WorkflowStep::updateOrCreate(
                [
                    'workflow_definition_id' => $workflow->id,
                    'step_order' => $index + 1,
                ],
                [
                    'step_name' => $stageName,
                    'step_code' => 'real_estate_' . ($index + 1),
                    'approver_type' => 'user',
                    'approver_value' => null,
                    'allow_approve' => true,
                    'allow_reject' => true,
                    'allow_revert' => true,
                    'comment_required' => true,
                    'is_active' => true,
                ]
            );
        }
    }
}