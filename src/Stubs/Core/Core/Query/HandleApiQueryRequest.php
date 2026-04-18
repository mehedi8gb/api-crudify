<?php

namespace App\Core\Query;

use App\Core\Query\Handlers\Core\FilterHandler;
use App\Core\Query\Handlers\Core\PaginationHandler;
use App\Core\Query\Handlers\Core\RelationHandler;
use App\Core\Query\Handlers\Core\SoftDeleteHandler;
use App\Core\Query\Handlers\Core\SortHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HandleApiQueryRequest
{
    public function __construct(protected Builder $builder, protected Request $request) {}

    public function handle(array $with = []): array
    {
        // responsibility chain
        $softDelete = new SoftDeleteHandler();
        $relations  = new RelationHandler($with);
        $filters    = new FilterHandler();
        $sort       = new SortHandler();
        $pagination = new PaginationHandler();

        // build chain order
        $softDelete
            ->setNext($relations)
            ->setNext($filters)
            ->setNext($sort)
            ->setNext($pagination);

        // start chain
        return $softDelete->handle($this->builder, $this->request);
    }
}
