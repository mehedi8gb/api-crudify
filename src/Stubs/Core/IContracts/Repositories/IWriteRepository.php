<?php

namespace App\IContracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface IWriteRepository
{
    /**
     * Get a new query builder instance.
     */
    public function query(): Builder;

    /**
     * Find a record by ID or fail.
     *
     * @throws ModelNotFoundException
     */
    public function findOrFail(int|string $id, array $columns = ['*']): ?Model;

    /**
     * Create a new record.
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record.
     */
    public function update(Model $model, array $attributes): Model;

    /**
     * Delete a record.
     */
    public function delete(Model $model): bool;

    /**
     * Begin a database transaction.
     */
    public function beginTransaction(): void;

    /**
     * Commit a database transaction.
     */
    public function commit(): void;

    /**
     * Rollback a database transaction.
     */
    public function rollback(): void;

    /**
     * Execute a callback within a transaction.
     */
    public function transaction(callable $callback): mixed;
}
