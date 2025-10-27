<?php

namespace Mehedi8gb\ApiCrudify\Stubs;

use Mehedi8gb\ApiCrudify\Stubs\Base\BaseStub;

class CreateRepository extends BaseStub
{
    private array $modelBinding;
    private string $namespace;

    public function __construct(array $modelBinding, string $controllerPath)
    {
        $this->modelBinding = $modelBinding;
        $this->namespace = str_replace('/', '\\', $controllerPath);
    }

    public function generate(): string
    {
        $className        = $this->modelBinding['className'];            // Original model class name, e.g., "Order"
        $classVar         = lcfirst($className);                         // e.g., "order"
        $classNamePlural  = $this->pluralize($className);                // e.g., "Orders"
        $classNameCamel   = $this->toCamelCase($classNamePlural);        // e.g., "orders" => "orders" (camelCase)
        $classNameTitle   = ucfirst($classNamePlural);                   // e.g., "Orders"
        $methodNameSuffix = ucfirst($classNameCamel);                    // e.g., "Orders" (used in getXXXData)
        $modelNameSpace = $this->normalizeNamespaceToGetSingleDirectory($this->namespace);

        return "<?php

namespace App\Repositories\\{$this->namespace};

use App\Models\\{$modelNameSpace}\\{$className};
use App\Repositories\V1\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Exception;
/**
 * {$className}-specific data access operations.
 *
 * This repository ONLY handles database queries.
 * Business logic belongs in {$className}Service.
 */
class {$className}Repository extends BaseRepository
{
    public function __construct({$className} \$model, protected Request \$request)
    {
        parent::__construct(\$model, \$request);
    }


    /**
     * @throws Exception
     * Get data for {$classNameTitle}.
     */
    public function get{$methodNameSuffix}Data(array \$with = []): array
    {
        \$query = \$this->query();

        //  apply custom query if needed before passing to handleApiQueryRequest
        // \$query->where('status', true);

        return \$this->handleApiQueryRequest(\$query, \$with);
    }

    /**
     * Find by slug (common lookup pattern).
     */
    public function findBySlug(string \$slug): ?{$className}
    {
        return \$this->query()
            ->where('slug', \$slug)
            ->first();
    }

    /**
     * Get {$classVar}s by IDs (useful for bulk operations).
     */
    public function findMany(array \$ids): Collection
    {
        return \$this->query()
            ->whereIn('id', \$ids)
            ->get();
    }

    /**
     * Get active {$classVar}s.
     */
    public function getActive(): Collection
    {
        return \$this->query()
            ->where('isActive', true)
            ->orderBy('createdAt', 'desc')
            ->get();
    }
}
        ";
    }
}
