<?php

namespace App\Core\Query\Handlers\Core;

use App\Core\Query\Handlers\AbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SoftDeleteHandler extends AbstractQueryHandler
{
    protected function process(Builder $builder, Request $request): Builder
    {
        $trashed = $request->query('trashed');

        match ($trashed) {
            'with' => $builder->withTrashed(),
            'only' => $builder->onlyTrashed(),
            default => null,
        };

        return $builder;
    }
}
