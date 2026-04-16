<?php

namespace App\IContracts\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface IReadService
{
    /**
     * Find a model by ID with optional relations.
     */
    public function findById(int|string $id, array $with = []): ?Model;

    /**
     * Find a model by ID or fail with optional relations.
     *
     * @throws ModelNotFoundException
     */
    public function findByIdOrFail(int|string $id, array $with = []): Model;
}
