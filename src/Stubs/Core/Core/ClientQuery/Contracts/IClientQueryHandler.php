<?php

namespace App\Core\ClientQuery\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

interface IClientQueryHandler
{
    public function setNext(?IClientQueryHandler $handler): IClientQueryHandler;

    public function handle(Builder $builder, Request $request): Builder|array;
}
