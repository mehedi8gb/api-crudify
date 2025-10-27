<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateResource extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $domainPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = str_replace('/', '\\', $domainPath);
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace App\Http\Resources\\{$this->namespace}\\{$className};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$className}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        \$id = \$this->id;
        return [
            'title' => \$this->resource->title,
            'createdAt' => getFormatedDate(\$this->resource->created_at)
        ];
    }
}
        ";
    }
}
