<?php

namespace App\Core\ClientQuery\Handlers;

use App\Core\ClientQuery\Contracts\IClientQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class ClientAbstractQueryHandler implements IClientQueryHandler
{
    protected ?IClientQueryHandler $nextHandler = null;

    public function setNext(?IClientQueryHandler $handler): IClientQueryHandler
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
