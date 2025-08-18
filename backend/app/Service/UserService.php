<?php

namespace App\Service;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Repository\UserRepository;
use Closure;

class UserService
{
    protected UserRepository $userRepository;

    private const CACHE_KEYS = [
        'users_count'            => 'users.count',
        'users_with_count'       => 'users.with_count',
        'users_without_count'    => 'users.without_count',
    ];

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Helper for caching results.
     * Uses Cache::tags when the driver is supported (Redis/Memcached).
     *
     * @template TReturn
     * @param string $key Unique cache key
     * @param int $ttl TTL in seconds (e.g., 600 = 10 min)
     * @param Closure():TReturn $resolver Closure that executes a query
     * @return mixed
     */
    private function remember(string $key, int $ttl, Closure $resolver)
    {
        $storeSupportsTags = method_exists(Cache::getStore(), 'tags');
        $tag = config('cachekeys.tags.users_stats', 'users_stats');

        if ($storeSupportsTags) {
            return Cache::tags([$tag])->remember($key, $ttl, $resolver);
        }

        return Cache::remember($key, $ttl, $resolver);
    }

    /**
     * Clears all keys related to user statistics.
     * (Called by Observers)
     */
    public function flushUsersStatsCache(): void
    {
        $storeSupportsTags = method_exists(Cache::getStore(), 'tags');
        $tag  = config('cachekeys.tags.users_stats', 'users_stats');

        if ($storeSupportsTags) {
            Cache::tags([$tag])->flush();
            return;
        }

        $keys = config('cachekeys.users', []);

        foreach ([
                     'with_products',
                     'without_products',
                     'with_count',
                     'without_count',
                     'count',
                 ] as $k) {
            if (!empty($keys[$k])) {
                Cache::forget($keys[$k]);
            }
        }
    }

    /**
     * List all users
     *
     * @param array $columns
     * @param array $relations
     * @param array $conditions
     * @return array<int, User>
     * @throws Exception
     */
    public function all(array $columns = ['*'], array $relations = [], array $conditions = []): array
    {
        try {
            return $this->userRepository->all($columns, $relations, $conditions)->toArray();
        } catch (Exception $e) {
            Log::error('Error listing users: ' . $e->getMessage());
            throw new Exception('Unable to list users.');
        }
    }

    /**
     * Find a user by ID
     *
     * @param int $id
     * @return Model|null
     * @throws Exception
     */
    public function find(int $id): ?Model
    {
        try {
            return $this->userRepository->find($id , ['products']);
        } catch (Exception $e) {
            Log::error('Error finding user: ' . $e->getMessage());
            throw new Exception('Unable to find the user.');
        }
    }

    /**
     * Find users by conditions
     *
     * @param array $conditions
     * @return array
     * @throws Exception
     */
    public function findBy(array $conditions): array
    {
        try {
            return $this->userRepository->findBy($conditions)->toArray();
        } catch (Exception $e) {
            Log::error('Error finding users by conditions: ' . $e->getMessage());
            throw new Exception('Unable to find users.');
        }
    }

    /**
     * Create a new user
     *
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public function create(array $data): Model
    {
        try {
            return $this->userRepository->create($data);
        } catch (Exception $e) {
            Log::error('Error creating user: ' . $e->getMessage());
            throw new Exception('Unable to create user.');
        }
    }

    /**
     * Update a user
     *
     * @param int $id
     * @param array $data
     * @return Model
     * @throws Exception
     */
    public function update(int $id, array $data): Model
    {
        try {
            $user = $this->userRepository->update($id, $data);
            if (!$user) {
                throw new Exception('User not found.');
            }
            return $user;
        } catch (Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            throw new Exception('Unable to update user.');
        }
    }

    /**
     * Delete a user
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        try {
            return $this->userRepository->delete($id);
        } catch (Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            throw new Exception('Unable to delete user.');
        }
    }

    /**
     * Check if a user exists by conditions
     *
     * @param array $conditions
     * @return bool
     * @throws Exception
     */
    public function exists(array $conditions): bool
    {
        try {
            return $this->userRepository->exists($conditions);
        } catch (Exception $e) {
            Log::error('Error checking if user exists: ' . $e->getMessage());
            throw new Exception('Unable to check if user exists.');
        }
    }

    /**
     * Paginate users
     *
     * @param int $perPage
     * @param array $filters
     * @param string $orderBy
     * @param string $direction
     * @return array
     * @throws Exception
     */
    public function paginate(
        int $perPage = 15,
        array $filters = [],
        string $orderBy = 'id',
        string $direction = 'asc'
    ): array {
        try {
            return $this->userRepository->paginate($perPage, $filters, ['products'], $orderBy, $direction)->toArray();
        } catch (Exception $e) {
            Log::error('Error paginating users: ' . $e->getMessage());
            throw new Exception('Unable to paginate users.');
        }
    }


    /**
     * List users that have at least one product.
     *
     * @return array
     * @throws Exception
     */
    public function usersWithProducts(): array
    {
        $key = config('cachekeys.users.with_products', 'users.with_products');

        return $this->remember($key, 600, function () {
            return $this->userRepository->selectUsersWithProducts()->toArray();
        });
    }

    /**
     * List users that have no products.
     *
     * @return array
     * @throws \Exception
     */
    public function usersWithoutProducts(): array
    {
        $key = config('cachekeys.users.without_products', 'users.without_products');

        return $this->remember($key, 600, function () {
            return $this->userRepository->selectUsersWithoutProducts()->toArray();
        });
    }

    /**
     * Count Users.
     *
     * @return int
     * @throws \Exception
     */
    public function usersCount(): int
    {
        $key = config('cachekeys.users.count', 'users.count');

        return (int) $this->remember($key, 600, function () {
            return $this->userRepository->countAll();
        });
    }
}
