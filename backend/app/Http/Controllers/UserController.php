<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Service\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class UserController extends Controller
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * List all users
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage   = (int) $request->integer('per_page', 10);
            $orderBy   = (string) $request->get('order_by', 'id');
            $direction = (string) $request->get('direction', 'asc');

            // Monte os filtros no formato que o BaseRepository entende
            $filters = [
                'search'      => $request->string('search')->toString(),              // texto livre
                'status'      => $request->input('status'),                           // string ou array
                'company_id'  => $request->input('company_id'),                       // numérico
                'created_at'  => [                                                    // range (datas)
                    'from' => $request->input('created_at_from'),
                    'to'   => $request->input('created_at_to'),
                ],
            ];
            $result = $this->userService->paginate($perPage, $filters, $orderBy, $direction);
            return response()->json([
                'data' => $result['data'] ?? [],
                'meta' => [
                    'total'        => $result['total'] ?? 0,
                    'per_page'     => $result['per_page'] ?? $perPage,
                    'current_page' => $result['current_page'] ?? (int) $request->integer('page', 1),
                    'last_page'    => $result['last_page'] ?? 1,
                ],
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response()->json(['message' => 'Unable to paginate users.'], 500);
        }
    }
    /**
     * Show a single user
     */
    public function show(int $id): JsonResponse
    {
        $user = $this->userService->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json($user);
    }

    /**
     * Create a new user
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $data = $request->only(['name', 'email', 'cpf', 'password' ]);
        $user = $this->userService->create($data);

        if (!$user) {
            return response()->json(['message' => 'Failed to create user'], 500);
        }

        return response()->json($user, 201);
    }

    /**
     * Update an existing user
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $data = $request->only(['name', 'email', 'password']);

        $updated = $this->userService->update($id, $data);

        if (!$updated) {
            return response()->json(['message' => 'Failed to update user'], 500);
        }

        return response()->json(['message' => 'User updated successfully']);
    }

    /**
     * Delete a user
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->userService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Failed to delete user'], 500);
        }

        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get all users with at least one product.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function usersWithProducts(): JsonResponse
    {
        $users = $this->userService->usersWithProducts();
        return response()->json($users);
    }

    /**
     * Get all users with no products.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function usersWithoutProducts(): JsonResponse
    {
        $users = $this->userService->usersWithoutProducts();
        return response()->json($users);
    }

    /**
     * Count Users.
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function usersCount(): JsonResponse
    {
        $users = $this->userService->usersCount();
        return response()->json($users);
    }


    /**
     * @throws \Exception
     */
    public function listSimple(Request $request): array
    {
        return $this->userService->all();
    }

}
