<?php

namespace Modules\Admin\Http\Controllers;


use App\Http\Controllers\Controller;
use Modules\Admin\Http\Requests\PermissionStoreRequest;
use Modules\Admin\Http\Requests\PermissionUpdateRequest;
use Modules\Admin\Services\PermissionService;
use Spatie\Permission\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function __construct(private PermissionService $service) {}

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

    public function store(PermissionStoreRequest $request)
    {
        $perm = $this->service->create($request->string('name'));
        return response()->json($perm, 201);
    }

    public function show(Permission $permission)
    {
        return response()->json($permission);
    }

    public function update(PermissionUpdateRequest $request, Permission $permission)
    {
        $perm = $this->service->update($permission, $request->string('name'));
        return response()->json($perm);
    }

    public function destroy(Permission $permission)
    {
        $this->service->delete($permission);
        return response()->json([], 204);
    }
}
