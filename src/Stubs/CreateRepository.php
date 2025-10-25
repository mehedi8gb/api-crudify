<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateRepository
{
    private array $modelBinding;
    private mixed $controllerPath;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        if ($controllerPath === '') {
            $this->controllerPath = '';
        } else {
            $this->controllerPath = '\\' . str_replace('/', '\\', $controllerPath);
        }
    }

    public function generate(): string
    {
        $className = $this->modelBinding['className'];

        return "<?php

namespace App\Repositories\V1$this->controllerPath;

use App\Models\\{$className};
use Illuminate\Database\Eloquent\Builder;

class {$className}BaseRepository
{
    public function query(): Builder
    {
        return {$className}::query();
    }

    public function findById(string \$id)
    {
        return {$className}::find(\$id);
    }

    public function create(array \$data)
    {
        return {$className}::create(\$data);
    }

    public function update({$className} \$model, array \$data)
    {
        \$model->update(\$data);
        return \$model->fresh();
    }

    public function delete({$className} \$model): bool
    {
        return \$model->delete();
    }
}
        ";
    }
}
