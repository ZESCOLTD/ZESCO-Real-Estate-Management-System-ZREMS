<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class TenantRegisterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = collect(['view', 'create', 'update', 'export'])
            ->map(fn (string $action) => Permission::firstOrCreate([
                'name' => "tenant_register.{$action}",
                'guard_name' => 'web',
            ]))
            ->values()
            ->all();

        foreach (['Super Administrator', 'Administrator', 'Asset Officer', 'Tenant Officer'] as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web'])
                ->givePermissionTo($permissions);
        }
    }
}