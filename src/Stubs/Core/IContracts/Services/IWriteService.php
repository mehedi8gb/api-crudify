<?php

namespace App\IContracts\Services;

use App\IContracts\Repositories\IRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

interface IWriteService
{
    /**
     * Create a new model instance (wrapped in transaction).
     *
     * @throws Throwable
     */
    public function create(array $data): Model;

    /**
     * Update an existing model instance (wrapped in transaction).
     *
     * @throws Throwable
     */
    public function update(Model $model, array $data): Model;

    /**
     * Delete a model instance (wrapped in transaction).
     *
     * @throws Throwable
     */
    public function delete(Model $model): bool;

    /**
     * Delete multiple models (wrapped in transaction).
     *
     * @throws Throwable
     */
    public function bulkDelete(array $models): int;

    /**
     * Check if a model exists by its primary key.
     */
    public function exists(int|string $id): bool;

    public function executeInTransaction(callable $callback): mixed;
    public function getRepository(): IRepository;
}
