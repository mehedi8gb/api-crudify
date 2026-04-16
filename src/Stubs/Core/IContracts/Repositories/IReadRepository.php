<?php

namespace App\IContracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface IReadRepository
{
    /**
     * Get all records.
     */
    public function all(array $columns = ['*']);

    /**
     * Find a record by ID or model instance.
     */
    public function find(int|string|Model $idOrObject, array $columns = ['*']): ?Model;
}
