<?php

namespace App\Services\V1;

use App\IContracts\Repositories\IRepository;
use App\IContracts\Services\IService;
use App\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Base service with common business logic patterns.
 *
 * IMPORTANT: This does NOT implement IRepository!
 * Services are NOT repositories - they coordinate business logic.
 *
 * Responsibilities:
 * - Business logic orchestration
 * - Transaction coordination
 * - Multi-repository operations
 * - Business rule validation
 *
 * Does NOT handle:
 * - Direct database access (use repository)
 * - HTTP concerns (use controller)
 * - Resource transformation (use controller)
 */
abstract class BaseService implements IService
{
    protected IRepository $repository;

    protected Request $request;

    public function __construct(IRepository $repository, Request $request)
    {
        $this->repository = $repository;
        $this->request = $request;
    }

    /**
     * Transforms a paginated dataset into a standardized resource response
     * by wrapping the data collection with its corresponding Resource class
     * and returning both the transformed result and meta information.
     */
    protected function prepareResourceResponse($data, ?string $resourceClass): array
    {
        $resourceClass ??= getResourceClass($this->repository->getModel());

        $result = $resourceClass::collection($data['data']);
        $meta = $data['meta'];

        return compact('meta', 'result');
    }

    /**
     * Find by ID with optional relations.
     */
    public function findById(int|string $id, array $with = []): ?Model
    {
        $model = $this->repository->find($id);

        if ($model && ! empty($with)) {
            $model->load($with);
        }

        return $model;
    }

    /**
     * Find by ID or fail with optional relations.
     *
     * @throws ModelNotFoundException
     */
    public function findByIdOrFail(int|string $id, array $with = []): Model
    {
        $model = $this->repository->findOrFail($id);

        if (! empty($with)) {
            $model->load($with);
        }

        return $model;
    }

    /**
     * Create with transaction.
     *
     * @throws Throwable
     */
    public function create(array $rawData): Model
    {
        // Hook for pre-creation logic
        $data = $this->beforeCreate($rawData);

        // Create model within transaction
        return $this->repository->transaction(function () use ($data, $rawData) {
            $model = $this->repository->create($data);

            // Hook for post-creation logic
            $this->afterCreate($model, $rawData);
            $this->repository->clearModelCache();

            return $model;
        });
    }

    /**
     * Update with transaction.
     *
     * @throws Throwable
     */
    public function update(Model $model, array $data): Model
    {
        return $this->repository->transaction(function () use ($model, $data) {
            // Hook for pre-update logic
            $data = $this->beforeUpdate($model, $data);

            $model = $this->repository->update($model, $data);

            // Hook for post-update logic
            $this->afterUpdate($model);
            $this->repository->clearModelCache();

            return $model;
        });
    }

    /**
     * Delete with transaction.
     *
     * @throws Throwable
     */
    public function delete(Model $model): bool
    {
        return $this->repository->transaction(function () use ($model) {
            // Hook for pre-delete logic
            $this->beforeDelete($model);

            $result = $this->repository->delete($model);

            // Hook for post-delete logic
            if ($result) $this->afterDelete($model);

            $this->repository->clearModelCache();

            return $result;
        });
    }

    /**
     * Bulk delete with transaction.
     *
     * @throws Throwable
     */
    public function bulkDelete(array $models): int
    {
        return $this->repository->transaction(function () use ($models) {
            $deleted = 0;

            foreach ($models as $model) {
                if ($this->repository->delete($model)) {
                    $deleted++;
                }
            }
            $this->repository->clearModelCache();

            return $deleted;
        });
    }

    // ==========================================
    // Lifecycle Hooks (Template Method Pattern)
    // ==========================================

    /**
     * Override in child class to modify data before creation.
     */
    protected function beforeCreate(array $data): array
    {
        return $data;
    }

    /**
     * Override in child class for post-creation logic.
     */
    protected function afterCreate(Model|BaseModel $model, array $rawData): void
    {
        // Hook for events, logging, cache invalidation, etc.
    }

    /**
     * Override in child class to modify data before update.
     */
    protected function beforeUpdate(Model|BaseModel $model, array $data): array
    {
        return $data;
    }

    /**
     * Override in child class for post-update logic.
     */
    protected function afterUpdate(Model|BaseModel $model): void
    {
        // Hook for events, logging, cache invalidation, etc.
    }

    /**
     * Override in child class for pre-delete validation.
     */
    protected function beforeDelete(Model|BaseModel $model): void
    {
        // Hook for validation, checking dependencies, etc.
    }

    /**
     * Override in child class for post-delete logic.
     */
    protected function afterDelete(Model|BaseModel $model): void
    {
        // Hook for cleanup, cache invalidation, etc.
    }

    // ==========================================
    // Helper Methods
    // ==========================================

    /**
     * Execute multiple operations in a single transaction.
     *
     * @throws Throwable
     */
    public function executeInTransaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Check if model exists.
     */
    public function exists(int|string $id): bool
    {
        return $this->repository->exists($id);
    }

    /**
     * Get repository instance (for advanced queries in child services).
     */
    public function getRepository(): IRepository
    {
        return $this->repository;
    }
}