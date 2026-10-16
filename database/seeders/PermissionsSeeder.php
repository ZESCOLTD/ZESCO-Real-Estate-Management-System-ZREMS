<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
//        DB::table('model_has_permissions')->truncate();
//        Permission::query()->truncate();

        $data = $this->data();
        foreach ($data as $value) {
            Permission::updateOrCreate([
                'name' => $value,
            ],[
                'name' => $value,
            ]);
        }
    }

    public function data()
    {
        return config('perm');
    }

    public function crudActions($name)
    {
        $actions = [];
        // list of permission actions
        $crud = ['create', 'read', 'update', 'delete'];

        foreach ($crud as $value) {
            $actions[] = $value.' '.$name;
        }

        return $actions;
    }
}
