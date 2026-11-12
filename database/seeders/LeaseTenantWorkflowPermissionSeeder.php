<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LeaseTenantWorkflowPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = collect([
            'lease_tenant_workflow.view',
            'lease_tenant_workflow.manage',
            'lease_agreements.view',
        ])->map(fn (string $permission) => Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]));

        Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', [
                'Administrator',
                'Super Administrator',
                'Senior Real Estate Officer',
                'Principal Real Estate Officer',
                'Manager Real Estate',
                'Senior Manager Real Estate',
                'Head Real Estate',
                'Director Real Estate',
            ])
            ->get()
            ->each(fn (Role $role) => $role->givePermissionTo($permissions));

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}