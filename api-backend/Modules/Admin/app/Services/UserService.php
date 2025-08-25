<?php

namespace Modules\Admin\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Modules\Admin\Repositories\UserRepository;

class UserService
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllUsers(array $columns = ['*'], array $relations = [], array $conditions = []): mixed
    {
        return $this->repository->all($columns, $relations, $conditions);
    }

    public function getUserById(int|string $id): ?Model
    {
        return $this->repository->find($id);
    }

    public function createUser(array $data): ?Model
    {
        return $this->repository->create($data);
    }

    public function updateUser(int|string $id, array $data): ?Model
    {
        return $this->repository->update($data, $id);
    }

    public function deleteUser(int|string $id): bool
    {
        return $this->repository->delete($id);
    }

    public function paginate(array $params = []): LengthAwarePaginator|Collection
    {
        $perPage    = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $orderBy    = $params['order_by']   ?? 'id';
        $direction  = $params['direction']  ?? 'asc';
        $with       = is_array($params['with'] ?? null) ? $params['with'] : [];
        $filters    = is_array($params['filters'] ?? null) ? $params['filters'] : [];
        $conditions = is_array($params['conditions'] ?? null) ? $params['conditions'] : [];

        if (!empty($params['search'])) {
            $filters['search'] = trim((string) $params['search']);
        }

        return $this->repository->paginate(
            perPage:    $perPage,
            filters:    $filters,
            with:       $with,
            conditions: $conditions,
            orderBy:    $orderBy,
            direction:  $direction
        );
    }
}
