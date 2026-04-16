<?php

namespace App\Core\Query\Handlers\Core;

use App\Core\Query\Handlers\AbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class PaginationHandler extends AbstractQueryHandler
{

    protected function process(Builder $builder, Request $request): Builder|array
    {
        $page = (int) $request->query('page', 1);
        $limit = $request->query('limit', 10);

        if ($limit === 'all') {
            $data = $builder->get();
            $total = $data->count();
            $meta = [
                'page' => 1,
                'limit' => $total,
                'total' => $total,
                'totalPage' => 1,
            ];
        } else {
            $data = $builder->paginate($limit, ['*'], 'page', $page);
            $meta = [
                'page' => $page,
                'limit' => $limit,
                'total' => $data->total(),
                'totalPage' => $data->lastPage(),
            ];
        }

        return [
            'meta' => $meta,
            'data' => $data,
        ];
    }
}
