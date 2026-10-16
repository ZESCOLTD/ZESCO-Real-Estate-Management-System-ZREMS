<?php

namespace App\Console\Commands\Permission;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissionCommand extends Command
{
    protected $signature = 'app:permission:sync {--force : Force the synchronization by detaching permissions from roles}';

    protected $description = 'Sync permissions from file to database';

    public function handle()
    {
        $filePath = database_path('seeders/PermissionSeederData.php');
        $filePermissions = include $filePath;
        $databasePermissions = Permission::pluck('name')->toArray();

        // Add new permissions from the file to the database
        $permissionsToAdd = array_diff($filePermissions, $databasePermissions);
        foreach ($permissionsToAdd as $permission) {
            Permission::create(['name' => $permission]);
            $this->info("Permission '{$permission}' added to the database.");
        }

        // Remove deleted permissions from the file in the database
        $permissionsToRemove = array_diff($databasePermissions, $filePermissions);
        foreach ($permissionsToRemove as $permission) {
            $permissionModel = Permission::where('name', $permission)->first();
            if ($permissionModel) {
                $rolesWithPermission = Role::whereHas('permissions', function ($query) use ($permission) {
                    $query->where('name', $permission);
                })->get();
                if ($rolesWithPermission->isNotEmpty()) {
                    $this->error("Permission '{$permission}' is assigned to the following roles:");
                    foreach ($rolesWithPermission as $role) {
                        $this->line($role->name);
                    }
                    if ($this->option('force')) {
                        $this->line("Detaching the permission '{$permission}' from the roles.");
                        foreach ($rolesWithPermission as $role) {
                            $role->revokePermissionTo($permission);
                        }
                    }
                }

                if ($this->option('force') || $rolesWithPermission->isEmpty()) {
                    $permissionModel->delete();
                    $this->info("Permission '{$permission}' removed from the database.");
                }
            }
        }

        $this->info('Permissions have been synchronized between the file and the database.');

        $this->createPermFile();
    }

    public function createPermFile()
    {
        try {

            if (\App\Models\Permission::get('name')->count() >= 1) {

                // open config file for writing
                $fp = fopen(base_path() . '/config/perm.php', 'w');
                fwrite($fp, '<?php return ');

                foreach (Permission::get('name') as $permission) {
                    //edit config for current runtime
//                    config(['permissions.' . trim(strtolower($permission->name)) => trim(strtolower($permission->name))]);
                    config(['permissions.' . trim(($permission->name)) => trim(($permission->name))]);
                }

                // write updated runtime config to file
                fwrite($fp, var_export(config('permissions'), true));
                fwrite($fp, ';');
                // close the file
                fclose($fp);

            }

            if (\App\Models\Role::count() >= 1) {

                // open config file for writing
                $fp = fopen(base_path() . '/config/roles.php', 'w');
                fwrite($fp, '<?php return ');

                foreach (Role::get() as $role) {
                    //edit config for current runtime
//                    config(['roles.' . trim(strtolower($role->name)) => trim(strtolower($role->name))]);
                    config(['roles.' . trim(($role->name)) => trim(($role->name))]);
                }

                // write updated runtime config to file
                fwrite($fp, var_export(config('roles'), true));
                fwrite($fp, ';');
                // close the file
                fclose($fp);
            }

        } catch (\Exception $e) {
            //ignored
            Log::info('Loading System Config and Error Message');
            Log::error($e);
        }
    }
}
