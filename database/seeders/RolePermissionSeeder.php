<?php

namespace Database\Seeders;

use App\Domain\Shared\Enums\AdminRole;
use App\Domain\Shared\Enums\Permission;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission as PermissionModel;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $registrar = app(PermissionRegistrar::class);

        // Evita cache obsoleto de permissions/roles entre execuções (ex.: migrate:fresh --seed).
        // Chamado antes e depois do loop porque eventos de Model (que normalmente invalidam
        // o cache) podem estar suprimidos pelo runner do seeder (ex.: WithoutModelEvents).
        $registrar->forgetCachedPermissions();

        foreach (Permission::values() as $permission) {
            PermissionModel::findOrCreate($permission, 'web');
        }

        $registrar->forgetCachedPermissions();

        $superAdmin = Role::findOrCreate(AdminRole::SuperAdmin->value, 'web');
        $superAdmin->syncPermissions(Permission::values());

        $administrador = Role::findOrCreate(AdminRole::Administrador->value, 'web');
        $administrador->syncPermissions([
            Permission::UsersView->value,
        ]);
    }
}
