<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Throwable;


/**
 * Class BaseRepository
 *
 * This is a base repository class that implements common database operations
 * following the repository pattern. It provides a standardized interface for
 * interacting with database models and includes error handling and logging.
 *
 * @package App\Repository
 */
class BaseRepository
{
    /**
     * The Eloquent model instance
     *
     * @var Model
     */
    protected Model $model;

    protected array $searchable = [];

    protected array $filterable = [];

    protected array $sortable   = ['id'];

    /**
     * BaseRepository constructor
     *
     * Initializes the repository with an Eloquent model instance.
     *
     * @param Model $model The Eloquent model to use for database operations
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records with optional relations and where conditions.
     *
     * @param array $columns
     * @param array $relations
     * @param array $conditions Each condition: ['column', 'value'] or ['column', 'operator', 'value']
     * @return \Illuminate\Support\Collection
     */
    public function all(array $columns = ['*'], array $relations = [], array $conditions = []): Collection
    {
        try {
            $query = $this->model->with($relations);

            foreach ($conditions as $condition) {
                if (is_array($condition) && count($condition) === 3) {
                    [$column, $operator, $value] = $condition;
                    $query->where($column, $operator, $value);
                } elseif (is_array($condition) && count($condition) === 2) {
                    [$column, $value] = $condition;
                    $query->where($column, $value);
                }
            }

            return $query->get($columns);

        } catch (Exception $e) {
            Log::error(__('repository.errors.fetch_all', ['message' => $e->getMessage()]));
            return collect();
        }
    }

    /**
     * Find a record by its primary key
     *
     * Retrieves a single record from the database by its ID.
     * Returns null if the record is not found or an error occurs.
     *
     * @param mixed $id The primary key value of the record to find
     * @return Model|null The found model instance or null
     */
    public function find($id, array $with = []): ?Model
    {
        try {
            return $this->model->with($with)->find($id);
        } catch (\Exception $e) {
            Log::error(__('repository.errors.find_by_id', ['id' => $id, 'message' => $e->getMessage()]));
            return null;
        }
    }


    /**
     * Find records by specified conditions
     *
     * Retrieves all records that match the given conditions.
     * Returns an empty collection if no records are found or an error occurs.
     *
     * @param array $conditions Associative array of column-value pairs to match
     * @return Collection Collection of matching records
     */
    public function findBy(array $conditions): Collection
    {
        try {
            return $this->model->where($conditions)->get();
        } catch (Exception $e) {
            Log::error(__('repository.errors.find_by_conditions', ['message' => $e->getMessage()]));
            return collect();
        }
    }

    /**
     * Create a new record in the database
     *
     * Creates a new record with the provided data.
     * Returns null if an error occurs during creation.
     *
     * @param array $data Associative array of column-value pairs for the new record
     * @return Model|null The created model instance or null on failure
     */
    public function create(array $data): ?Model
    {
        try {
            return $this->model->create($data);
        } catch (Exception $e) {
            dd($e);
            Log::error(__('repository.errors.create', ['message' => $e->getMessage()]));
            return null;
        }
    }

    /**
     * Update an existing record in the database
     *
     * Updates a record identified by its primary key with the provided data.
     * Returns null if the record is not found or an error occurs.
     *
     * @param mixed $id The primary key of the record to update
     * @param array $data Associative array of column-value pairs to update
     * @return Model|null The updated model instance or null on failure
     */
    public function update($id, array $data): ?Model
    {
        try {
            $record = $this->find($id);
            if (!$record) {
                Log::warning(__('repository.warnings.not_found_for_update', ['id' => $id]));
                return null;
            }
            $record->update($data);
            return $record;
        } catch (Exception $e) {
            Log::error(__('repository.errors.update', ['id' => $id, 'message' => $e->getMessage()]));
            return null;
        }
    }

    /**
     * Delete a record from the database
     *
     * Removes a record identified by its primary key.
     * Returns false if the record is not found or an error occurs.
     *
     * @param mixed $id The primary key of the record to delete
     * @return bool True if deletion was successful, false otherwise
     */
    public function delete($id): bool
    {
        try {
            $record = $this->find($id);
            if (!$record) {
                Log::warning(__('repository.warnings.not_found_for_delete', ['id' => $id]));
                return false;
            }

            return $record->delete();
        } catch (Exception $e) {
            Log::error(__('repository.errors.delete', ['id' => $id, 'message' => $e->getMessage()]));
            return false;
        }
    }

