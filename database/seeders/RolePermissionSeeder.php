<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset Spatie's cache so repeated runs pick up new permissions.
        Artisan::call('permission:cache-reset');

        $guard = 'web';

        // ---------------------------------------------------------------
        // Permissions
        // ---------------------------------------------------------------
        $brandPermissions = [
            'brand.manage_badges',
            'brand.manage_polls',
            'brand.manage_events',
            'brand.manage_trophies',
            'brand.manage_rules',
            'brand.manage_guild',
            'brand.view_stats',
            'brand.manage_hall',
        ];

        $moderatorPermissions = [
            'tr.moderate_feed',
            'tr.moderate_comments',
            'tr.moderate_users',
        ];

        $adminPermissions = [
            'tr.verify_brands',
            'tr.feature_halls',
            'tr.manage_users',
            'tr.assign_roles',
            'tr.view_audit_log',
            'tr.manage_reserved_usernames',
        ];

        $superadminPermissions = [
            'tr.impersonate_users',
            'tr.assign_superadmin',
            'tr.delete_users',
            'tr.manage_system',
        ];

        $allPermissions = array_merge(
            $brandPermissions,
            $moderatorPermissions,
            $adminPermissions,
            $superadminPermissions
        );

        foreach ($allPermissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
        }

        // ---------------------------------------------------------------
        // Roles
        // ---------------------------------------------------------------
        // member — default role, no special permissions
        Role::firstOrCreate(['name' => 'member', 'guard_name' => $guard]);

        // brand_admin — manages their own brand Hall
        $brandAdmin = Role::firstOrCreate(['name' => 'brand_admin', 'guard_name' => $guard]);
        $brandAdmin->syncPermissions($brandPermissions);

        // tr_moderator — platform-level moderation
        $moderator = Role::firstOrCreate(['name' => 'tr_moderator', 'guard_name' => $guard]);
        $moderator->syncPermissions($moderatorPermissions);

        // tr_admin — brand + moderator + admin permissions
        $admin = Role::firstOrCreate(['name' => 'tr_admin', 'guard_name' => $guard]);
        $admin->syncPermissions(array_merge(
            $brandPermissions,
            $moderatorPermissions,
            $adminPermissions
        ));

        // tr_superadmin — every permission
        $superadmin = Role::firstOrCreate(['name' => 'tr_superadmin', 'guard_name' => $guard]);
        $superadmin->syncPermissions(Permission::where('guard_name', $guard)->get());
    }
}
