<?php

namespace App\Core\ClientQuery\Handlers\Core;

use App\Core\ClientQuery\Handlers\ClientAbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ClientRelationHandler extends ClientAbstractQueryHandler
{
    public function __construct(protected array $with) {}

    protected function process(Builder $builder, Request $request): Builder
    {
        if (!empty($this->with)) {
            $builder->with($this->with);
            return $builder;
        }

        return $builder;
    }
}
