<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'view-dashboard',
            'manage-employees',
            'view-employees',
            'manage-branches',
            'manage-departments',
            'manage-shifts',
            'manage-schedules',
            'view-attendance',
            'record-attendance',
            'manage-attendance',
            'approve-attendance-correction',
            'request-leave',
            'approve-leave',
            'request-overtime',
            'approve-overtime',
            'view-reports',
            'export-reports',
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and assign permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        $hrManager = Role::firstOrCreate(['name' => 'HR Manager']);
        $hrManager->givePermissionTo([
            'view-dashboard',
            'manage-employees',
            'view-employees',
            'manage-branches',
            'manage-departments',
            'manage-shifts',
            'manage-schedules',
            'view-attendance',
            'record-attendance',
            'manage-attendance',
            'approve-attendance-correction',
            'request-leave',
            'approve-leave',
            'request-overtime',
            'approve-overtime',
            'view-reports',
            'export-reports',
            'manage-settings',
        ]);

        $supervisor = Role::firstOrCreate(['name' => 'Supervisor']);
        $supervisor->givePermissionTo([
            'view-dashboard',
            'view-employees',
            'view-attendance',
            'record-attendance',
            'approve-attendance-correction',
            'request-leave',
            'approve-leave',
            'request-overtime',
            'approve-overtime',
            'view-reports',
        ]);

        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->givePermissionTo([
            'view-dashboard',
            'record-attendance',
            'view-attendance',
            'request-leave',
            'request-overtime',
        ]);
    }
}
