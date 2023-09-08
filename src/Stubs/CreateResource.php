<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateResource
{

    private array $modelBinding;

    public function __construct(array $modelBinding)
    {
        $this->modelBinding = $modelBinding;
    }

    public function generateResource(): string
    {
        return "<?php
namespace App\Http\Resources\\{$this->modelBinding['className']};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$this->modelBinding['className']}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        \$id = \$this->id;
        return [
            'title' => \$this->title,
            'description' => \$this->description,
            'created_at' => \$this->created_at->format('d-m-Y'),
            'links' => [
                'show' => route('post.show', \$this->slug),
                'update' => route('post.update', \$id),
                'delete' => route('post.destroy', \$id),
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

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class {$this->modelBinding['className']}ResourceCollection extends ResourceCollection
{
    public function toArray(Request \$request): array
    {
        return [
            'data' => {$this->modelBinding['className']}Resource::collection(\$this->collection),
        ];
    }
}
        ";
    }
}
