<?php

namespace Modules\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\Models\User;
use Modules\Admin\Models\Menu;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeed extends Seeder
{
    public function run()
    {
        // Permissões
        $perms = [
            'dashboard.view',
            'users.read', 'users.create', 'users.update', 'users.delete',
            'settings.read',
            'roles.read', 'roles.create', 'roles.delete',
            'permissions.read', 'permissions.create', 'permissions.delete',
        ];

        foreach ($perms as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'api']);
        }

        // Roles
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'api']);
        $admin->syncPermissions(Permission::all());

        $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => 'api']);
        $userRole->syncPermissions([
            'dashboard.view',
            'users.read',
        ]);

        // Menus
        $dashboard = Menu::firstOrCreate([
            'title' => 'Dashboard',
            'route' => '/dashboard',
            'icon'  => 'lucide:home',
            'permission' => 'dashboard.view',
            'sort' => 1
        ]);

        $users = Menu::firstOrCreate([
            'title' => 'Users',
            'route' => null,
            'icon'  => 'lucide:users',
            'permission' => 'users.read',
            'sort' => 2
        ]);

        Menu::firstOrCreate([
            'title' => 'List',
            'route' => '/users',
            'icon'  => null,
            'permission' => 'users.read',
            'parent_id' => $users->id,
            'sort' => 1
        ]);

        Menu::firstOrCreate([
            'title' => 'Create',
            'route' => '/users/create',
            'icon'  => null,
            'permission' => 'users.create',
            'parent_id' => $users->id,
            'sort' => 2
        ]);

        Menu::firstOrCreate([
            'title' => 'Settings',
            'route' => '/settings',
            'icon'  => 'lucide:settings',
            'permission' => 'settings.read',
            'sort' => 99
        ]);

        // 🔹 Vincular usuários às roles
        $adminUser = User::where('email', 'admin@local.test')->first();
        if ($adminUser) {
            $adminUser->syncRoles(['admin']);
        }

        $otherUsers = User::where('email', '<>', 'admin@local.test')->get();
        foreach ($otherUsers as $u) {
            $u->syncRoles(['user']);
        }
    }
}
