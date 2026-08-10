<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role as RoleModel;

class RolePermissionSeeder extends Seeder
{
    /**
     * Create the platform roles and permissions.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (Permission::cases() as $permission) {
            PermissionModel::updateOrCreate(['name' => $permission->value]);
        }

        foreach (Role::cases() as $role) {
            $model = RoleModel::updateOrCreate(['name' => $role->value]);

            $model->syncPermissions(
                array_map(
                    static fn (Permission $p): string => $p->value,
                    Permission::forRole($role),
                )
            );
        }
    }
}
