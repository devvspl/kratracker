<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $admin = Role::create(['name' => 'Admin']);
        $manager = Role::create(['name' => 'Manager']);
        $employee = Role::create(['name' => 'Employee']);

        // Create permissions
        $permissions = [
            'manage-masters',
            'view-all-logs',
            'manage-own-logs',
            'add-manager-feedback',
            'add-self-feedback',
            'view-analytics',
            'export-data',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $admin->givePermissionTo(Permission::all());
        $manager->givePermissionTo(['view-all-logs', 'add-manager-feedback', 'view-analytics', 'export-data']);
        $employee->givePermissionTo(['manage-own-logs', 'add-self-feedback', 'view-analytics']);
    }
}
