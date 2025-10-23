<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

class CreateService
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
        $classNameLower = strtolower($className);
        $classNamePlural = $this->pluralize($classNameLower);
        $classNamePluralTitle = ucfirst($classNamePlural);
        $repositoryClass = "{$className}Repository";
        $serviceVar = lcfirst($className);

        return "<?php

namespace App\Services\V1$this->controllerPath;

use App\Http\Resources\\{$className}\\{$className}Resource;
use App\Repositories\V1$this->controllerPath\\{$repositoryClass};
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Throwable;

class {$className}Service
{
    public function __construct(private readonly {$repositoryClass} \$repository) {}

    /**
     * @throws Exception
     */
    public function list{$classNamePluralTitle}(): array|Collection
    {
        \$query = \$this->repository->query();
        return handleApiRequest(request(), \$query, [], {$className}Resource::class);
    }

    /**
     * @throws Throwable
     */
    public function create{$className}(array \$data)
    {
        return \$this->repository->create(\$data);
    }

    public function get{$className}(string \$id)
    {
        return \$this->repository->findById(\$id);
    }

    /**
     * @throws Exception
     */
    public function update{$className}(string \$id, array \$data)
    {
        \${$serviceVar} = \$this->repository->findById(\$id);

        if (! \${$serviceVar}) {
            throw new Exception('{$className} not found');
        }

        return \$this->repository->update(\${$serviceVar}, \$data);
    }

    /**
     * @throws Exception
     */
    public function delete{$className}(string \$id): bool
    {
        \${$serviceVar} = \$this->repository->findById(\$id);

        if (! \${$serviceVar}) {
            throw new Exception('{$className} not found');
        }

        return \$this->repository->delete(\${$serviceVar});
    }
}
        ";
    }

    private function pluralize(string $word): string
    {
        if (substr($word, -1) === 'y') {
            return substr($word, 0, -1) . 'ies';
        } elseif (in_array(substr($word, -1), ['s', 'x', 'z']) || in_array(substr($word, -2), ['ch', 'sh'])) {
            return $word . 'es';
        } else {
            return $word . 's';
        }
    }
}
