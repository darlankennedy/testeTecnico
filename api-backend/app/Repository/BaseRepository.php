<?php

namespace App\Repository;

use App\interface\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder;
use Nwidart\Modules\Collection;
use Throwable;

class BaseRepository implements BaseRepositoryInterface
{
    protected Model $model;

    protected array $searchable = [];

    protected array $filterable = [];

    protected array $sortable   = ['id'];

    /**
     * BaseRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve all records from the model.
     *
     * @param array $columns
     * @param array $relations
     * @param bool $paginate
     * @param int $perPage
     * @return mixed
     */
    public function all($columns = array('*'), $relations = array(), $conditions = array(), $perPage = 15): mixed
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
            Log::error("function all",[
               'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
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
            Log::error("function create",[
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * @param array $data
     * @param $id
     * @param $attribute
     * @return Model
     */
    public function update(array $data, $id, String $attribute = "id"): ?Model
    {
        try {
            $record = $this->find($id);
            if (!$record) {
                Log::warning("not found", ['id' => $id]);
                return null;
            }
            $record->fill($data);
            $record->save();
            return $record;
        } catch (Exception $e) {
            Log::error("function update",[
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * @param $id
     * @return bool|null
     */
    public function delete($id): ?bool
    {
        try {
            $record = $this->find($id);
            if (!$record) {
                Log::warning("not found", ['id' => $id]);
                return false;
            }

            return $record->delete();
        } catch (Exception $e) {
            Log::error("function delete",[
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * @param $id
     * @param array $columns
     * @param array $relations
     * @return Model|null
     */
    public function findBy($attribute, $value, $columns = array('*'), $relations = array()): ?Model
    {
        try {
            return $this->model->with($relations)
                ->where($attribute, $value)
                ->first($columns);
        } catch (\Exception $e) {
            Log::error("function findBy",[
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Execute a raw SQL query and return the results as a collection
     *
     * @param string $rawQuery The raw SQL query to execute
     * @param array $bindings Optional array of bindings for the query
     * @return \Illuminate\Support\Collection Collection of results
     */
    public function query(string $rawQuery, array $bindings = []): \Illuminate\Support\Collection
    {
        return collect(DB::select($rawQuery, $bindings));
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
            array $conditions = [],
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

        /**
         * Aplica condições de filtro na query
         *
         * @param Builder $q
         * @param string $field
         * @param mixed $value
         * @param string $likeOp
         * @return void
         */
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

    public function find($id, $columns = array('*'), $with = array()): ?Model
    {
        try {
            return $this->model->with($with)->find($id);
        } catch (\Exception $e) {
            Log::error("function find",[
                'file' => $e->getFile(),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
