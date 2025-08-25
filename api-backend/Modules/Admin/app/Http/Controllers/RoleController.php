<?php

namespace Modules\Admin\Http\Controllers;


use App\Http\Controllers\Controller;
use Modules\Admin\Http\Requests\RoleStoreRequest;
use Modules\Admin\Http\Requests\RoleUpdateRequest;
use Modules\Admin\Services\RoleService;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function __construct(private RoleService $service) {}

    public function index(Request $request)
    {
        $data = $this->service->list(
            search: $request->query('search'),
            perPage: (int) $request->query('per_page', 15)
        );
        return response()->json($data);
    }

    public function all()
    {
        return response()->json($this->service->all());
    }

    public function store(RoleStoreRequest $request)
    {
        $role = $this->service->create(
            name: $request->string('name'),
            permissionNames: $request->input('permissions', [])
        );
        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        return response()->json($role->load('permissions'));
    }

    public function update(RoleUpdateRequest $request, Role $role)
    {
        $role = $this->service->update(
            role: $role,
            name: $request->string('name'),
            permissionNames: $request->input('permissions', [])
        );
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $this->service->delete($role);
        return response()->json([], 204);
    }

    public function syncPermissions(Request $request, Role $role)
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = $this->service->syncPermissions($role, $validated['permissions'] ?? []);
        return response()->json($role);
    }
}
