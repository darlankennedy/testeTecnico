<?php

namespace App\Service;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Repository\ProductRepository;
use Closure;

class ProductService
{
    protected ProductRepository $productRepository;

    /**
     * ProductService constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * Cache helper with tag support (Redis/Memcached).
     *
     * @template TReturn
     * @param string $key
     * @param int $ttl seconds (e.g.: 600 = 10 min)
     * @param Closure():TReturn $resolver
     * @return mixed
     */
    private function remember(string $key, int $ttl, Closure $resolver)
    {
        $storeSupportsTags = method_exists(Cache::getStore(), 'tags');
        $tag = config('cachekeys.tags.products_stats', 'products_stats');

        if ($storeSupportsTags) {
            return Cache::tags([$tag])->remember($key, $ttl, $resolver);
        }

        return Cache::remember($key, $ttl, $resolver);
    }


    /**
     * Clears the product statistics cache.
     * Call this in observers (created/updated/deleted).
     */
    public function flushProductsStatsCache(): void
    {
        $storeSupportsTags = method_exists(Cache::getStore(), 'tags');
        $tag = config('cachekeys.tags.products_stats', 'products_stats');

        if ($storeSupportsTags) {
            Cache::tags([$tag])->flush();
            return;
        }

        $keys = config('cachekeys.products', []);
        foreach (['count', 'active_count', 'inactive_count'] as $k) {
            if (!empty($keys[$k])) {
                Cache::forget($keys[$k]);
            }
        }
    }

    /**
     * Get all products
     *
     * @param array<string> $columns
     * @param array<string> $relations
     * @param array<string,mixed> $conditions
     * @return array<int, Product>
     * @throws Exception
     */
    public function all(array $columns = ['*'], array $relations = [], array $conditions = []): array
    {
        try {
            return $this->productRepository->all($columns, $relations, $conditions)->toArray();
        } catch (Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            throw new Exception('Unable to fetch products.');
        }
    }

    /**
     * Find a product by ID
     *
     * @param int $id
     * @param array<string> $with
     * @return Model|null
     * @throws Exception
     */
    public function find(int $id): ?Model
    {
        try {
            return $this->productRepository->find($id, ['owner']);
        } catch (Exception $e) {
            Log::error('Error fetching product: ' . $e->getMessage());
            throw new Exception('Unable to fetch the product.');
        }
    }

    /**
     * Create a new product
     *
     * @param array<string,mixed> $data
     * @return Model
     * @throws Exception
     */
    public function create(array $data)
    {
        try {
            return $this->productRepository->create($data);
        } catch (Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            throw new Exception('Unable to create product.');
        }
    }

    /**
     * Update a product
     *
     * @param int $id
     * @param array<string,mixed> $data
     * @return Model
     * @throws Exception
     */
    public function update(int $id, array $data): Model
    {
        try {
            $product = $this->productRepository->update($id, $data);
            if (!$product) {
                throw new Exception('Product not found.');
            }
            return $product;
        } catch (Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            throw new Exception('Unable to update product.');
        }
    }

    /**
     * Delete a product
     *
     * @param int $id
     * @return bool
     * @throws Exception
     */
    public function delete(int $id): bool
    {
        try {
            return $this->productRepository->delete($id);
        } catch (Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            throw new Exception('Unable to delete product.');
        }
    }

    /**
     * Check if a product exists by conditions
     *
     * @param array<string,mixed> $conditions
     * @return bool
     */
    public function exists(array $conditions): bool
    {
        try {
            return $this->productRepository->exists($conditions);
        } catch (Exception $e) {
            Log::error('Error checking product existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Conta produtos (com cache).
     *
     * @return int
     */
    public function productsCount(): int
    {
        $key = config('cachekeys.products.count', 'products.count');

        return (int) $this->remember($key, 600, function () {
            if (method_exists($this->productRepository, 'count')) {
                return $this->productRepository->count();
            }
            return Product::query()->count();
        });
    }

    /**
     * Paginate product
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
            return $this->productRepository->paginate($perPage, $filters,["owner"],$orderBy, $direction)->toArray();
        } catch (Exception $e) {
            Log::error('Error paginating product: ' . $e->getMessage());
            throw new Exception('Unable to paginate product.');
        }
    }

}
