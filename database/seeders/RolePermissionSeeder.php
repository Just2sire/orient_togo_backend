<?php

namespace Database\Seeders;

use App\Enums\UserRoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define permissions
        $permissions = [
            'manage-users',
            'manage-content',
            'manage-quiz',
            'manage-settings',
            'view-analytics',
            'moderate-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Create roles and assign specific permissions
        $superadmin = Role::findOrCreate(UserRoleEnum::Superadmin->value);
        $superadmin->givePermissionTo(Permission::all());

        $admin = Role::findOrCreate(UserRoleEnum::Admin->value);
        $admin->givePermissionTo([
            'manage-users',
            'manage-content',
            'manage-quiz',
            'manage-settings',
            'view-analytics',
            'moderate-reports',
        ]);

        $editor = Role::findOrCreate(UserRoleEnum::Editor->value);
        $editor->givePermissionTo([
            'manage-content',
            'manage-quiz',
        ]);

        $moderator = Role::findOrCreate(UserRoleEnum::Moderator->value);
        $moderator->givePermissionTo([
            'moderate-reports',
        ]);

        $analyst = Role::findOrCreate(UserRoleEnum::Analyst->value);
        $analyst->givePermissionTo([
            'view-analytics',
        ]);

        // Creating roles for basic users so they can be assigned successfully
        Role::findOrCreate(UserRoleEnum::Advisor->value);
        Role::findOrCreate(UserRoleEnum::Parent->value);
        Role::findOrCreate(UserRoleEnum::Student->value);
    }
}
