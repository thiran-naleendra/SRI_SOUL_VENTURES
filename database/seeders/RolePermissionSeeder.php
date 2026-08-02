<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'admin.dashboard.view',
            'settings.manage',
            'pages.manage',
            'destinations.view',
            'destinations.create',
            'destinations.update',
            'destinations.delete',
            'experiences.view',
            'experiences.create',
            'experiences.update',
            'experiences.delete',
            'packages.view',
            'packages.create',
            'packages.update',
            'packages.delete',
            'enquiries.view',
            'enquiries.update',
            'custom_tours.view',
            'custom_tours.update',
            'testimonials.manage',
            'team.manage',
            'faqs.manage',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $contentPermissions = [
            'admin.dashboard.view',
            'pages.manage',
            'destinations.view',
            'destinations.create',
            'destinations.update',
            'destinations.delete',
            'experiences.view',
            'experiences.create',
            'experiences.update',
            'experiences.delete',
            'packages.view',
            'packages.create',
            'packages.update',
            'packages.delete',
            'testimonials.manage',
            'team.manage',
            'faqs.manage',
        ];

        $consultantPermissions = [
            'admin.dashboard.view',
            'enquiries.view',
            'enquiries.update',
            'custom_tours.view',
            'custom_tours.update',
        ];

        Role::findOrCreate('super_admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('administrator', 'web')->syncPermissions($permissions);
        Role::findOrCreate('content_manager', 'web')->syncPermissions($contentPermissions);
        Role::findOrCreate('tour_consultant', 'web')->syncPermissions($consultantPermissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
