<?php

namespace App\Core\Query\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface IQueryHandler
{
    public function setNext(?IQueryHandler $handler): IQueryHandler;

    public function handle(Builder $builder, Request $request): Builder|array;
}
