<?php

namespace Modules\Admin\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Collection;

class PermissionService
{
    public function list(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Permission::query()
            ->where('guard_name', 'api')
            ->when($search, fn ($q) =>
            $q->where('name', 'ILIKE', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function all(): Collection
    {
        return Permission::query()
            ->where('guard_name', 'api')
            ->orderBy('name')
            ->get();
    }

    public function create(string $name): Permission
    {
        return Permission::create([
            'name' => $name,
            'guard_name' => 'api',
        ]);
    }

    public function update(Permission $permission, string $name): Permission
    {
        $permission->update(['name' => $name]);
        return $permission;
    }

    public function delete(Permission $permission): void
    {
        $permission->delete();
    }
}