    /**
     * Retrieve paginated records with filtering and sorting
     *
     * Fetches records with pagination, optional filtering, and sorting.
     * Returns an empty collection if an error occurs.
     *
     * @param int $perPage Number of records per page
     * @param array $filters Associative array of column-value pairs for filtering (uses LIKE operator)
     * @param string $orderBy Column name to sort by
     * @param string $direction Sort direction ('asc' or 'desc')
     * @return LengthAwarePaginator|Collection Paginated results or empty collection on failure
     */
    public function paginate(
        int    $perPage = 15,
        array  $filters = [],
        array  $with = [],
        string $orderBy = 'id',
        string $direction = 'asc'
    ): LengthAwarePaginator|\Illuminate\Support\Collection
    {
        try {
            /** @var Builder $query */
            $query = $this->model->newQuery();

            // Eager loads
            if (!empty($with)) {
                $query->with($with);
            }

            // LIKE compatível com o driver
            $driver = $this->model->getConnection()->getDriverName();
            $likeOp = $driver === 'pgsql' ? 'ILIKE' : 'LIKE';

            // --- SEARCH (opcional) ---
            $search = trim((string)($filters['search'] ?? ''));
            unset($filters['search']);

            if ($search !== '' && !empty($this->searchable)) {
                $query->where(function (Builder $q) use ($search, $likeOp) {
                    foreach ($this->searchable as $col) {
                        if (str_contains($col, '.')) {
                            // relação.ex: company.name
                            [$rel, $relCol] = explode('.', $col, 2);
                            $q->orWhereHas($rel, function (Builder $qq) use ($relCol, $search, $likeOp) {
                                $qq->where($relCol, $likeOp, "%{$search}%");
                            });
                        } else {
                            $q->orWhere($col, $likeOp, "%{$search}%");
                        }
                    }
                });
            }

            foreach ($filters as $field => $value) {
                // pula nulos/vazios
                if ($value === null || (is_string($value) && trim($value) === '')) {
                    continue;
                }

                if (!empty($this->filterable) && !in_array($field, $this->filterable, true)) {
                    continue;
                }

                if (str_contains($field, '.')) {
                    [$rel, $relCol] = explode('.', $field, 2);
                    $query->whereHas($rel, function (Builder $qq) use ($relCol, $value, $likeOp) {
                        $this->applyWhere($qq, $relCol, $value, $likeOp);
                    });
                    continue;
                }

                $this->applyWhere($query, $field, $value, $likeOp);
            }

            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';
            if (!empty($this->sortable) && !in_array($orderBy, $this->sortable, true)) {
                $orderBy = $this->sortable[0] ?? 'id';
            }
            $query->orderBy($orderBy, $direction);

            return $query->paginate($perPage);
        } catch (Throwable $e) {
            Log::error(__('repository.errors.pagination', ['message' => $e->getMessage()]));
            return collect();
        }
    }

    protected function applyWhere(Builder $q, string $field, mixed $value, string $likeOp): void
    {
        if (is_array($value)) {
            if (array_key_exists('from', $value) || array_key_exists('to', $value)) {
                $from = $value['from'] ?? null;
                $to   = $value['to']   ?? null;

                if ($from !== null && $to !== null) {
                    $q->whereBetween($field, [$from, $to]);
                } elseif ($from !== null) {
                    $q->where($field, '>=', $from);
                } elseif ($to !== null) {
                    $q->where($field, '<=', $to);
                }
                return;
            }

            if (isset($value['op'], $value['value'])) {
                $op  = strtolower((string)$value['op']);
                $val = $value['value'];

                if (in_array($op, ['in', 'not in'], true) && is_array($val)) {
                    $op === 'in' ? $q->whereIn($field, $val) : $q->whereNotIn($field, $val);
                    return;
                }

                if (in_array($op, ['like', 'ilike'], true)) {
                    $q->where($field, $likeOp, "%{$val}%");
                } elseif (in_array($op, ['=', '!=', '>', '>=', '<', '<='], true)) {
                    $q->where($field, strtoupper($op), $val);
                } else {
                    // fallback
                    $q->where($field, $likeOp, "%{$val}%");
                }
                return;
            }

            $vals = array_values(array_filter($value, fn($v) => $v !== null && $v !== ''));
            if ($vals) {
                $q->whereIn($field, $vals);
            }
            return;
        }

        if (is_bool($value) || $value === 0 || $value === 1 || $value === '0' || $value === '1') {
            $q->where($field, (int)$value);
            return;
        }
        if (is_numeric($value)) {
            $q->where($field, $value);
            return;
        }
        $q->where($field, $likeOp, '%' . trim((string)$value) . '%');
    }


    /**
     * Check if records exist with the specified conditions
     *
     * Determines if any records match the given conditions.
     * Returns false if an error occurs.
     *
     * @param array $conditions Associative array of column-value pairs to match
     * @return bool True if matching records exist, false otherwise
     */
    public function exists(array $conditions): bool
    {
        try {
            return $this->model->where($conditions)->exists();
        } catch (Exception $e) {
            Log::error(__('repository.errors.exists', ['message' => $e->getMessage()]));
            return false;
        }
    }

    /**
     * Get a new query builder instance for the model
     *
     * Provides direct access to the query builder for more complex queries.
     * This method allows for custom query building beyond the standard repository methods.
     *
     * @return \Illuminate\Database\Eloquent\Builder Query builder instance
     */
    public function query()
    {
        return $this->model->newQuery();
    }
}
