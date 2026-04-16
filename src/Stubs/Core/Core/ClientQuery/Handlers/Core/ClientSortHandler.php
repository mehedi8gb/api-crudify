<?php

namespace App\Core\ClientQuery\Handlers\Core;

use App\Core\ClientQuery\Handlers\ClientAbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ClientSortHandler extends ClientAbstractQueryHandler
{
    protected function process(Builder $builder, Request $request): Builder|array
    {
        // If any order already exists — from latest(), oldest(), or orderBy()
        if (!empty($builder->getQuery()->orders)) {
            return $builder;
        }

        // Get query params
        $sortBy = $request->query('sortBy', getCreatedAtColumn($builder));
        $sortDirection = Str::lower($request->query('sortOrder', 'desc'));

        // Validate direction
        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = 'desc';
        }

        // Safe fallback: ensure column exists to prevent SQL injection
        if (Schema::hasColumn($builder->getModel()->getTable(), $sortBy)) {
            $builder->orderBy($sortBy, $sortDirection);
        }

        return $builder;
    }
}
