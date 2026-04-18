<?php

namespace App\Core\ClientQuery;


use App\Core\ClientQuery\Handlers\Core\ClientPaginationHandler;
use App\Core\ClientQuery\Handlers\Core\ClientRelationHandler;
use App\Core\ClientQuery\Handlers\Core\ClientSortHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HandleClientApiQueryRequest
{
    public function __construct(protected Builder $builder, protected Request $request) {}

    public function handle(array $with = []): array
    {
        // responsibility chain
        $relations  = new ClientRelationHandler($with);
        $sort       = new ClientSortHandler();
        $pagination = new ClientPaginationHandler();

        // build chain order
        $relations
            ->setNext($relations)
            ->setNext($sort)
            ->setNext($pagination);

        // start chain
        return $relations->handle($this->builder, $this->request);
    }
}
