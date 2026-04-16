<?php

namespace App\Repositories\V1;

use App\IContracts\Repositories\IRepository;
use App\Core\Query\HandleApiQueryRequest;
use App\Core\ClientQuery\HandleClientApiQueryRequest;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Base repository implementation.
 *
 * Responsibilities:
 * - Data access operations
 * - Query building
 * - Transaction management
 *
 * Does NOT handle:
 * - Business logic (belongs in Service)
 * - HTTP concerns (belongs in Controller)
 * - Authorization (belongs in Policy/Gate)
 */
abstract class BaseRepository implements IRepository
{
    public function __construct(
        protected Model $model,
        protected Request $request
    ) {}

    public function query(): Builder
    {
        return $this->model->newQuery();
    }

    public function clearModelCache(): void
    {
        if (config('cache.enabled')) {
            Cache::tags($this->getModel()->getTable())->flush();
        }
    }

    /**
     * Delegate query handling to the centralized API query processor.
     *
     * This method serves as a thin abstraction layer that initializes and
     * executes the `HandleApiQueryRequest` pipeline. It passes the query builder
     * and current request context to a dedicated handler responsible for applying
     * filtering, sorting, eager loading, field selection, and pagination logic.
     *
     * **Responsibilities:**
     * - Decouple API query handling from the repository/service
     * - Ensure consistent query transformation flow across all modules
     * - Maintain SRP by delegating heavy query logic to a specialized class
     *
     * @param Builder $builder
     *         The base query builder instance (typically provided by repository scope methods).
     * @param array $with
     *         List of relationship names to eager load (e.g. ['children', 'parent']).
     *
     * @return array
     *         Structured API-ready response containing `meta` and `result` keys.
     *
     * @throws Exception
     *         When invalid query parameters or filters are encountered.
     *
     * @see HandleApiQueryRequest
     */
    public function handleApiQueryRequest(Builder $builder, array $with): array
    {
        $builder = new HandleApiQueryRequest($builder, $this->request);

        return $builder->handle($with);
    }

    public function handleApiClientQueryRequest(Builder $builder, array $with): array
    {
        $builder = new HandleClientApiQueryRequest($builder, $this->request);

        return $builder->handle($with);
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function find(int|string|Model $id, array $columns = ['*']): Model
    {
        return $this->query()->find($id, $columns);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        $model = $this->find($id, $columns);

        if (! $model) {
            throw (new ModelNotFoundException)->setModel(
                get_class($this->model),
                $id
            );
        }

        return $model;
    }

    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    /**
     * Update or create a record and return the instance.
     *
     * @param array $attributes (The values to find the record)
     * @param array $values (The values to update/create)
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /**
     * @throws Throwable
     */
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    /**
     * @throws Throwable
     */
    public function commit(): void
    {
        DB::commit();
    }

    /**
     * @throws Throwable
     */
    public function rollback(): void
    {
        DB::rollBack();
    }

    /**
     * @throws Throwable
     */
    public function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Update model and eager load relationships.
     *
     * This is a common pattern, so we provide it in the base class.
     */
    protected function updateWithRelations(Model $model, array $attributes, array $relations = []): Model
    {
        $model = $this->update($model, $attributes);

        if (! empty($relations)) {
            $model->load($relations);
        }

        return $model;
    }

    /**
     * Create model and eager load relationships.
     */
    protected function createWithRelations(array $attributes, array $relations = []): Model
    {
        $model = $this->create($attributes);

        if (! empty($relations)) {
            $model->load($relations);
        }

        return $model;
    }

    /**
     * Bulk insert records (more efficient than multiple creates).
     */
    public function bulkInsert(array $records): bool
    {
        return $this->query()->insert($records);
    }

    /**
     * Bulk update records matching criteria.
     */
    public function bulkUpdate(array $criteria, array $attributes): int
    {
        return $this->query()->where($criteria)->update($attributes);
    }

    /**
     * Check if record exists.
     */
    public function exists(int|string $id): bool
    {
        return $this->query()->where($this->model->getKeyName(), $id)->exists();
    }

    /**
     * Count records matching criteria.
     */
    public function count(array $criteria = []): int
    {
        $query = $this->query();

        if (! empty($criteria)) {
            $query->where($criteria);
        }

        return $query->count();
    }


    public function getModel(): Model
    {
        return $this->query()->getModel();
    }
}