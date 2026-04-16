<?php

namespace App\Core\Query\Handlers\Core;

use App\Core\Query\Handlers\AbstractQueryHandler;
use App\Helpers\SearchParamMapper;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class FilterHandler extends AbstractQueryHandler
{
    /**
     * @throws Exception
     */
    protected function process(Builder $builder, Request $request): Builder|array
    {
        new SearchParamMapper($request);

        $operator = $request->query('operator', 'like');

        // Exclude
        if ($request->query('exclude')) {
            $exclude = explode(',', $request->query('exclude'));
            if (count($exclude) === 2) {
                $builder->where($exclude[0], '!=', $exclude[1]);
            }
        }

        // WHERE filters
        if ($whereFilters = $request->query('where')) {
            $this->applyConditionalFilters($builder, $whereFilters, $operator, 'and');
        }

        // OR filters
        if ($orFilters = $request->query('orWhere')) {
            $this->applyConditionalFilters($builder, $orFilters, $operator, 'or');
        }

        return $builder;
    }

    protected function applyConditionalFilters(Builder $query, mixed $filters, string $operator, string $type = 'and'): void
    {
        $filters = is_array($filters) ? $filters : [$filters];

        $method = $type === 'and' ? 'where' : 'orWhere';

        $query->{$method}(/**
         * @throws Exception
         */ function ($nested) use ($filters, $operator, $type, $method) {
            foreach ($filters as $filter) {
                $parts = explode(',', $filter);

                if (count($parts) < 2) {
                    throw new Exception("Invalid {$type}Where format: {$filter}");
                }

                // Extract nested with:relations
                $relations = [];
                while (! empty($parts) && str_starts_with($parts[0], 'with:')) {
                    $relations[] = str_replace('with:', '', array_shift($parts));
                }

                [$column, $value] = $parts;

                if (! empty($relations)) {
                    $nested->{$method.'Has'}(implode('.', $relations), function ($relationQuery) use ($operator, $column, $value) {
                        $relationQuery->where($column, $operator, $value);
                    });
                } else {
                    $nested->{$method}($column, $operator, $value);
                }
            }
        });
    }
}
