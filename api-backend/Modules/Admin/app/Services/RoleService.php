<?php

namespace Modules\Admin\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class RoleService
{
    public function list(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Role::query()
            ->where('guard_name', 'api')
            ->when($search, fn ($q) =>
            $q->where('name', 'ILIKE', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Role::query()
            ->where('guard_name', 'api')
            ->orderBy('name')
            ->get();
    }

    public function create(string $name, array $permissionNames = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'api',
        ]);

        if (!empty($permissionNames)) {
            $perms = Permission::whereIn('name', $permissionNames)->where('guard_name', 'api')->get();
            $role->syncPermissions($perms);
        }

        return $role->load('permissions');
    }

    public function update(Role $role, string $name, array $permissionNames = []): Role
    {
        $role->update(['name' => $name]);

        // Atualiza permissões (se vier vazio, zera)
        $perms = Permission::whereIn('name', $permissionNames)->where('guard_name', 'api')->get();
        $role->syncPermissions($perms);

        return $role->load('permissions');
    }

    public function delete(Role $role): void
    {
        $role->delete();
    }

    public function syncPermissions(Role $role, array $permissionNames): Role
    {
        $perms = Permission::whereIn('name', $permissionNames)->where('guard_name', 'api')->get();
        $role->syncPermissions($perms);
        return $role->load('permissions');
    }
}
