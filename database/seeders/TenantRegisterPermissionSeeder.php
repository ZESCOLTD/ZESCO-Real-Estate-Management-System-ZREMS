<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantRegisterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(['view', 'create', 'update', 'export', 'verify'])
            ->map(fn (string $action) => Permission::firstOrCreate([
                'name' => "tenant_register.{$action}",
                'guard_name' => 'web',
            ]))
            ->values()
            ->all();
        $documentHistoryPermissions = collect([
            'tenant_register.documents.view',
            'tenant_register.documents.manage',
            'tenant_register.history.view',
        ])->map(fn (string $name) => Permission::firstOrCreate([
            'name' => $name,
            'guard_name' => 'web',
        ]))->all();
        $permissionByName = collect([...$permissions, ...$documentHistoryPermissions])->keyBy('name');

        foreach (['Super Administrator', 'Administrator'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->givePermissionTo($permissionByName->values()->all());
        }

        $registerPermissions = $permissionByName
            ->only(['tenant_register.view', 'tenant_register.create', 'tenant_register.update', 'tenant_register.export'])
            ->values()
            ->all();

        Role::firstOrCreate(['name' => 'Asset Officer', 'guard_name' => 'web'])
            ->givePermissionTo($registerPermissions);

        $documentPermissions = $permissionByName
            ->only(['tenant_register.documents.view', 'tenant_register.documents.manage', 'tenant_register.history.view'])
            ->values()
            ->all();

        Role::firstOrCreate(['name' => 'Tenant Officer', 'guard_name' => 'web'])
            ->givePermissionTo(array_merge($registerPermissions, $documentPermissions));

        $verificationPermissions = $permissionByName
            ->only([
                'tenant_register.view',
                'tenant_register.verify',
                'tenant_register.documents.view',
                'tenant_register.history.view',
            ])
            ->values()
            ->all();

        $verificationRole = Role::firstOrCreate(['name' => 'Tenant Verification Officer', 'guard_name' => 'web']);
        $verificationRole->givePermissionTo($verificationPermissions);
        $verificationRole->revokePermissionTo('tenant_register.documents.manage');
    }
}