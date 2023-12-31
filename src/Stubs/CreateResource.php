<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateResource
{

    private array $modelBinding;
    private mixed $modelBindingLower;

    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding;
        $this->modelBindingLower = strtolower($modelBinding['className']);
    }

    public function generateResource(): string
    {
        return "<?php
namespace App\Http\Resources\\{$this->modelBinding['className']};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

class {$this->modelBinding['className']}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        \$id = \$this->id;
        return [
            'title' => \$this->title,
            'created_at' => Carbon::parse(\$this->created_at)->format('d-m-Y'),
            'links' => [
                'show' => \$this->unless(Route::currentRouteName() === '{$this->modelBindingLower}.show', function () {
                    return route('{$this->modelBindingLower}.show', [
                        'slug' => \$this->slug
                    ]);
                }),
                'update' => route('{$this->modelBindingLower}.update', \$id),
                'delete' => route('{$this->modelBindingLower}.destroy', \$id),
            ]
        ];
    }
}
        ";
    }

    public function generateResourceCollection(): string
    {
        return "<?php
namespace App\Http\Resources\\{$this->modelBinding['className']};

use Illuminate\Http\Resources\Json\ResourceCollection;

class {$this->modelBinding['className']}ResourceCollection extends ResourceCollection
{
    public function toArray(\$request): array
    {
        return [
            'data' => {$this->modelBinding['className']}Resource::collection(\$this->collection),
        ];
    }
}
        ";
    }
}
