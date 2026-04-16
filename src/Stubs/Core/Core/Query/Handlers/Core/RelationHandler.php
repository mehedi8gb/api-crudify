<?php

namespace App\Core\Query\Handlers\Core;

use App\Core\Query\Handlers\AbstractQueryHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ReflectionClass;

class RelationHandler extends AbstractQueryHandler
{
    public function __construct(protected array $with){}

    protected function process(Builder $builder, Request $request): Builder
    {
        if (!empty($this->with)) {
            $builder->with($this->with);
            return $builder;
        }

        $model = $builder->getModel();
        $defaultEagerLoads = $model->getEagerLoads();

        if (!empty($defaultEagerLoads)) {
            $builder->with(array_keys($defaultEagerLoads));
            return $builder;
        }

        $reflection = new ReflectionClass($model);
        if ($reflection->hasProperty('with')) {
            $property = $reflection->getProperty('with');
            $defaultWith = $property->getValue($model) ?? [];
            if (!empty($defaultWith)) {
                $builder->with($defaultWith);
            }
        }

        return $builder;
    }
}
