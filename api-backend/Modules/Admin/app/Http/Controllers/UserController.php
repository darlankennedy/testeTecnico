<?php

namespace Modules\Admin\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Admin\Http\Requests\IndexUserRequest;
use Modules\Admin\Http\Requests\StoreUserRequest;
use Modules\Admin\Http\Requests\UserResource;
use Modules\Admin\Services\UserService;

class UserController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(IndexUserRequest $request)
    {
        $dto = $request->toDto();

        $params = [
            'per_page'  => $dto->perPage,
            'search'    => $dto->search,
            'order_by'  => $dto->orderBy,
            'direction' => $dto->direction,
            'with'      => $dto->with,
            'filters'   => $dto->filters,
        ];

        $paginator = $this->userService->paginate($params);

        return UserResource::collection($paginator);
    }

    public function show($id)
    {
        $user = $this->userService->getUserById($id);
        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        return  new UserResource($user);
    }

    public function store(StoreUserRequest $request) {

        $user = $this->userService->createUser($request->all());
        if (!$user) {
            return response()->json(['message' => 'User could not be created.'], 500);
        }
        return response()->json(new UserResource($user), 201);
    }

    public function update(Request $request, $id) {
        $user = $this->userService->updateUser($id, $request->all());
        if (!$user) {
            return response()->json(['message' => 'User could not be updated.'], 500);
        }
        return response()->json(new UserResource($user), 200);
    }

}
