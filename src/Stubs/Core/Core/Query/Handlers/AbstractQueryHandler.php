<?php

namespace App\Core\Query\Handlers;

use App\Core\Query\Contracts\IQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class AbstractQueryHandler implements IQueryHandler
{
    protected ?IQueryHandler $nextHandler = null;

    public function setNext(?IQueryHandler $handler): IQueryHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle(Builder $builder, Request $request): Builder|array
    {
        $builder = $this->process($builder, $request);

        if ($this->nextHandler) {
            return $this->nextHandler->handle($builder, $request);
        }

        return $builder;
    }

    abstract protected function process(Builder $builder, Request $request): Builder|array;
}
