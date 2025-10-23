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
            'title' => \$this->resource->title,
            'created_at' => getFormatedDate(\$this->resource->created_at)
        ];
    }
}
        ";
    }
}
