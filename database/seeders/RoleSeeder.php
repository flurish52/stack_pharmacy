<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'manage-system-settings', 'manage-staff', 'manage-products',
            'manage-categories', 'manage-services', 'manage-training',
            'manage-contact', 'manage-pickup-points', 'view-orders',
            'update-order-status', 'cancel-order', 'view-activity-log',
            'view-reports', 'manage-about',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        Role::firstOrCreate(['name' => 'owner'])->givePermissionTo([
            'manage-staff', 'manage-products', 'manage-categories', 'manage-services',
            'manage-training', 'manage-contact', 'manage-pickup-points', 'view-orders',
            'update-order-status', 'cancel-order', 'view-activity-log', 'view-reports', 'manage-about',
        ]);

        Role::firstOrCreate(['name' => 'admin'])->givePermissionTo([
            'manage-products', 'manage-categories', 'manage-services', 'manage-training',
            'manage-contact', 'manage-pickup-points', 'view-orders', 'update-order-status', 'cancel-order', 'manage-about',
        ]);

        Role::firstOrCreate(['name' => 'staff'])->givePermissionTo([
            'view-orders', 'update-order-status',
        ]);

        Role::firstOrCreate(['name' => 'customer']);
    }
}
